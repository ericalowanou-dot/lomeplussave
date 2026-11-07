<?php

namespace App\Services\Search;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Tient l'index de recherche à jour. Seules les annonces approuvées y figurent.
 * Une erreur d'indexation ne doit jamais empêcher d'enregistrer une annonce.
 */
class SearchIndexer
{
    public const VERSION_KEY = 'search:version';

    /** Numéro de champ stocké dans search_postings. */
    public const FIELDS = ['titre' => 1, 'categories' => 2, 'lieu' => 3, 'vendeur' => 4, 'description' => 5];

    private const RELATIONS = ['user:id,name,ville,certifie', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'];

    public function __construct(private TextNormalizer $text)
    {
    }

    /** À appeler après une écriture : ne lève jamais d'exception. */
    public function sync(int $articleId): void
    {
        DB::afterCommit(function () use ($articleId) {
            $this->safely(fn () => $this->index($articleId));
        });
    }

    public function forget(int $articleId): void
    {
        DB::afterCommit(function () use ($articleId) {
            $this->safely(function () use ($articleId) {
                $this->remove($articleId, true);
                $this->touch();
            });
        });
    }

    /** Réindexe les annonces approuvées désignées par une condition (vendeur, catégorie). */
    public function syncWhere(callable $condition): void
    {
        DB::afterCommit(function () use ($condition) {
            $this->safely(function () use ($condition) {
                $query = Article::query()->where('status', 'approved');
                $condition($query);
                $query->select('id')->chunkById(200, function ($articles) {
                    $articles->each(fn ($article) => $this->index($article->id));
                });
            });
        });
    }

    public function index(int $articleId, bool $refreshCounts = true): void
    {
        $article = Article::with(self::RELATIONS)->find($articleId);

        if (! $article || $article->status !== 'approved') {
            $this->remove($articleId, $refreshCounts);
            $this->touch();

            return;
        }

        $fields = $this->fields($article);
        $occurrences = $this->occurrences($fields);
        $termIds = $this->termIds(array_map('strval', array_keys($occurrences)));

        DB::transaction(function () use ($article, $fields, $occurrences, $termIds, $refreshCounts) {
            $previous = DB::table('search_postings')->where('article_id', $article->id)->pluck('term_id')->all();
            DB::table('search_postings')->where('article_id', $article->id)->delete();

            $rows = [];
            foreach ($occurrences as $word => $byField) {
                foreach ($byField as $field => $count) {
                    $rows[] = [
                        'term_id' => $termIds[$word],
                        'article_id' => $article->id,
                        'champ' => $field,
                        'occurrences' => min($count, 65535),
                    ];
                }
            }
            foreach (array_chunk($rows, 300) as $chunk) {
                DB::table('search_postings')->insert($chunk);
            }

            DB::table('search_documents')->updateOrInsert(['article_id' => $article->id], $this->padded($fields) + [
                'boosted_until' => $article->boosted_until,
                'certifie' => (bool) $article->user?->certifie,
                'photos' => count(array_filter(array_map(fn ($field) => $article->getAttribute($field), Article::PHOTO_FIELDS))),
                'publie_le' => $article->created_at,
                'ville' => substr($fields['lieu'], 0, 191),
                'prix' => $article->prix_ht,
                'sous_categorie_id' => $article->sous_categorie_id,
            ]);

            if ($refreshCounts) {
                $this->refreshCounts(array_values(array_unique([...$previous, ...array_values($termIds)])));
            }
        });

        $this->touch();
    }

    /** Reconstruit tout l'index à partir des annonces. */
    public function rebuild(?callable $onProgress = null): int
    {
        DB::table('search_postings')->whereNotIn('article_id', Article::where('status', 'approved')->select('id'))->delete();
        DB::table('search_documents')->whereNotIn('article_id', Article::where('status', 'approved')->select('id'))->delete();

        $count = 0;
        Article::where('status', 'approved')->select('id')->chunkById(200, function ($articles) use (&$count, $onProgress) {
            DB::transaction(function () use ($articles, &$count) {
                foreach ($articles as $article) {
                    $this->index($article->id, false);
                    $count++;
                }
            });
            if ($onProgress) {
                $onProgress($count);
            }
        });

        $this->refreshStatistics();

        return $count;
    }

    /**
     * Passage de nuit : remet l'index d'accord avec les annonces, au cas où une mise à jour
     * aurait échoué, puis recalcule les poids et les prix médians.
     *
     * @return array{ajoutees: int, retirees: int}
     */
    public function refresh(): array
    {
        $missing = Article::where('status', 'approved')
            ->whereNotIn('id', DB::table('search_documents')->select('article_id'))
            ->pluck('id');
        $missing->each(fn ($id) => $this->index($id, false));

        $stale = DB::table('search_documents')
            ->whereNotIn('article_id', Article::where('status', 'approved')->select('id'))
            ->pluck('article_id');
        $stale->each(fn ($id) => $this->remove($id, false));

        $this->refreshStatistics();

        return ['ajoutees' => $missing->count(), 'retirees' => $stale->count()];
    }

    private function refreshStatistics(): void
    {
        $this->refreshCounts();
        DB::table('search_terms')->where('df', 0)->delete();
        $this->refreshMedians();
        $this->touch();
    }

    /** La médiane, pas la moyenne : quelques annonces très chères fausseraient le résultat. */
    private function refreshMedians(): void
    {
        $prices = [];
        foreach (DB::table('search_documents')->whereNotNull('sous_categorie_id')->select('sous_categorie_id', 'prix')->cursor() as $row) {
            $prices[$row->sous_categorie_id][] = (float) $row->prix;
        }

        $rows = [];
        foreach ($prices as $categoryId => $values) {
            sort($values);
            $middle = (int) (count($values) / 2);
            $median = count($values) % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
            $rows[] = ['sous_categorie_id' => $categoryId, 'prix_median' => $median];
        }

        DB::transaction(function () use ($rows) {
            DB::table('search_category_stats')->delete();
            DB::table('search_category_stats')->insert($rows);
        });
    }

    /** @return array<string, string> */
    private function fields(Article $article): array
    {
        $sousCategorie = $article->sousCategorie;

        return [
            'titre' => $this->text->normalize((string) $article->titre),
            'categories' => $this->text->normalize($sousCategorie?->nom . ' ' . $sousCategorie?->categorie?->nom),
            'lieu' => $this->text->normalize((string) $article->lieu),
            'vendeur' => $this->text->normalize($article->user?->name . ' ' . $article->user?->ville),
            'description' => $this->text->normalize((string) $article->description),
        ];
    }

    /**
     * Espaces aux extrémités : une expression se cherche ensuite avec LIKE '% mot mot %'.
     *
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    private function padded(array $fields): array
    {
        return array_map(fn ($value) => " {$value} ", $fields);
    }

    /**
     * Les mots vides restent dans le texte des documents, sans entrée dans l'index.
     *
     * @param  array<string, string>  $fields
     * @return array<string, array<int, int>> mot => [champ => nombre d'occurrences]
     */
    private function occurrences(array $fields): array
    {
        $occurrences = [];
        foreach ($fields as $name => $value) {
            foreach ($this->text->words($value) as $word) {
                if (strlen($word) <= TextNormalizer::MAX_WORD_LENGTH && ! $this->text->isStopWord($word)) {
                    $occurrences[$word][self::FIELDS[$name]] = ($occurrences[$word][self::FIELDS[$name]] ?? 0) + 1;
                }
            }
        }

        return $occurrences;
    }

    /**
     * @param  list<string>  $words
     * @return array<string, int>
     */
    private function termIds(array $words): array
    {
        $ids = [];
        foreach (array_chunk($words, 300) as $chunk) {
            DB::table('search_terms')->insertOrIgnore(array_map(
                fn ($word) => [
                    'terme' => $word,
                    'racine' => $this->text->stem($word),
                    'phonetique' => $this->text->phonetic($word),
                    'longueur' => strlen($word),
                ],
                $chunk
            ));
            $ids += DB::table('search_terms')->whereIn('terme', $chunk)->pluck('id', 'terme')->all();
        }

        return $ids;
    }

    /** @param  list<int>|null  $termIds  null : tout le vocabulaire */
    private function refreshCounts(?array $termIds = null): void
    {
        $count = DB::raw('(select count(distinct search_postings.article_id) from search_postings where search_postings.term_id = search_terms.id)');

        if ($termIds === null) {
            DB::table('search_terms')->update(['df' => $count]);

            return;
        }

        foreach (array_chunk($termIds, 300) as $chunk) {
            DB::table('search_terms')->whereIn('id', $chunk)->update(['df' => $count]);
        }
    }

    private function remove(int $articleId, bool $refreshCounts): void
    {
        DB::transaction(function () use ($articleId, $refreshCounts) {
            $termIds = DB::table('search_postings')->where('article_id', $articleId)->pluck('term_id')->unique()->values()->all();
            DB::table('search_postings')->where('article_id', $articleId)->delete();
            DB::table('search_documents')->where('article_id', $articleId)->delete();

            if ($refreshCounts && $termIds !== []) {
                $this->refreshCounts($termIds);
            }
        });
    }

    /** Toute modification invalide les résultats gardés en cache. */
    private function touch(): void
    {
        Cache::forever(self::VERSION_KEY, (string) microtime(true));
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('Indexation de la recherche impossible : ' . $e->getMessage());
        }
    }
}
