<?php

namespace App\Services\Search;

use App\Models\Article;
use App\Models\SousCategorie;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cascade de recherche : du plus fidèle à la requête au plus souple.
 * Les annonces d'un niveau passent toujours devant celles des niveaux suivants,
 * et le filet garantit que tout ce que trouvait l'ancien moteur reste présent.
 */
class SearchEngine
{
    public const NIVEAU_EXPRESSION = 1;
    public const NIVEAU_TOUS_LES_MOTS = 2;
    public const NIVEAU_PLUPART_DES_MOTS = 3;
    public const NIVEAU_NORMALISE = 4;
    public const NIVEAU_DEBUT_DE_MOT = 5;
    public const NIVEAU_FAUTES = 6;
    public const NIVEAU_PHRASE = 7;
    public const NIVEAU_FILET = 8;
    public const NIVEAU_SECOURS = 9;

    public function __construct(private TextNormalizer $text)
    {
    }

    /**
     * @param  Builder  $base  annonces autorisées : approuvées, plus les filtres de l'utilisateur
     * @param  array<string, mixed>  $filters  filtres actifs, pour la clé de cache
     * @param  string|null  $userCity  ville de l'utilisateur connecté, pour le classement
     */
    public function run(string $q, Builder $base, array $filters = [], ?string $userCity = null): SearchOutcome
    {
        $userCity = $userCity ? $this->text->normalize($userCity) : null;
        $key = 'search:' . sha1(json_encode([$q, $filters, $userCity, Cache::get(SearchIndexer::VERSION_KEY)]));

        if ($cached = Cache::get($key)) {
            return new SearchOutcome(...$cached);
        }

        $outcome = $this->cascade($q, $base, $filters !== [], $userCity);

        if (count($outcome->ids) <= config('search.cache_max_ids')) {
            Cache::put($key, get_object_vars($outcome), config('search.cache_seconds'));
        }

        return $outcome;
    }

    /**
     * Mot corrigé le plus probable, pour « Vous voulez dire ».
     *
     * @return array<string, string> mot tapé => mot corrigé
     */
    public function suggestCorrections(string $q): array
    {
        $words = array_filter($this->text->words($q), fn ($word) => ! $this->text->isStopWord($word));
        $terms = $this->terms($words);
        $corrections = [];

        foreach ($words as $word) {
            if (($terms[$word]->df ?? 0) === 0 && ($best = $this->corrections($word)[0] ?? null)) {
                $corrections[$word] = $best->terme;
            }
        }

        return $corrections;
    }

    private function cascade(string $q, Builder $base, bool $filtered, ?string $userCity): SearchOutcome
    {
        $allWords = array_slice($this->text->words($q), 0, config('search.max_query_words'));
        $intent = QueryIntent::parse(implode(' ', $allWords), $this->cities(), $this->text);
        $scope = $filtered ? (clone $base)->toBase()->select('articles.id') : null;
        $placed = [];
        $niveau = null;
        $corrections = [];

        foreach ($this->tiers($allWords, $intent, $scope, $corrections) as [$level, $found]) {
            $found = array_diff_key($found, $placed);

            if ($found !== []) {
                $niveau ??= $level;
                $placed += array_flip($this->ranked($found, $intent, $userCity));
            }

            if (count($placed) >= config('search.threshold')) {
                break;
            }
        }

        $ids = array_keys($placed);
        $safetyNet = array_values(array_diff($this->safetyNet($allWords, $scope), $ids));

        if ($safetyNet !== []) {
            $niveau ??= self::NIVEAU_FILET;
        }

        $ids = [...$ids, ...$safetyNet];
        $realCount = count($ids);
        $categorieProche = null;

        if ($ids === [] && trim($q) !== '') {
            [$ids, $categorieProche] = $this->fallback($allWords, $base);
            $niveau = self::NIVEAU_SECOURS;
        }

        return new SearchOutcome(
            $ids,
            $niveau,
            $realCount,
            $corrections,
            $this->comprehension($intent, $niveau, $corrections),
            $categorieProche,
        );
    }

    /**
     * Les paliers de la cascade, du plus strict au plus souple : chacun n'est calculé
     * que si les précédents n'ont pas trouvé assez d'annonces.
     *
     * @return Generator<array{int, array<int, float>}>
     */
    private function tiers(array $allWords, QueryIntent $intent, $scope, array &$corrections): Generator
    {
        $useful = array_values(array_filter($allWords, fn ($word) => ! $this->text->isStopWord($word)));
        $useful = array_slice($useful, 0, config('search.max_useful_words'));

        if ($useful === []) {
            return;
        }

        $documents = DB::table('search_documents')->count();
        $terms = $this->terms($useful);
        $useful = $this->withoutFrequentWords($useful, $terms, $documents);
        $exact = $this->exactGroups($useful, $terms, $documents);
        $exactMatches = $this->matches($exact, $scope);

        yield [self::NIVEAU_EXPRESSION, $this->phraseMatches($allWords, $exact, $exactMatches)];
        yield [self::NIVEAU_TOUS_LES_MOTS, $this->selectAll($exact, $exactMatches)];
        yield [self::NIVEAU_PLUPART_DES_MOTS, $this->selectMost($exact, $exactMatches)];
        yield [self::NIVEAU_NORMALISE, $this->normalizedMatches($allWords, $useful, $documents, $scope)];

        yield from $this->prefixTiers($useful, $documents, $scope);

        $typos = $this->typoGroups($useful, $terms, $documents, $corrections);
        if ($corrections !== []) {
            $matches = $this->matches($typos, $scope);
            yield [self::NIVEAU_FAUTES, $this->selectAll($typos, $matches) + $this->selectMost($typos, $matches)];
        }

        yield from $this->phraseTiers($intent, $documents, $scope, $corrections);
    }

    /**
     * @param  list<string>  $words
     * @return array<string, object>
     */
    private function terms(array $words): array
    {
        return DB::table('search_terms')->whereIn('terme', array_values($words))->get(['id', 'terme', 'df'])->keyBy('terme')->all();
    }

    /** Un mot présent presque partout (« bon », « etat ») ne doit pas décider d'un niveau. */
    private function withoutFrequentWords(array $useful, array $terms, int $documents): array
    {
        if ($documents < config('search.frequent_word_min_documents')) {
            return $useful;
        }

        $limit = $documents * config('search.frequent_word_ratio');
        $kept = array_values(array_filter($useful, fn ($word) => ($terms[$word]->df ?? 0) <= $limit));

        return $kept === [] ? $useful : $kept;
    }

    private function weight(int $df, int $documents): float
    {
        return log(1 + max($documents, 1) / max($df, 1));
    }

    private function group(array $ids, int $df, int $documents, string $word): array
    {
        return ['ids' => $ids, 'weight' => $this->weight($df, $documents), 'numeric' => ctype_digit($word)];
    }

    /** @return list<array{ids: list<int>, weight: float, numeric: bool}> */
    private function exactGroups(array $useful, array $terms, int $documents): array
    {
        return array_map(
            fn ($word) => $this->group(isset($terms[$word]) ? [$terms[$word]->id] : [], $terms[$word]->df ?? 0, $documents, $word),
            $useful
        );
    }

    /**
     * Pour chaque annonce, la meilleure valeur trouvée par groupe de mots.
     *
     * @return array<int, array<int, float>> article => [groupe => valeur du champ]
     */
    private function matches(array $groups, $scope): array
    {
        $groupsOfTerm = [];
        foreach ($groups as $index => $group) {
            foreach ($group['ids'] as $termId) {
                $groupsOfTerm[$termId][] = $index;
            }
        }

        if ($groupsOfTerm === []) {
            return [];
        }

        $fieldNames = array_flip(SearchIndexer::FIELDS);
        $fieldWeights = config('search.field_weights');
        $matches = [];

        $rows = DB::table('search_postings')
            ->whereIn('term_id', array_keys($groupsOfTerm))
            ->when($scope, fn ($query) => $query->whereIn('article_id', $scope))
            ->select('term_id', 'article_id', 'champ', 'occurrences')
            ->cursor();

        foreach ($rows as $row) {
            $value = $fieldWeights[$fieldNames[$row->champ]] * (1 + log(max($row->occurrences, 1)) / 4);

            foreach ($groupsOfTerm[$row->term_id] as $index) {
                $matches[$row->article_id][$index] = max($matches[$row->article_id][$index] ?? 0, $value);
            }
        }

        return $matches;
    }

    /** @return array<int, float> article => pertinence entre 0 et 1 */
    private function selectAll(array $groups, array $matches): array
    {
        $count = count($groups);

        return $this->relevance($groups, array_filter($matches, fn ($matched) => count($matched) === $count));
    }

    private function selectMost(array $groups, array $matches): array
    {
        if (count($groups) < 2) {
            return [];
        }

        $total = array_sum(array_column($groups, 'weight'));
        $minimum = $total * config('search.min_weight_share');

        return $this->relevance($groups, array_filter($matches, function ($matched) use ($groups, $minimum) {
            $weight = 0;
            $hasWord = false;

            foreach (array_keys($matched) as $index) {
                $weight += $groups[$index]['weight'];
                $hasWord = $hasWord || ! $groups[$index]['numeric'];
            }

            return $hasWord && $weight >= $minimum; // un nombre seul ne classe jamais une annonce
        }));
    }

    private function relevance(array $groups, array $matches): array
    {
        $total = array_sum(array_column($groups, 'weight')) ?: 1;
        $relevance = [];

        foreach ($matches as $articleId => $matched) {
            $score = 0;
            foreach ($matched as $index => $value) {
                $score += $groups[$index]['weight'] * $value;
            }
            $relevance[$articleId] = min($score / $total, 1);
        }

        return $relevance;
    }

    private function phraseMatches(array $allWords, array $exact, array $exactMatches): array
    {
        $found = $this->selectAll($exact, $exactMatches);

        if (count($allWords) < 2 || $found === []) {
            return $found;
        }

        $like = '% ' . implode(' ', $allWords) . ' %';
        $kept = [];

        foreach (array_chunk(array_keys($found), 1000) as $chunk) {
            $ids = DB::table('search_documents')
                ->whereIn('article_id', $chunk)
                ->where(function ($query) use ($like) {
                    foreach (array_keys(SearchIndexer::FIELDS) as $field) {
                        $query->orWhere($field, 'like', $like);
                    }
                })
                ->pluck('article_id');

            foreach ($ids as $id) {
                $kept[$id] = $found[$id];
            }
        }

        return $kept;
    }

    /** Niveau 4 : pluriels et racines, mots collés (« iphone13 ») ou coupés (« i phone »). */
    private function normalizedMatches(array $allWords, array $useful, int $documents, $scope): array
    {
        $found = [];

        foreach ($this->rewrites($allWords, $useful) as $words) {
            $groups = $this->stemGroups($words, $documents);
            $matches = $this->matches($groups, $scope);
            $found += $this->selectAll($groups, $matches) + $this->selectMost($groups, $matches);
        }

        return $found;
    }

    /** @return list<list<string>> la requête, plus ses variantes découpées ou recollées */
    private function rewrites(array $allWords, array $useful): array
    {
        $rewrites = [$useful];

        $split = [];
        foreach ($useful as $word) {
            array_push($split, ...$this->pieces($word));
        }
        $split = array_values(array_filter($split, fn ($word) => ! $this->text->isStopWord($word)));
        if ($split !== [] && $split !== $useful) {
            $rewrites[] = $split;
        }

        for ($i = 0; $i < count($allWords) - 1; $i++) {
            $joined = $allWords[$i] . $allWords[$i + 1];

            if (DB::table('search_terms')->where('terme', $joined)->exists()) {
                $words = $allWords;
                array_splice($words, $i, 2, [$joined]);
                $rewrites[] = array_values(array_filter($words, fn ($word) => ! $this->text->isStopWord($word)));
            }
        }

        return $rewrites;
    }

    /** @return list<string> */
    private function pieces(string $word): array
    {
        $pieces = [];
        foreach ($this->text->splitLettersAndDigits($word) as $piece) {
            array_push($pieces, ...(ctype_digit($piece) ? [$piece] : $this->splitOnVocabulary($piece)));
        }

        return $pieces;
    }

    /**
     * « chaussureslanvin » → [chaussures, lanvin], si ces deux mots existent dans les annonces.
     *
     * @return list<string>
     */
    private function splitOnVocabulary(string $word): array
    {
        $length = strlen($word);

        if ($length < 6 || DB::table('search_terms')->where('terme', $word)->exists()) {
            return [$word];
        }

        $candidates = [];
        for ($i = 2; $i <= $length - 2; $i++) {
            $candidates[$i] = [substr($word, 0, $i), substr($word, $i)];
        }

        $counts = DB::table('search_terms')->whereIn('terme', array_merge(...array_values($candidates)))->pluck('df', 'terme')->all();
        $best = null;
        $bestScore = 0;

        foreach ($candidates as [$left, $right]) {
            $score = min($this->pieceCount($left, $counts), $this->pieceCount($right, $counts));

            if ($score > $bestScore) {
                [$best, $bestScore] = [[$left, $right], $score];
            }
        }

        return $best ?? [$word];
    }

    /** Un mot vide compte pour un découpage valide (« iphonese » → iphone + se), sans plus. */
    private function pieceCount(string $piece, array $counts): int
    {
        return $counts[$piece] ?? ($this->text->isStopWord($piece) ? 1 : 0);
    }

    private function stemGroups(array $words, int $documents): array
    {
        $stems = array_map(fn ($word) => $this->text->stem($word), $words);
        $terms = DB::table('search_terms')->whereIn('racine', $stems)->get(['id', 'racine', 'df'])->groupBy('racine');

        return array_map(function ($word, $stem) use ($terms, $documents) {
            $variants = $terms->get($stem, collect());

            return $this->group($variants->pluck('id')->all(), (int) $variants->sum('df'), $documents, $word);
        }, $words, $stems);
    }

    /**
     * Niveau 5 : dernier mot en cours de frappe, puis tous les mots tronqués,
     * puis dernier mot tronqué avec une faute (« ell » → « ele… » → electronique).
     */
    private function prefixTiers(array $useful, int $documents, $scope): Generator
    {
        $last = array_key_last($useful);
        $lastWord = $useful[$last];

        if (ctype_digit($lastWord)) {
            return;
        }

        $stems = $this->stemGroups($useful, $documents);

        $groups = $stems;
        $groups[$last] = $this->termGroup($this->prefixTerms($lastWord), $lastWord, $documents);
        yield [self::NIVEAU_DEBUT_DE_MOT, $this->selectAll($groups, $this->matches($groups, $scope))];

        if (count($useful) > 1) {
            $prefixes = array_map(fn ($word) => $this->termGroup($this->prefixTerms($word), $word, $documents), $useful);
            yield [self::NIVEAU_DEBUT_DE_MOT, $this->selectAll($prefixes, $this->matches($prefixes, $scope))];
        }

        $groups = $stems;
        $groups[$last] = $this->termGroup($this->fuzzyPrefixTerms($lastWord), $lastWord, $documents);
        yield [self::NIVEAU_DEBUT_DE_MOT, $this->selectAll($groups, $this->matches($groups, $scope))];
    }

    private function termGroup($terms, string $word, int $documents): array
    {
        return $this->group($terms->pluck('id')->all(), (int) $terms->sum('df'), $documents, $word);
    }

    private function prefixTerms(string $word)
    {
        if (strlen($word) < config('search.prefix_min_length')) {
            return collect();
        }

        return DB::table('search_terms')
            ->where('terme', 'like', $word . '%')
            ->where('df', '>', 0)
            ->orderByDesc('df')
            ->limit(config('search.prefix_max_terms'))
            ->get(['id', 'terme', 'df']);
    }

    /** Mots dont le début ressemble à la saisie, limités aux mots fréquents pour éviter le bruit. */
    private function fuzzyPrefixTerms(string $word)
    {
        $length = strlen($word);

        if ($length < 3 || $length > 8) {
            return collect();
        }

        return DB::table('search_terms')
            ->where('terme', 'like', $word[0] . '%')
            ->where('longueur', '>=', $length)
            ->where('df', '>=', config('search.fuzzy_prefix_min_df'))
            ->get(['id', 'terme', 'df'])
            ->filter(fn ($term) => $this->text->distance($word, substr($term->terme, 0, $length)) <= 1)
            ->sortByDesc('df')
            ->take(config('search.fuzzy_prefix_max_terms'))
            ->values();
    }

    /**
     * Niveau 6 : la requête d'origine reste cherchée, les mots corrigés s'y ajoutent.
     *
     * @param  array<string, string>  $corrections  rempli avec les corrections retenues
     */
    private function typoGroups(array $useful, array $terms, int $documents, array &$corrections): array
    {
        $groups = [];

        foreach ($useful as $word) {
            $ids = isset($terms[$word]) ? [$terms[$word]->id] : [];
            $df = $terms[$word]->df ?? 0;

            if ($df === 0 && $found = $this->corrections($word)) {
                $corrections[$word] = $found[0]->terme;
                $ids = [...$ids, ...array_column($found, 'id')];
                $df = (int) array_sum(array_column($found, 'df'));
            }

            $groups[] = $this->group($ids, $df, $documents, $word);
        }

        return $groups;
    }

    /**
     * Mots du catalogue proches d'un mot inconnu : même prononciation, ou peu de fautes.
     * Le plus proche d'abord, puis le plus fréquent.
     *
     * @return list<object>
     */
    private function corrections(string $word): array
    {
        $allowed = $this->text->allowedTypos($word);

        if ($allowed === 0 || preg_match('/[0-9]/', $word)) {
            return [];
        }

        $length = strlen($word);
        $phonetic = $this->text->phonetic($word);

        $candidates = DB::table('search_terms')
            ->where('df', '>', 0)
            ->where('terme', '!=', $word)
            ->where(function ($query) use ($length, $allowed, $phonetic) {
                $query->whereBetween('longueur', [$length - $allowed, $length + $allowed])
                    ->orWhere('phonetique', $phonetic);
            })
            ->get(['id', 'terme', 'df', 'phonetique']);

        $found = [];
        foreach ($candidates as $term) {
            if ($term->phonetique === $phonetic) {
                $found[] = [$term, 0];

                continue;
            }

            // levenshtein() est bien plus rapide : il écarte d'abord les mots trop éloignés.
            if (levenshtein($word, $term->terme) > $allowed + 1) {
                continue;
            }

            $distance = $this->text->distance($word, $term->terme);

            if ($distance > $allowed || ($distance === 3 && substr($word, 0, 2) !== substr($term->terme, 0, 2))) {
                continue;
            }

            $found[] = [$term, $distance];
        }

        usort($found, fn ($a, $b) => [$a[1], $b[0]->df, $a[0]->terme] <=> [$b[1], $a[0]->df, $b[0]->terme]);

        return array_column(array_slice($found, 0, config('search.max_corrections')), 0);
    }

    /**
     * Niveau 7 : mots-clés de la phrase, mots inconnus ignorés, puis retrait progressif
     * des moins importants. Un nombre part avant le mot qu'il précise, et ne reste jamais seul.
     */
    private function phraseTiers(QueryIntent $intent, int $documents, $scope, array &$corrections): Generator
    {
        $keywords = [];
        foreach (array_slice($intent->words, 0, config('search.max_useful_words')) as $word) {
            $group = $this->keywordGroup($word, $documents, $corrections);

            if ($group['ids'] !== []) {
                $keywords[] = $group;
            }
        }

        while (array_filter($keywords, fn ($group) => ! $group['numeric']) !== []) {
            yield [self::NIVEAU_PHRASE, $this->selectAll($keywords, $this->matches($keywords, $scope))];

            $weakest = array_key_first(array_filter($keywords, fn ($group) => $group['numeric']));

            if ($weakest === null) {
                $weights = array_column($keywords, 'weight');
                $weakest = array_search(min($weights), $weights, true);
            }

            array_splice($keywords, $weakest, 1);
        }
    }

    private function keywordGroup(string $word, int $documents, array &$corrections): array
    {
        $group = $this->stemGroups([$word], $documents)[0];

        if ($group['ids'] === [] && $found = $this->corrections($word)) {
            $corrections[$word] = $found[0]->terme;

            return $this->group(array_column($found, 'id'), (int) array_sum(array_column($found, 'df')), $documents, $word);
        }

        return $group;
    }

    /**
     * Classe les annonces d'un même niveau : pertinence, signaux métier, ville et prix demandés.
     * L'ordre est stable : à égalité, la plus récente, puis le plus grand identifiant.
     *
     * @param  array<int, float>  $relevance
     * @return list<int>
     */
    private function ranked(array $relevance, QueryIntent $intent, ?string $userCity): array
    {
        $ranking = config('search.ranking');
        $now = time();
        $medians = $intent->prixBas ? DB::table('search_category_stats')->pluck('prix_median', 'sous_categorie_id')->all() : [];
        $photoFields = count(Article::PHOTO_FIELDS);
        $rows = [];

        foreach (array_chunk(array_keys($relevance), 1000) as $chunk) {
            $documents = DB::table('search_documents')->whereIn('article_id', $chunk)
                ->get(['article_id', 'boosted_until', 'certifie', 'photos', 'publie_le', 'ville', 'prix', 'sous_categorie_id']);

            foreach ($documents as $document) {
                $published = $document->publie_le ? strtotime($document->publie_le) : $now;
                $ageInDays = (int) (max($now - $published, 0) / 86400);
                $price = (float) $document->prix;
                $lieu = " {$document->ville} ";

                $score = $ranking['pertinence'] * $relevance[$document->article_id]
                    + ($document->boosted_until && strtotime($document->boosted_until) > $now ? $ranking['boost'] : 0)
                    + ($document->certifie ? $ranking['certifie'] : 0)
                    + $ranking['fraicheur'] * 0.5 ** ($ageInDays / $ranking['fraicheur_demi_vie_jours'])
                    + $ranking['photos'] * min($document->photos, $photoFields) / $photoFields;

                if ($intent->ville !== null && str_contains($lieu, " {$intent->ville} ")) {
                    $score += $ranking['ville'];
                } elseif ($userCity !== null && str_contains($lieu, " {$userCity} ")) {
                    $score += $ranking['ville_utilisateur'];
                }

                if (($intent->prixMin !== null || $intent->prixMax !== null)
                    && $price >= ($intent->prixMin ?? 0) && $price <= ($intent->prixMax ?? INF)) {
                    $score += $ranking['prix'];
                }

                if ($intent->prixBas && isset($medians[$document->sous_categorie_id]) && $price < (float) $medians[$document->sous_categorie_id]) {
                    $score += $ranking['prix'];
                }

                $rows[] = [$document->article_id, $score, $published];
            }
        }

        usort($rows, fn ($a, $b) => [$b[1], $b[2], $b[0]] <=> [$a[1], $a[2], $a[0]]);

        return array_column($rows, 0);
    }

    /**
     * Filet : tout ce que l'ancien moteur trouvait, y compris en milieu de mot.
     * Cherche le texte tel quel dans tous les champs indexés, sur une seule table :
     * les textes normalisés d'un côté et de l'autre couvrent l'ancienne recherche large.
     *
     * @return list<int>
     */
    private function safetyNet(array $allWords, $scope): array
    {
        if ($allWords === []) {
            return [];
        }

        $like = '%' . implode(' ', $allWords) . '%';

        return DB::table('search_documents')
            ->where(function ($query) use ($like) {
                foreach (array_keys(SearchIndexer::FIELDS) as $field) {
                    $query->orWhere($field, 'like', $like);
                }
            })
            ->when($scope, fn ($query) => $query->whereIn('article_id', $scope))
            ->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > ?) DESC', [now()])
            ->orderByDesc('publie_le')
            ->orderByDesc('article_id')
            ->pluck('article_id')
            ->all();
    }

    /**
     * Secours : catégorie la plus proche des mots tapés, sinon annonces populaires et récentes.
     *
     * @return array{list<int>, ?string} annonces, nom de la catégorie proche
     */
    private function fallback(array $allWords, Builder $base): array
    {
        $limit = config('search.fallback_size');
        $category = $this->closestCategory($allWords);

        if ($category) {
            $ids = (clone $base)->where('sous_categorie_id', $category->id)
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit($limit)->pluck('id')->all();

            if ($ids !== []) {
                return [$ids, $category->nom];
            }
        }

        $popular = DB::table('stat_visites')
            ->where('type', 'article')
            ->where('created_at', '>=', now()->subDays(config('search.popular_days')))
            ->whereNotNull('article_id')
            ->groupBy('article_id')
            ->orderByRaw('count(*) desc')
            ->limit($limit * 3)
            ->pluck('article_id')
            ->all();

        $available = (clone $base)->whereIn('id', $popular)->pluck('id')->all();
        $ids = array_values(array_slice(array_intersect($popular, $available), 0, $limit));

        if (count($ids) < $limit) {
            $recent = (clone $base)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit($limit)->pluck('id')->all();
            $ids = array_slice(array_values(array_unique([...$ids, ...$recent])), 0, $limit);
        }

        return [$ids, null];
    }

    private function closestCategory(array $allWords): ?SousCategorie
    {
        $words = array_filter($allWords, fn ($word) => ! $this->text->isStopWord($word) && strlen($word) >= 3);

        if ($words === []) {
            return null;
        }

        $best = null;
        $bestDistance = PHP_INT_MAX;

        foreach (SousCategorie::query()->get(['id', 'nom']) as $category) {
            foreach ($this->text->words($category->nom) as $name) {
                foreach ($words as $word) {
                    $distance = str_starts_with($name, $word) ? 0 : $this->text->distance($word, $name);

                    // Une faute de plus que les niveaux précédents : à ce stade, ils n'ont rien trouvé.
                    if ($distance <= $this->text->allowedTypos($word) + 1 && $distance < $bestDistance) {
                        [$best, $bestDistance] = [$category, $distance];
                    }
                }
            }
        }

        return $best;
    }

    /** @return list<string> villes normalisées connues par les annonces */
    private function cities(): array
    {
        return Cache::remember('search:cities:' . Cache::get(SearchIndexer::VERSION_KEY), 3600, function () {
            return DB::table('search_documents')->distinct()->pluck('ville')->filter()->values()->all();
        });
    }

    private function comprehension(QueryIntent $intent, ?int $niveau, array $corrections): ?array
    {
        if (! $intent->hasCriteria() && $corrections === [] && ($niveau ?? 0) < self::NIVEAU_PHRASE) {
            return null;
        }

        if ($niveau === self::NIVEAU_SECOURS) {
            return null;
        }

        return [
            'mots' => array_values(array_map(fn ($word) => $corrections[$word] ?? $word, $intent->words)),
            'ville' => $intent->ville,
            'prixMin' => $intent->prixMin,
            'prixMax' => $intent->prixMax,
            'prixBas' => $intent->prixBas,
        ];
    }
}
