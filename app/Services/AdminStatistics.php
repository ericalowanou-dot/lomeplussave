<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\SousCategorie;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Calcule les statistiques de la page admin « Statistiques ».
 *
 * Toutes les requêtes restent compatibles MySQL et SQLite : les regroupements
 * se font par DATE(created_at) en SQL, puis par semaine / mois en PHP.
 * Les requêtes groupées ne sélectionnent que des identifiants et des agrégats
 * (compatible ONLY_FULL_GROUP_BY) ; les libellés sont chargés ensuite.
 *
 * Filtres Ville / Catégorie : ils s'appliquent à tout ce qui dépend d'une annonce
 * (publications, vues, likes, vendeurs, classements). Les inscriptions et les
 * recherches ne sont rattachées à aucune annonce : elles restent globales.
 */
class AdminStatistics
{
    public const PERIODS = [
        'aujourdhui' => "Aujourd'hui",
        'hier' => 'Hier',
        '7j' => '7 derniers jours',
        '30j' => '30 derniers jours',
        '90j' => '90 derniers jours',
        'ce_mois' => 'Ce mois-ci',
        'mois_dernier' => 'Mois dernier',
        '12m' => '12 derniers mois',
        'annee' => 'Cette année',
        'annee_derniere' => 'Année dernière',
        'tout' => 'Depuis le début',
        'perso' => 'Personnalisée',
    ];

    public const GRANULARITIES = [
        'jour' => 'Par jour',
        'semaine' => 'Par semaine',
        'mois' => 'Par mois',
    ];

    public const TOP_SIZES = [10, 25, 50];

    private const CACHE_TTL = 300;

    /** Nombre maximum de lignes lues pour les calculs faits en PHP (mots-clés, heatmap). */
    private const SAMPLE_LIMIT = 50000;

    private const STOPWORDS = [
        'les', 'des', 'une', 'pour', 'avec', 'sans', 'dans', 'sur', 'par', 'est', 'aux', 'mon', 'mes',
        'ton', 'tes', 'son', 'ses', 'nos', 'vos', 'leur', 'leurs', 'qui', 'que', 'quoi', 'dont', 'ou',
        'et', 'en', 'du', 'de', 'la', 'le', 'un', 'au', 'ce', 'ces', 'cet', 'cette', 'tres', 'plus',
        'moins', 'tout', 'tous', 'toute', 'toutes', 'bien', 'pas', 'non', 'oui', 'vend', 'vends',
        'vendre', 'vente', 'prix', 'fcfa', 'cfa', 'xof', 'the', 'and', 'for', 'with', 'avoir', 'etre',
        'chez', 'via', 'deja', 'encore', 'aussi', 'comme', 'mais', 'donc', 'car', 'ici', 'disponible',
        'dispo', 'urgent', 'occasion',
    ];

    public readonly CarbonImmutable $from;
    public readonly CarbonImmutable $to;
    public readonly CarbonImmutable $previousFrom;
    public readonly CarbonImmutable $previousTo;
    public readonly string $granularity;

    private bool $hasVisites = false;
    private bool $hasRecherches = false;

    /** Valeurs brutes de `articles.lieu` correspondant à la ville filtrée (mémoïsé). */
    private ?array $lieuValues = null;

    /** Sous-catégories correspondant au filtre catégorie (mémoïsé). */
    private ?array $sousCategorieIds = null;

    public function __construct(
        public readonly string $period,
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $granularity,
        public readonly int $top,
        public readonly ?string $ville = null,
        public readonly ?int $categorieId = null,
        public readonly ?int $sousCategorieId = null,
    ) {
        $this->from = $from->startOfDay();
        $this->to = $to->endOfDay();

        $days = $this->from->diffInDays($this->to->startOfDay()) + 1;
        $this->previousTo = $this->from->subSecond();
        $this->previousFrom = $this->from->subDays((int) $days);

        $this->granularity = $this->safeGranularity($granularity, (int) $days);
    }

    public static function fromRequest(Request $request): self
    {
        $du = self::parseDate($request->query('du'));
        $au = self::parseDate($request->query('au'));

        $period = $request->query('periode');
        if (! array_key_exists($period, self::PERIODS)) {
            // Des dates sans période explicite = sélection personnalisée (calendrier, courbes)
            $period = ($du || $au) ? 'perso' : '30j';
        }

        $today = CarbonImmutable::today();
        $to = $today;

        switch ($period) {
            case 'aujourdhui':
                $from = $today;
                break;
            case 'hier':
                $from = $to = $today->subDay();
                break;
            case '7j':
                $from = $today->subDays(6);
                break;
            case '90j':
                $from = $today->subDays(89);
                break;
            case 'ce_mois':
                $from = $today->startOfMonth();
                break;
            case 'mois_dernier':
                $from = $today->subMonthNoOverflow()->startOfMonth();
                $to = $from->endOfMonth()->startOfDay();
                break;
            case '12m':
                $from = $today->subMonthsNoOverflow(12)->addDay();
                break;
            case 'annee':
                $from = $today->startOfYear();
                break;
            case 'annee_derniere':
                $from = $today->subYear()->startOfYear();
                $to = $from->endOfYear()->startOfDay();
                break;
            case 'tout':
                $from = self::firstActivityDate() ?? $today->subDays(29);
                break;
            case 'perso':
                $from = $du ?? $today->subDays(29);
                $to = $au ?? $today;
                if ($from->greaterThan($to)) {
                    [$from, $to] = [$to, $from];
                }
                break;
            case '30j':
            default:
                $from = $today->subDays(29);
        }

        $days = $from->diffInDays($to) + 1;
        $granularity = array_key_exists($request->query('par'), self::GRANULARITIES)
            ? $request->query('par')
            : ($days <= 45 ? 'jour' : ($days <= 180 ? 'semaine' : 'mois'));

        $top = (int) $request->query('top', 10);
        if (! in_array($top, self::TOP_SIZES, true)) {
            $top = 10;
        }

        // Filtres : ville (texte) et catégorie (« c12 » = catégorie, « s33 » = sous-catégorie)
        $ville = trim((string) $request->query('ville', ''));
        $ville = $ville !== '' && StatTracker::normalize($ville) !== '' ? mb_substr($ville, 0, 100) : null;

        $categorieId = $sousCategorieId = null;
        if (preg_match('/^([cs])(\d+)$/', (string) $request->query('categorie', ''), $m)) {
            $m[1] === 'c' ? $categorieId = (int) $m[2] : $sousCategorieId = (int) $m[2];
        }

        return new self($period, $from, $to, $granularity, $top, $ville, $categorieId, $sousCategorieId);
    }

    /**
     * Statistiques complètes (mises en cache 5 minutes).
     */
    public function all(bool $refresh = false): array
    {
        // Vérifié à chaque affichage et inclus dans la clé : après un `php artisan migrate`,
        // un résultat calculé sans les tables de suivi n'est jamais resservi depuis le cache.
        $this->hasVisites = Schema::hasTable('stat_visites');
        $this->hasRecherches = Schema::hasTable('stat_recherches');

        $key = 'admin_statistics:v3:' . md5(implode('|', [
            $this->from->toDateString(), $this->to->toDateString(), $this->granularity, $this->top,
            (int) $this->hasVisites, (int) $this->hasRecherches, $this->period === 'tout' ? 'tout' : '',
            StatTracker::normalize((string) $this->ville), $this->categorieId, $this->sousCategorieId,
        ]));

        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, self::CACHE_TTL, fn () => $this->compute());
    }

    public function hasArticleFilter(): bool
    {
        return $this->ville !== null || $this->categorieId !== null || $this->sousCategorieId !== null;
    }

    /**
     * Libellé lisible des filtres actifs, ex. « Kara · Électronique › Téléphones ».
     */
    public function filterLabel(): ?string
    {
        $parts = [];

        if ($this->ville !== null) {
            $parts[] = $this->ville;
        }

        if ($this->sousCategorieId !== null) {
            $sous = SousCategorie::with('categorie:id,nom')->find($this->sousCategorieId);
            $parts[] = $sous ? trim(($sous->categorie?->nom ? $sous->categorie->nom . ' › ' : '') . $sous->nom) : 'Sous-catégorie supprimée';
        } elseif ($this->categorieId !== null) {
            $parts[] = Categorie::whereKey($this->categorieId)->value('nom') ?? 'Catégorie supprimée';
        }

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * Valeur du paramètre « categorie » à remettre dans les liens et le formulaire.
     */
    public function categorieParam(): ?string
    {
        return $this->sousCategorieId !== null ? 's' . $this->sousCategorieId
            : ($this->categorieId !== null ? 'c' . $this->categorieId : null);
    }

    /**
     * Villes proposées dans le filtre (orthographes fusionnées, les plus utilisées d'abord).
     *
     * @return array<int, string>
     */
    public static function villeOptions(): array
    {
        return Cache::remember('admin_statistics:villes', 600, function () {
            $villes = [];
            DB::table('articles')
                ->selectRaw('lieu, COUNT(*) as total')
                ->whereNotNull('lieu')
                ->groupBy('lieu')
                ->get()
                ->each(function ($row) use (&$villes) {
                    $label = trim((string) $row->lieu);
                    $key = StatTracker::normalize($label);
                    if ($key === '') {
                        return;
                    }
                    $villes[$key] ??= ['nom' => $label, 'total' => 0, 'best' => 0];
                    $villes[$key]['total'] += (int) $row->total;
                    if ((int) $row->total > $villes[$key]['best']) {
                        $villes[$key]['nom'] = $label;
                        $villes[$key]['best'] = (int) $row->total;
                    }
                });

            usort($villes, fn ($a, $b) => $b['total'] <=> $a['total']);

            return array_column($villes, 'nom');
        });
    }

    /**
     * Catégories et leurs sous-catégories pour le filtre.
     */
    public static function categorieOptions(): array
    {
        return Categorie::with(['sousCategories' => fn ($q) => $q->orderBy('nom')])
            ->orderBy('nom')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nom' => $c->nom,
                'sous' => $c->sousCategories->map(fn ($s) => ['id' => $s->id, 'nom' => $s->nom])->all(),
            ])
            ->all();
    }

    private function compute(): array
    {
        return [
            'generated_at' => now()->format('d/m/Y H:i'),
            'filtre' => $this->filterLabel(),
            'tracking' => [
                'visites' => $this->hasVisites,
                'recherches' => $this->hasRecherches,
                'depuis_visites' => $this->trackingSince('stat_visites', $this->hasVisites),
                'depuis_recherches' => $this->trackingSince('stat_recherches', $this->hasRecherches),
            ],
            'kpis' => $this->kpis(),
            'series' => $this->series(),
            'repartitions' => $this->repartitions(),
            'heatmaps' => $this->heatmaps(),
            'top_vendeurs' => $this->topPublishers(),
            'top_categories' => $this->topCategories(),
            'top_sous_categories' => $this->topSousCategories(),
            'top_villes' => $this->topVilles(),
            'top_mots' => $this->topKeywords(),
            'top_articles_vus' => $this->topViewedArticles(),
            'top_boutiques' => $this->topShops(),
            'top_articles_aimes' => $this->topLikedArticles(),
            'recherches' => $this->searches(),
        ];
    }

    // ------------------------------------------------------------------
    // Sources de données (avec filtres Ville / Catégorie)
    // ------------------------------------------------------------------

    /**
     * Restreint une requête qui contient la table `articles` aux annonces filtrées.
     */
    private function filterArticles(Builder $query): Builder
    {
        if ($this->ville !== null) {
            $query->whereIn('articles.lieu', $this->lieuValues());
        }

        if ($this->categorieId !== null || $this->sousCategorieId !== null) {
            $query->whereIn('articles.sous_categorie_id', $this->sousCategorieIds());
        }

        return $query;
    }

    private function lieuValues(): array
    {
        if ($this->lieuValues === null) {
            $wanted = StatTracker::normalize((string) $this->ville);
            $this->lieuValues = DB::table('articles')
                ->whereNotNull('lieu')
                ->distinct()
                ->pluck('lieu')
                ->filter(fn ($lieu) => StatTracker::normalize((string) $lieu) === $wanted)
                ->values()
                ->all();
        }

        return $this->lieuValues;
    }

    private function sousCategorieIds(): array
    {
        if ($this->sousCategorieIds === null) {
            $this->sousCategorieIds = $this->sousCategorieId !== null
                ? [$this->sousCategorieId]
                : DB::table('sous_categories')->where('categorie_id', $this->categorieId)->pluck('id')->all();
        }

        return $this->sousCategorieIds;
    }

    /** Annonces (filtrées). */
    private function articles(): Builder
    {
        return $this->filterArticles(DB::table('articles'));
    }

    /** Vues d'annonces (filtrées par les annonces vues). */
    private function articleVisits(): Builder
    {
        $query = DB::table('stat_visites')->where('stat_visites.type', 'article');

        if ($this->hasArticleFilter()) {
            $query->join('articles', 'articles.id', '=', 'stat_visites.article_id');
            $this->filterArticles($query);
        }

        return $query;
    }

    /** Visites de boutiques (filtrées : vendeurs ayant au moins une annonce correspondante). */
    private function shopVisits(): Builder
    {
        $query = DB::table('stat_visites')->where('stat_visites.type', 'boutique');

        if ($this->hasArticleFilter()) {
            $query->whereIn('stat_visites.vendeur_id', $this->articles()->select('articles.user_id'));
        }

        return $query;
    }

    /** Likes (filtrés par les annonces aimées). */
    private function likes(): Builder
    {
        $query = DB::table('article_user_like');

        if ($this->hasArticleFilter()) {
            $query->join('articles', 'articles.id', '=', 'article_user_like.article_id');
            $this->filterArticles($query);
        }

        return $query;
    }

    /**
     * Likes donnés pendant la période. Les anciens likes enregistrés sans date
     * (avant la correction de withTimestamps) n'ont pas de jour connu : ils ne
     * comptent que pour « Depuis le début ».
     */
    private function likedDuring(Builder $query, array $range, bool $includeUndated = false): Builder
    {
        $column = 'article_user_like.created_at';

        if ($includeUndated && $this->period === 'tout') {
            return $query->where(fn (Builder $w) => $w->whereBetween($column, $range)->orWhereNull($column));
        }

        return $query->whereBetween($column, $range);
    }

    private function period(): array
    {
        return [$this->from, $this->to];
    }

    private function previousPeriod(): array
    {
        return [$this->previousFrom, $this->previousTo];
    }

    // ------------------------------------------------------------------
    // Indicateurs clés
    // ------------------------------------------------------------------

    private function kpis(): array
    {
        $current = $this->period();
        $previous = $this->previousPeriod();

        $newUsers = DB::table('users')->whereBetween('created_at', $current)->count();
        $newUsersPrev = DB::table('users')->whereBetween('created_at', $previous)->count();

        $articles = $this->articles()->whereBetween('articles.created_at', $current)->count();
        $articlesPrev = $this->articles()->whereBetween('articles.created_at', $previous)->count();

        $activeSellers = $this->articles()->whereBetween('articles.created_at', $current)->distinct()->count('articles.user_id');
        $activeSellersPrev = $this->articles()->whereBetween('articles.created_at', $previous)->distinct()->count('articles.user_id');

        $likes = $this->likedDuring($this->likes(), $current, true)->count();
        $likesPrev = $this->likes()->whereBetween('article_user_like.created_at', $previous)->count();

        // Part des nouveaux inscrits de la période qui ont publié au moins une annonce
        $newSellers = $newUsers > 0
            ? DB::table('users')
                ->whereBetween('users.created_at', $current)
                ->whereExists(fn (Builder $q) => $q->select(DB::raw(1))->from('articles')->whereColumn('articles.user_id', 'users.id'))
                ->count()
            : 0;

        $totalUsers = DB::table('users')->count();

        $kpis = [
            'inscriptions' => $this->metric($newUsers, $newUsersPrev),
            'annonces' => $this->metric($articles, $articlesPrev),
            'vendeurs_actifs' => $this->metric($activeSellers, $activeSellersPrev),
            'likes' => $this->metric($likes, $likesPrev),
            'utilisateurs_total' => $totalUsers,
            'annonces_total' => $this->articles()->count(),
            'annonces_en_ligne' => $this->articles()->where('articles.status', 'approved')->count(),
            'annonces_en_attente' => $this->articles()->where('articles.status', 'pending')->count(),
            'annonces_boostees' => $this->articles()->where('articles.boosted_until', '>', now())->count(),
            'certifies' => DB::table('users')
                ->where('certifie', 1)
                ->where(fn ($q) => $q->whereNull('certifie_from')->orWhere('certifie_from', '<=', now()))
                ->where(fn ($q) => $q->whereNull('certifie_until')->orWhere('certifie_until', '>', now()))
                ->count(),
            'bloques' => DB::table('users')->where('is_blocked', true)->count(),
            'emails_verifies_pct' => $this->percent(DB::table('users')->whereNotNull('email_verified_at')->count(), $totalUsers),
            'conversion_vendeurs_pct' => $this->percent($newSellers, $newUsers),
            'prix_moyen' => (float) ($this->articles()->whereBetween('articles.created_at', $current)->where('articles.prix_ht', '>', 0)->avg('articles.prix_ht') ?? 0),
            'prix_median' => $this->medianPrice(),
            'vues_annonces' => $this->metric(0, 0),
            'visiteurs_uniques' => 0,
            'visites_boutiques' => $this->metric(0, 0),
            'recherches' => $this->metric(0, 0),
        ];

        if ($this->hasVisites) {
            $kpis['vues_annonces'] = $this->metric(
                $this->articleVisits()->whereBetween('stat_visites.created_at', $current)->count(),
                $this->articleVisits()->whereBetween('stat_visites.created_at', $previous)->count(),
            );
            $kpis['visites_boutiques'] = $this->metric(
                $this->shopVisits()->whereBetween('stat_visites.created_at', $current)->count(),
                $this->shopVisits()->whereBetween('stat_visites.created_at', $previous)->count(),
            );
            $visitors = $this->hasArticleFilter()
                ? $this->articleVisits()
                : DB::table('stat_visites');
            $kpis['visiteurs_uniques'] = $visitors->whereBetween('stat_visites.created_at', $current)->distinct()->count('stat_visites.visiteur_hash');
        }

        if ($this->hasRecherches) {
            $kpis['recherches'] = $this->metric(
                DB::table('stat_recherches')->whereBetween('created_at', $current)->count(),
                DB::table('stat_recherches')->whereBetween('created_at', $previous)->count(),
            );
        }

        return $kpis;
    }

    private function medianPrice(): float
    {
        $query = fn () => $this->articles()->whereBetween('articles.created_at', $this->period())->where('articles.prix_ht', '>', 0);

        $count = $query()->count();
        if ($count === 0) {
            return 0.0;
        }

        $values = $query()
            ->orderBy('articles.prix_ht')
            ->offset(intdiv($count - 1, 2))
            ->limit($count % 2 === 0 ? 2 : 1)
            ->pluck('articles.prix_ht');

        return (float) $values->avg();
    }

    // ------------------------------------------------------------------
    // Courbes dans le temps
    // ------------------------------------------------------------------

    private function series(): array
    {
        $buckets = $this->buckets();
        $empty = array_fill(0, count($buckets), 0);

        $series = [
            'labels' => array_values(array_column($buckets, 'label')),
            // Bornes réelles de chaque point (pour appliquer une sélection faite sur la courbe)
            'debuts' => array_values(array_column($buckets, 'start')),
            'fins' => array_values(array_column($buckets, 'end')),
            'inscriptions' => $this->bucketize($this->dailyCounts(DB::table('users'), 'users.created_at'), $buckets),
            'annonces' => $this->bucketize($this->dailyCounts($this->articles(), 'articles.created_at'), $buckets),
            'likes' => $this->bucketize($this->dailyCounts($this->likes(), 'article_user_like.created_at'), $buckets),
            'vues' => $empty,
            'visites_boutiques' => $empty,
            'recherches' => $empty,
        ];

        if ($this->hasVisites) {
            $series['vues'] = $this->bucketize($this->dailyCounts($this->articleVisits(), 'stat_visites.created_at'), $buckets);
            $series['visites_boutiques'] = $this->bucketize($this->dailyCounts($this->shopVisits(), 'stat_visites.created_at'), $buckets);
        }

        if ($this->hasRecherches) {
            $series['recherches'] = $this->bucketize($this->dailyCounts(DB::table('stat_recherches'), 'stat_recherches.created_at'), $buckets);
        }

        // Cumul des inscrits : part du total existant avant la période
        $running = DB::table('users')->where('created_at', '<', $this->from)->count();
        $series['inscrits_cumules'] = array_map(function ($n) use (&$running) {
            return $running += $n;
        }, $series['inscriptions']);

        return $series;
    }

    /**
     * @return array<string, int> date Y-m-d => nombre
     */
    private function dailyCounts(Builder $query, string $column): array
    {
        return $query
            ->whereBetween($column, $this->period())
            ->selectRaw("DATE({$column}) as jour, COUNT(*) as total")
            ->groupBy(DB::raw("DATE({$column})"))
            ->pluck('total', 'jour')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * Intervalles de la courbe selon la granularité : clé => [label, start, end].
     * start / end sont bornés à la période (une semaine à cheval reste dans la sélection).
     */
    private function buckets(): array
    {
        $buckets = [];
        $first = $this->from;
        $last = $this->to->startOfDay();
        $clip = fn (CarbonImmutable $start, CarbonImmutable $end) => [
            'start' => $start->max($first)->toDateString(),
            'end' => $end->min($last)->toDateString(),
        ];

        if ($this->granularity === 'jour') {
            foreach (CarbonPeriod::create($first, '1 day', $last) as $day) {
                $day = CarbonImmutable::instance($day);
                $buckets[$day->toDateString()] = ['label' => $day->translatedFormat('d M')] + $clip($day, $day);
            }
        } elseif ($this->granularity === 'semaine') {
            $cursor = $first->startOfWeek();
            while ($cursor->lessThanOrEqualTo($last)) {
                $buckets[$cursor->toDateString()] = ['label' => 'Sem. ' . $cursor->translatedFormat('d M')]
                    + $clip($cursor, $cursor->endOfWeek()->startOfDay());
                $cursor = $cursor->addWeek();
            }
        } else {
            $cursor = $first->startOfMonth();
            while ($cursor->lessThanOrEqualTo($last)) {
                $buckets[$cursor->toDateString()] = ['label' => ucfirst($cursor->translatedFormat('M Y'))]
                    + $clip($cursor, $cursor->endOfMonth()->startOfDay());
                $cursor = $cursor->addMonthNoOverflow();
            }
        }

        return $buckets;
    }

    private function bucketize(array $daily, array $buckets): array
    {
        $values = array_fill_keys(array_keys($buckets), 0);

        foreach ($daily as $date => $count) {
            $day = CarbonImmutable::parse($date);
            $key = match ($this->granularity) {
                'semaine' => $day->startOfWeek()->toDateString(),
                'mois' => $day->startOfMonth()->toDateString(),
                default => $day->toDateString(),
            };

            if (array_key_exists($key, $values)) {
                $values[$key] += $count;
            }
        }

        return array_values($values);
    }

    // ------------------------------------------------------------------
    // Répartitions
    // ------------------------------------------------------------------

    private function repartitions(): array
    {
        $articles = fn () => $this->articles()->whereBetween('articles.created_at', $this->period());

        $status = $articles()->selectRaw('articles.status as cle, COUNT(*) as total')->groupBy('articles.status')->pluck('total', 'cle');
        $etat = $articles()->selectRaw('articles.neuf as cle, COUNT(*) as total')->groupBy('articles.neuf')->pluck('total', 'cle');
        $livraison = $articles()->selectRaw('articles.livraison as cle, COUNT(*) as total')->groupBy('articles.livraison')->pluck('total', 'cle');

        $result = [
            'statut' => [
                'Approuvées' => (int) ($status['approved'] ?? 0),
                'En attente' => (int) ($status['pending'] ?? 0),
                'Bloquées' => (int) ($status['blocked'] ?? 0),
            ],
            'etat' => [
                'Neuf' => (int) $etat->filter(fn ($n, $k) => (int) $k === 1)->sum(),
                'Occasion' => (int) $etat->filter(fn ($n, $k) => (int) $k !== 1)->sum(),
            ],
            'livraison' => [
                'Avec livraison' => (int) $livraison->filter(fn ($n, $k) => (int) $k === 1)->sum(),
                'Sans livraison' => (int) $livraison->filter(fn ($n, $k) => (int) $k !== 1)->sum(),
            ],
            'tranches_prix' => $this->priceRanges(),
            'sources_recherche' => [],
        ];

        if ($this->hasRecherches) {
            $labels = ['accueil' => 'Barre d\'accueil', 'recherche' => 'Page de recherche', 'recherche_directe' => 'Recherche en direct'];
            $result['sources_recherche'] = DB::table('stat_recherches')
                ->whereBetween('created_at', $this->period())
                ->selectRaw('source, COUNT(*) as total')
                ->groupBy('source')
                ->pluck('total', 'source')
                ->mapWithKeys(fn ($n, $k) => [$labels[$k] ?? ($k ?: 'Autre') => (int) $n])
                ->all();
        }

        return $result;
    }

    private function priceRanges(): array
    {
        $ranges = [
            'Moins de 5 000' => [0, 5000],
            '5 000 – 25 000' => [5000, 25000],
            '25 000 – 100 000' => [25000, 100000],
            '100 000 – 500 000' => [100000, 500000],
            '500 000 et plus' => [500000, null],
        ];

        $result = [];
        foreach ($ranges as $label => [$min, $max]) {
            $query = $this->articles()->whereBetween('articles.created_at', $this->period())->where('articles.prix_ht', '>=', $min);
            if ($max !== null) {
                $query->where('articles.prix_ht', '<', $max);
            }
            $result[$label] = $query->count();
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // Heatmaps jour de semaine × heure
    // ------------------------------------------------------------------

    private function heatmaps(): array
    {
        $maps = [
            'annonces' => $this->heatmap($this->articles(), 'articles.created_at'),
            'inscriptions' => $this->heatmap(DB::table('users'), 'users.created_at'),
        ];

        if ($this->hasVisites) {
            $maps['vues'] = $this->hasArticleFilter()
                ? $this->heatmap($this->articleVisits(), 'stat_visites.created_at')
                : $this->heatmap(DB::table('stat_visites'), 'stat_visites.created_at');
        }

        return $maps;
    }

    /**
     * @return array<int, array<int, int>> [jour 0=lundi..6][heure 0..23]
     */
    private function heatmap(Builder $query, string $column): array
    {
        $grid = array_fill(0, 7, array_fill(0, 24, 0));

        $query->whereBetween($column, $this->period())
            ->orderByDesc($column)
            ->limit(self::SAMPLE_LIMIT)
            ->pluck($column)
            ->each(function ($value) use (&$grid) {
                // Lecture directe de « Y-m-d H:i:s » : bien plus rapide que Carbon sur des milliers de lignes
                $value = (string) $value;
                $timestamp = strtotime(substr($value, 0, 10));
                if ($timestamp === false) {
                    return;
                }
                $grid[(int) date('N', $timestamp) - 1][(int) substr($value, 11, 2)]++;
            });

        return $grid;
    }

    // ------------------------------------------------------------------
    // Classements
    // ------------------------------------------------------------------

    private function topPublishers(): array
    {
        $rows = $this->articles()
            ->whereBetween('articles.created_at', $this->period())
            ->selectRaw("articles.user_id as uid, COUNT(*) as total, SUM(CASE WHEN articles.status = 'approved' THEN 1 ELSE 0 END) as approuvees")
            ->groupBy('articles.user_id')
            ->orderByDesc('total')
            ->limit($this->top)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $ids = $rows->pluck('uid')->all();
        $users = User::whereIn('id', $ids)->get()->keyBy('id');
        $allTime = $this->articles()->whereIn('articles.user_id', $ids)
            ->selectRaw('articles.user_id as uid, COUNT(*) as total')->groupBy('articles.user_id')->pluck('total', 'uid');
        $views = $this->hasVisites
            ? $this->articleVisits()
                ->whereBetween('stat_visites.created_at', $this->period())
                ->whereIn('stat_visites.vendeur_id', $ids)
                ->selectRaw('stat_visites.vendeur_id as uid, COUNT(*) as total')
                ->groupBy('stat_visites.vendeur_id')
                ->pluck('total', 'uid')
            : collect();
        $likes = $this->filterArticles(
            DB::table('article_user_like')->join('articles', 'articles.id', '=', 'article_user_like.article_id')
        )
            ->whereIn('articles.user_id', $ids)
            ->selectRaw('articles.user_id as uid, COUNT(*) as total')
            ->groupBy('articles.user_id')
            ->pluck('total', 'uid');

        return $rows->map(function ($row) use ($users, $allTime, $views, $likes) {
            return $this->userColumns($users->get($row->uid), (int) $row->uid) + [
                'annonces' => (int) $row->total,
                'approuvees' => (int) $row->approuvees,
                'annonces_total' => (int) ($allTime[$row->uid] ?? 0),
                'vues' => (int) ($views[$row->uid] ?? 0),
                'likes' => (int) ($likes[$row->uid] ?? 0),
            ];
        })->values()->all();
    }

    private function topCategories(): array
    {
        $rows = $this->articles()
            ->join('sous_categories', 'sous_categories.id', '=', 'articles.sous_categorie_id')
            ->whereBetween('articles.created_at', $this->period())
            ->selectRaw('sous_categories.categorie_id as cid, COUNT(*) as total')
            ->groupBy('sous_categories.categorie_id')
            ->orderByDesc('total')
            ->limit($this->top)
            ->get();

        $periodTotal = $this->articles()->whereBetween('articles.created_at', $this->period())->count();
        $names = Categorie::whereIn('id', $rows->pluck('cid'))->pluck('nom', 'id');

        $views = [];
        if ($this->hasVisites && $rows->isNotEmpty()) {
            $views = $this->filterArticles(
                DB::table('stat_visites')
                    ->join('articles', 'articles.id', '=', 'stat_visites.article_id')
                    ->join('sous_categories', 'sous_categories.id', '=', 'articles.sous_categorie_id')
            )
                ->where('stat_visites.type', 'article')
                ->whereBetween('stat_visites.created_at', $this->period())
                ->whereIn('sous_categories.categorie_id', $rows->pluck('cid'))
                ->selectRaw('sous_categories.categorie_id as cid, COUNT(*) as total')
                ->groupBy('sous_categories.categorie_id')
                ->pluck('total', 'cid')
                ->all();
        }

        return $rows->map(fn ($row) => [
            'id' => (int) $row->cid,
            'nom' => $names[$row->cid] ?? 'Catégorie supprimée',
            'annonces' => (int) $row->total,
            'part' => $this->percent((int) $row->total, $periodTotal),
            'vues' => (int) ($views[$row->cid] ?? 0),
        ])->values()->all();
    }

    private function topSousCategories(): array
    {
        $rows = $this->articles()
            ->whereBetween('articles.created_at', $this->period())
            ->whereNotNull('articles.sous_categorie_id')
            ->selectRaw('articles.sous_categorie_id as sid, COUNT(*) as total')
            ->groupBy('articles.sous_categorie_id')
            ->orderByDesc('total')
            ->limit($this->top)
            ->get();

        $periodTotal = $this->articles()->whereBetween('articles.created_at', $this->period())->count();
        $sous = SousCategorie::with('categorie:id,nom')->whereIn('id', $rows->pluck('sid'))->get()->keyBy('id');

        $views = [];
        if ($this->hasVisites && $rows->isNotEmpty()) {
            $views = $this->filterArticles(
                DB::table('stat_visites')->join('articles', 'articles.id', '=', 'stat_visites.article_id')
            )
                ->where('stat_visites.type', 'article')
                ->whereBetween('stat_visites.created_at', $this->period())
                ->whereIn('articles.sous_categorie_id', $rows->pluck('sid'))
                ->selectRaw('articles.sous_categorie_id as sid, COUNT(*) as total')
                ->groupBy('articles.sous_categorie_id')
                ->pluck('total', 'sid')
                ->all();
        }

        return $rows->map(function ($row) use ($sous, $periodTotal, $views) {
            $item = $sous->get($row->sid);

            return [
                'id' => (int) $row->sid,
                'nom' => $item->nom ?? 'Sous-catégorie supprimée',
                'categorie' => $item?->categorie?->nom ?? '—',
                'annonces' => (int) $row->total,
                'part' => $this->percent((int) $row->total, $periodTotal),
                'vues' => (int) ($views[$row->sid] ?? 0),
            ];
        })->values()->all();
    }

    private function topVilles(): array
    {
        $rows = $this->articles()
            ->whereBetween('articles.created_at', $this->period())
            ->selectRaw('articles.lieu as lieu, COUNT(*) as total')
            ->groupBy('articles.lieu')
            ->get();

        // Fusionne « Lomé », « lome », « LOMÉ »… sous une même ville
        $villes = [];
        foreach ($rows as $row) {
            $label = trim((string) $row->lieu);
            $key = StatTracker::normalize($label) ?: 'non-renseigne';

            $villes[$key] ??= ['nom' => $label !== '' ? $label : 'Non renseignée', 'annonces' => 0, 'best' => 0];
            $villes[$key]['annonces'] += (int) $row->total;

            if ((int) $row->total > $villes[$key]['best'] && $label !== '') {
                $villes[$key]['nom'] = $label;
                $villes[$key]['best'] = (int) $row->total;
            }
        }

        $periodTotal = array_sum(array_column($villes, 'annonces'));
        usort($villes, fn ($a, $b) => $b['annonces'] <=> $a['annonces']);

        return array_map(fn ($v) => [
            'nom' => $v['nom'],
            'annonces' => $v['annonces'],
            'part' => $this->percent($v['annonces'], $periodTotal),
        ], array_slice($villes, 0, $this->top));
    }

    /**
     * Mots et expressions (2 mots) les plus présents dans les titres des annonces.
     * On compte le nombre d'annonces qui contiennent le mot, pas ses répétitions.
     */
    private function topKeywords(): array
    {
        $stopwords = array_flip(self::STOPWORDS);
        $words = [];
        $pairs = [];
        $variants = [];
        $titles = 0;

        $this->articles()
            ->whereBetween('articles.created_at', $this->period())
            ->orderByDesc('articles.id')
            ->limit(self::SAMPLE_LIMIT)
            ->pluck('articles.titre')
            ->each(function ($titre) use (&$words, &$pairs, &$variants, &$titles, $stopwords) {
                $titles++;
                $tokens = [];

                foreach (preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string) $titre), -1, PREG_SPLIT_NO_EMPTY) as $raw) {
                    $norm = StatTracker::normalize($raw);
                    if (strlen($norm) < 3 || ctype_digit($norm) || isset($stopwords[$norm])) {
                        continue;
                    }
                    $tokens[] = $norm;
                    $variants[$norm][$raw] = ($variants[$norm][$raw] ?? 0) + 1;
                }

                foreach (array_unique($tokens) as $token) {
                    $words[$token] = ($words[$token] ?? 0) + 1;
                }

                $seenPairs = [];
                for ($i = 0; $i < count($tokens) - 1; $i++) {
                    if ($tokens[$i] !== $tokens[$i + 1]) {
                        $seenPairs[$tokens[$i] . ' ' . $tokens[$i + 1]] = true;
                    }
                }
                foreach (array_keys($seenPairs) as $pair) {
                    $pairs[$pair] = ($pairs[$pair] ?? 0) + 1;
                }
            });

        $display = function (string $norm) use ($variants) {
            return implode(' ', array_map(function ($part) use ($variants) {
                if (empty($variants[$part])) {
                    return $part;
                }
                arsort($variants[$part]);

                return array_key_first($variants[$part]);
            }, explode(' ', $norm)));
        };

        arsort($words);
        arsort($pairs);
        $pairs = array_filter($pairs, fn ($n) => $n >= 2);

        $format = fn (array $list) => array_map(
            fn ($term, $n) => ['mot' => $display((string) $term), 'annonces' => $n, 'part' => $this->percent($n, $titles)],
            array_keys($list),
            array_values($list),
        );

        return [
            'mots' => $format(array_slice($words, 0, $this->top, true)),
            'expressions' => $format(array_slice($pairs, 0, $this->top, true)),
        ];
    }

    private function topViewedArticles(): array
    {
        if (! $this->hasVisites) {
            return [];
        }

        $rows = $this->articleVisits()
            ->whereBetween('stat_visites.created_at', $this->period())
            ->whereNotNull('stat_visites.article_id')
            ->selectRaw('stat_visites.article_id as aid, COUNT(*) as vues, COUNT(DISTINCT stat_visites.visiteur_hash) as visiteurs')
            ->groupBy('stat_visites.article_id')
            ->orderByDesc('vues')
            ->limit($this->top)
            ->get();

        $articles = $this->loadArticles($rows->pluck('aid')->all());
        $likes = DB::table('article_user_like')->whereIn('article_id', $rows->pluck('aid'))
            ->selectRaw('article_id, COUNT(*) as total')->groupBy('article_id')->pluck('total', 'article_id');

        return $rows->map(fn ($row) => $this->articleColumns($articles->get($row->aid), (int) $row->aid) + [
            'vues' => (int) $row->vues,
            'visiteurs' => (int) $row->visiteurs,
            'likes' => (int) ($likes[$row->aid] ?? 0),
        ])->values()->all();
    }

    /**
     * Vendeurs les plus visités (visites de la boutique), avec l'audience de leurs
     * annonces et leur annonce la plus vue sur la période.
     */
    private function topShops(): array
    {
        if (! $this->hasVisites) {
            return [];
        }

        $query = DB::table('stat_visites')
            ->whereBetween('stat_visites.created_at', $this->period())
            ->whereNotNull('stat_visites.vendeur_id');

        if ($this->hasArticleFilter()) {
            // Boutiques des vendeurs concernés + seulement les vues de leurs annonces filtrées
            $query->leftJoin('articles', 'articles.id', '=', 'stat_visites.article_id')
                ->whereIn('stat_visites.vendeur_id', $this->articles()->select('articles.user_id'))
                ->where(function (Builder $where) {
                    $where->where('stat_visites.type', 'boutique')
                        ->orWhere(function (Builder $article) {
                            $article->where('stat_visites.type', 'article');
                            $this->filterArticles($article);
                        });
                });
        }

        $rows = $query
            ->selectRaw("stat_visites.vendeur_id as vid,
                SUM(CASE WHEN stat_visites.type = 'boutique' THEN 1 ELSE 0 END) as visites_boutique,
                SUM(CASE WHEN stat_visites.type = 'article' THEN 1 ELSE 0 END) as vues_annonces,
                COUNT(DISTINCT stat_visites.visiteur_hash) as visiteurs")
            ->groupBy('stat_visites.vendeur_id')
            ->orderByDesc('visites_boutique')
            ->orderByDesc('vues_annonces')
            ->limit($this->top)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $ids = $rows->pluck('vid')->all();
        $users = User::whereIn('id', $ids)->get()->keyBy('id');

        // Annonce la plus vue de chaque vendeur
        $best = [];
        $this->articleVisits()
            ->whereBetween('stat_visites.created_at', $this->period())
            ->whereIn('stat_visites.vendeur_id', $ids)
            ->whereNotNull('stat_visites.article_id')
            ->selectRaw('stat_visites.vendeur_id as vid, stat_visites.article_id as aid, COUNT(*) as vues')
            ->groupBy('stat_visites.vendeur_id', 'stat_visites.article_id')
            ->get()
            ->each(function ($row) use (&$best) {
                if (! isset($best[$row->vid]) || $row->vues > $best[$row->vid]['vues']) {
                    $best[$row->vid] = ['article_id' => (int) $row->aid, 'vues' => (int) $row->vues];
                }
            });

        $articles = $this->loadArticles(array_column($best, 'article_id'));
        $activeArticles = $this->articles()->whereIn('articles.user_id', $ids)->where('articles.status', 'approved')
            ->selectRaw('articles.user_id as uid, COUNT(*) as total')->groupBy('articles.user_id')->pluck('total', 'uid');

        return $rows->map(function ($row) use ($users, $best, $articles, $activeArticles) {
            $top = $best[$row->vid] ?? null;

            return $this->userColumns($users->get($row->vid), (int) $row->vid) + [
                'visites_boutique' => (int) $row->visites_boutique,
                'vues_annonces' => (int) $row->vues_annonces,
                'visiteurs' => (int) $row->visiteurs,
                'annonces_en_ligne' => (int) ($activeArticles[$row->vid] ?? 0),
                'meilleure_annonce' => $top
                    ? $this->articleColumns($articles->get($top['article_id']), $top['article_id']) + ['vues' => $top['vues']]
                    : null,
            ];
        })->values()->all();
    }

    private function topLikedArticles(): array
    {
        $rows = $this->likes()
            ->tap(fn (Builder $q) => $this->likedDuring($q, $this->period(), true))
            ->selectRaw('article_user_like.article_id as aid, COUNT(*) as likes')
            ->groupBy('article_user_like.article_id')
            ->orderByDesc('likes')
            ->limit($this->top)
            ->get();

        $ids = $rows->pluck('aid')->all();
        $articles = $this->loadArticles($ids);
        $views = ($this->hasVisites && $ids)
            ? DB::table('stat_visites')
                ->where('type', 'article')
                ->whereBetween('created_at', $this->period())
                ->whereIn('article_id', $ids)
                ->selectRaw('article_id, COUNT(*) as total')
                ->groupBy('article_id')
                ->pluck('total', 'article_id')
            : collect();

        return $rows->map(fn ($row) => $this->articleColumns($articles->get($row->aid), (int) $row->aid) + [
            'likes' => (int) $row->likes,
            'vues' => (int) ($views[$row->aid] ?? 0),
        ])->values()->all();
    }

    // ------------------------------------------------------------------
    // Recherches (globales : non rattachées à une annonce)
    // ------------------------------------------------------------------

    private function searches(): array
    {
        $empty = ['total' => 0, 'chercheurs' => 0, 'sans_resultat' => 0, 'sans_resultat_pct' => 0, 'top' => [], 'sans_resultat_top' => []];

        if (! $this->hasRecherches) {
            return $empty;
        }

        $base = fn () => DB::table('stat_recherches')->whereBetween('created_at', $this->period());

        $total = $base()->count();
        if ($total === 0) {
            return $empty;
        }

        $zero = $base()->where('resultats', 0)->count();

        $top = $base()
            ->selectRaw('terme_normalise, MAX(terme) as terme, COUNT(*) as total, COUNT(DISTINCT visiteur_hash) as chercheurs, AVG(resultats) as resultats_moyens, SUM(CASE WHEN resultats = 0 THEN 1 ELSE 0 END) as sans_resultat')
            ->groupBy('terme_normalise')
            ->orderByDesc('total')
            ->limit($this->top)
            ->get()
            ->map(fn ($row) => [
                'terme' => $row->terme,
                'recherches' => (int) $row->total,
                'chercheurs' => (int) $row->chercheurs,
                'resultats_moyens' => (int) round((float) $row->resultats_moyens),
                'sans_resultat' => (int) $row->sans_resultat,
            ])->all();

        $zeroTop = $base()
            ->where('resultats', 0)
            ->selectRaw('terme_normalise, MAX(terme) as terme, COUNT(*) as total, COUNT(DISTINCT visiteur_hash) as chercheurs, MAX(created_at) as derniere')
            ->groupBy('terme_normalise')
            ->orderByDesc('total')
            ->limit($this->top)
            ->get()
            ->map(fn ($row) => [
                'terme' => $row->terme,
                'recherches' => (int) $row->total,
                'chercheurs' => (int) $row->chercheurs,
                'derniere' => CarbonImmutable::parse($row->derniere)->format('d/m/Y H:i'),
            ])->all();

        return [
            'total' => $total,
            'chercheurs' => $base()->distinct()->count('visiteur_hash'),
            'sans_resultat' => $zero,
            'sans_resultat_pct' => $this->percent($zero, $total),
            'top' => $top,
            'sans_resultat_top' => $zeroTop,
        ];
    }

    // ------------------------------------------------------------------
    // Outils
    // ------------------------------------------------------------------

    private function loadArticles(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return Article::with(['user:id,name', 'sousCategorie.categorie'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    private function articleColumns(?Article $article, int $id): array
    {
        if (! $article) {
            return [
                'id' => $id, 'titre' => 'Annonce supprimée (#' . $id . ')', 'photo' => null, 'prix' => null,
                'lieu' => null, 'categorie' => null, 'vendeur' => null, 'url' => null, 'admin_url' => null, 'statut' => null,
            ];
        }

        return [
            'id' => $article->id,
            'titre' => $article->titre,
            'photo' => $this->relativeUrl($article->photo_url),
            'prix' => (float) $article->prix_ht,
            'lieu' => $article->lieu,
            'categorie' => $article->sousCategorie?->nom,
            'vendeur' => $article->user?->name,
            'url' => $this->relativeUrl($article->url()),
            'admin_url' => $this->relativeUrl(route('admin.articles.show', $article->id)),
            'statut' => $article->status,
        ];
    }

    private function userColumns(?User $user, int $id): array
    {
        if (! $user) {
            return [
                'id' => $id, 'nom' => 'Compte supprimé (#' . $id . ')', 'photo' => $this->relativeUrl(User::defaultProfilPhotoUrl()),
                'email' => null, 'certifie' => false, 'inscrit_le' => null, 'boutique_url' => null, 'admin_url' => null,
            ];
        }

        return [
            'id' => $user->id,
            'nom' => $user->name,
            'photo' => $this->relativeUrl($user->getProfilPhotoUrl()),
            'email' => $user->email,
            'certifie' => $user->estCertifie(),
            'inscrit_le' => $user->created_at?->format('d/m/Y'),
            'boutique_url' => $this->relativeUrl($user->shopUrl()),
            'admin_url' => $this->relativeUrl(route('admin.users.show', $user->id)),
        ];
    }

    /**
     * Les résultats sont mis en cache : on n'y garde que des chemins (« /boutique/… »),
     * sans l'hôte de la requête qui les a calculés (http/https, www, port…).
     */
    private function relativeUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['host'])) {
            return $url;
        }

        return ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    private function metric(int $value, int $previous): array
    {
        if ($previous === 0) {
            $evolution = $value > 0 ? null : 0.0; // null = « nouveau »
        } else {
            $evolution = round((($value - $previous) / $previous) * 100, 1);
        }

        return ['valeur' => $value, 'precedent' => $previous, 'evolution' => $evolution];
    }

    private function percent(int|float $part, int|float $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0.0;
    }

    private function trackingSince(string $table, bool $exists): ?string
    {
        if (! $exists) {
            return null;
        }

        $first = DB::table($table)->min('created_at');

        return $first ? CarbonImmutable::parse($first)->format('d/m/Y') : null;
    }

    private function safeGranularity(string $granularity, int $days): string
    {
        // Évite des courbes illisibles (et trop lourdes) sur de très longues périodes
        if ($granularity === 'jour' && $days > 400) {
            return 'semaine';
        }
        if ($granularity === 'semaine' && $days > 1825) {
            return 'mois';
        }

        return $granularity;
    }

    private static function firstActivityDate(): ?CarbonImmutable
    {
        $dates = array_filter([
            DB::table('users')->min('created_at'),
            DB::table('articles')->min('created_at'),
        ]);

        return $dates ? CarbonImmutable::parse(min($dates))->startOfDay() : null;
    }

    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

            // Refuse les dates invalides que PHP « corrige » (ex. 2026-02-31)
            return $date && $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
