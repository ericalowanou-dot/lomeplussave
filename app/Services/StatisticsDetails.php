<?php

namespace App\Services;

use App\Models\SousCategorie;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Pages de détail des statistiques : la liste complète derrière chaque chiffre
 * ou classement de la page Statistiques, avec tri, recherche, pagination et export.
 *
 * Toutes les listes respectent la période et les filtres Ville / Catégorie de
 * AdminStatistics. Les requêtes sont paginées : seule la page affichée est chargée.
 */
class StatisticsDetails
{
    /** type => [titre, onglet de retour sur la page Statistiques] */
    public const TYPES = [
        'annonces' => ['Annonces publiées', 'annonces'],
        'annonces-vues' => ['Annonces les plus vues', 'annonces/plus-vues'],
        'annonces-aimees' => ['Annonces les plus aimées', 'annonces/plus-aimees'],
        'likes' => ['Tous les likes', 'annonces/plus-aimees'],
        'inscrits' => ['Inscriptions', 'utilisateurs/inscriptions'],
        'vendeurs' => ['Vendeurs qui publient le plus', 'utilisateurs/vendeurs'],
        'boutiques' => ['Boutiques les plus visitées', 'utilisateurs/boutiques'],
        'categories' => ['Catégories', 'categories/categories'],
        'sous-categories' => ['Sous-catégories', 'categories/sous-categories'],
        'villes' => ['Villes', 'villes'],
        'mots-cles' => ['Mots-clés des titres', 'annonces/mots-cles'],
        'recherches' => ['Recherches des visiteurs', 'recherches'],
    ];

    public const PER_PAGE = [25, 50, 100];

    /** Nombre maximum de lignes dans un export CSV. */
    public const EXPORT_LIMIT = 10000;

    private const STATUTS = ['approved' => 'Approuvée', 'pending' => 'En attente', 'blocked' => 'Bloquée'];

    private string $sort;
    private string $direction;
    private int $perPage;
    private string $search;

    public function __construct(private AdminStatistics $stats, private Request $request)
    {
        $this->stats->detectTrackingTables();
        $this->search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $this->direction = $request->query('sens') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) $request->query('par_page', 25);
        $this->perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 25;
    }

    /**
     * Construit la liste. $all = true : sans pagination (export CSV).
     */
    public function build(string $type, bool $all = false): array
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $definition = match ($type) {
            'annonces' => $this->annonces(),
            'annonces-vues' => $this->annoncesVues(),
            'annonces-aimees' => $this->annoncesAimees(),
            'likes' => $this->likes(),
            'inscrits' => $this->inscrits(),
            'vendeurs' => $this->vendeurs(),
            'boutiques' => $this->boutiques(),
            'categories' => $this->categories(),
            'sous-categories' => $this->sousCategories(),
            'villes' => $this->villes(),
            'mots-cles' => $this->motsCles(),
            'recherches' => $this->recherches(),
        };

        $sortable = array_keys(array_filter($definition['colonnes'], fn ($c) => isset($c['tri'])));
        $this->sort = in_array($this->request->query('tri'), $sortable, true) ? $this->request->query('tri') : $definition['tri'];
        if (! $this->request->has('sens')) {
            $this->direction = $definition['colonnes'][$this->sort]['sens'] ?? 'desc';
        }

        if (isset($definition['query'])) {
            [$items, $paginator, $total] = $this->runQuery($definition, $all);
        } else {
            [$items, $paginator, $total] = $this->runArray($definition, $all);
        }

        $rows = isset($definition['hydrate']) ? ($definition['hydrate'])($items) : $items;

        return [
            'type' => $type,
            'titre' => $definition['titre'] ?? self::TYPES[$type][0],
            'description' => $definition['description'] ?? null,
            'retour' => self::TYPES[$type][1],
            'colonnes' => $definition['colonnes'],
            'lignes' => $rows,
            'paginator' => $paginator,
            'total' => $total,
            'tri' => $this->sort,
            'sens' => $this->direction,
            'recherche' => $this->search,
            'recherche_placeholder' => $definition['recherche'] ?? null,
            'filtres' => $definition['filtres'] ?? [],
            'onglets' => $definition['onglets'] ?? [],
            'avertissement' => $definition['avertissement'] ?? null,
        ];
    }

    // ------------------------------------------------------------------
    // Exécution, tri, pagination
    // ------------------------------------------------------------------

    private function runQuery(array $definition, bool $all): array
    {
        /** @var Builder $query */
        $query = $definition['query'];
        $query->orderBy($definition['colonnes'][$this->sort]['tri'], $this->direction);
        if (isset($definition['cle'])) {
            $query->orderBy($definition['cle'], 'desc'); // ordre stable entre les pages
        }

        if ($all) {
            $items = $query->limit(self::EXPORT_LIMIT)->get();

            return [$items, null, $items->count()];
        }

        $paginator = $query->paginate($this->perPage)->withQueryString();

        return [collect($paginator->items()), $paginator, $paginator->total()];
    }

    private function runArray(array $definition, bool $all): array
    {
        $items = collect($definition['items']);

        if ($this->search !== '' && isset($definition['champ_recherche'])) {
            $needle = StatTracker::normalize($this->search);
            $items = $items->filter(fn ($row) => str_contains(StatTracker::normalize((string) $row[$definition['champ_recherche']]), $needle));
        }

        $key = $definition['colonnes'][$this->sort]['tri'];
        $items = $items->sort(function ($a, $b) use ($key) {
            $cmp = is_numeric($a[$key]) && is_numeric($b[$key])
                ? $a[$key] <=> $b[$key]
                : strcasecmp(StatTracker::normalize((string) $a[$key]), StatTracker::normalize((string) $b[$key]));

            return $this->direction === 'asc' ? $cmp : -$cmp;
        })->values();

        if ($all) {
            return [$items->take(self::EXPORT_LIMIT), null, $items->count()];
        }

        $page = max(1, (int) $this->request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $this->perPage)->values(),
            $items->count(),
            $this->perPage,
            $page,
            ['path' => $this->request->url(), 'query' => $this->request->query()],
        );

        return [collect($paginator->items()), $paginator, $items->count()];
    }

    // ------------------------------------------------------------------
    // Sous-requêtes réutilisées
    // ------------------------------------------------------------------

    /** Vues d'une annonce (colonne articles.id) pendant la période. */
    private function viewsOfArticle(bool $distinctVisitors = false): Builder|string
    {
        if (! $this->stats->hasVisites()) {
            return '0';
        }

        return DB::table('stat_visites')
            ->selectRaw($distinctVisitors ? 'COUNT(DISTINCT stat_visites.visiteur_hash)' : 'COUNT(*)')
            ->whereColumn('stat_visites.article_id', 'articles.id')
            ->where('stat_visites.type', 'article')
            ->whereBetween('stat_visites.created_at', $this->stats->period());
    }

    /** Likes d'une annonce (colonne articles.id) : sur la période, ou depuis toujours. */
    private function likesOfArticle(bool $inPeriod): Builder
    {
        $query = DB::table('article_user_like')
            ->selectRaw('COUNT(*)')
            ->whereColumn('article_user_like.article_id', 'articles.id');

        return $inPeriod ? $this->stats->likedDuring($query, $this->stats->period(), true) : $query;
    }

    private function addSub(Builder $query, Builder|string $sub, string $alias): void
    {
        is_string($sub) ? $query->selectRaw("{$sub} as {$alias}") : $query->selectSub($sub, $alias);
    }

    private function searchArticles(Builder $query): void
    {
        if ($this->search !== '') {
            $query->where('articles.titre', 'like', '%' . $this->search . '%');
        }
    }

    // ------------------------------------------------------------------
    // Annonces
    // ------------------------------------------------------------------

    private function annonces(): array
    {
        $query = $this->stats->articles()->select('articles.id');
        $filtres = [];
        $description = 'Annonces publiées pendant la période.';

        // Annonces correspondant à une recherche de visiteur : toutes dates, en ligne
        $recherche = trim((string) $this->request->query('recherche', ''));
        if ($recherche !== '') {
            $query->where('articles.status', 'approved')
                ->where(fn ($q) => $q->where('articles.titre', 'like', "%{$recherche}%")->orWhere('articles.description', 'like', "%{$recherche}%"));
            $filtres['recherche'] = 'Correspond à la recherche « ' . $recherche . ' »';
            $description = 'Annonces en ligne qui correspondent aujourd\'hui à cette recherche (toutes dates de publication).';
        } else {
            $query->whereBetween('articles.created_at', $this->stats->period());
        }

        $statut = $this->request->query('statut');
        if (array_key_exists($statut, self::STATUTS)) {
            $query->where('articles.status', $statut);
            $filtres['statut'] = 'Statut : ' . self::STATUTS[$statut];
        }

        $etat = $this->request->query('etat');
        if (in_array($etat, ['neuf', 'occasion'], true)) {
            $query->where('articles.neuf', $etat === 'neuf' ? 1 : 0);
            $filtres['etat'] = $etat === 'neuf' ? 'Neuf' : 'Occasion';
        }

        $livraison = $this->request->query('livraison');
        if (in_array($livraison, ['0', '1'], true)) {
            $query->where('articles.livraison', (int) $livraison);
            $filtres['livraison'] = $livraison === '1' ? 'Avec livraison' : 'Sans livraison';
        }

        $prix = $this->request->query('prix');
        if (array_key_exists($prix, AdminStatistics::PRICE_RANGES)) {
            [$label, $min, $max] = AdminStatistics::PRICE_RANGES[$prix];
            $query->where('articles.prix_ht', '>=', $min);
            if ($max !== null) {
                $query->where('articles.prix_ht', '<', $max);
            }
            $filtres['prix'] = 'Prix : ' . $label . ' F';
        }

        $mot = trim((string) $this->request->query('mot', ''));
        if ($mot !== '') {
            $query->where('articles.titre', 'like', "%{$mot}%");
            $filtres['mot'] = 'Titre contenant « ' . $mot . ' »';
        }

        $this->searchArticles($query);
        $query->addSelect('articles.created_at', 'articles.prix_ht', 'articles.titre');
        $this->addSub($query, $this->viewsOfArticle(), 'vues');
        $this->addSub($query, $this->likesOfArticle(false), 'likes');

        return [
            'description' => $description,
            'query' => $query,
            'cle' => 'articles.id',
            'tri' => 'publie_le',
            'filtres' => $filtres,
            'recherche' => 'Rechercher un titre…',
            'colonnes' => [
                'annonce' => ['label' => 'Annonce', 'format' => 'annonce', 'tri' => 'articles.titre', 'sens' => 'asc'],
                'vendeur' => ['label' => 'Vendeur', 'format' => 'texte'],
                'ville' => ['label' => 'Ville', 'format' => 'texte'],
                'prix' => ['label' => 'Prix', 'format' => 'money', 'tri' => 'articles.prix_ht'],
                'statut' => ['label' => 'Statut', 'format' => 'statut'],
                'publie_le' => ['label' => 'Publiée le', 'format' => 'date', 'tri' => 'articles.created_at'],
                'vues' => ['label' => 'Vues (période)', 'format' => 'num', 'tri' => 'vues'],
                'likes' => ['label' => 'Likes (total)', 'format' => 'num', 'tri' => 'likes'],
            ],
            'hydrate' => fn ($items) => $this->articleRows($items, fn ($row, $a) => [
                'vues' => (int) $row->vues,
                'likes' => (int) $row->likes,
            ]),
        ];
    }

    private function annoncesVues(): array
    {
        $query = $this->stats->articles()->select('articles.id');
        $this->searchArticles($query);

        if ($this->stats->hasVisites()) {
            $query->whereExists(fn ($q) => $q->select(DB::raw(1))->from('stat_visites')
                ->whereColumn('stat_visites.article_id', 'articles.id')
                ->where('stat_visites.type', 'article')
                ->whereBetween('stat_visites.created_at', $this->stats->period()));
        } else {
            $query->whereRaw('1 = 0');
        }

        $this->addSub($query, $this->viewsOfArticle(), 'vues');
        $this->addSub($query, $this->viewsOfArticle(true), 'visiteurs');
        $this->addSub($query, $this->likesOfArticle(false), 'likes');
        $query->addSelect('articles.prix_ht', 'articles.created_at', 'articles.titre');

        return [
            'description' => 'Annonces consultées pendant la période (un visiteur compte une fois par heure).',
            'query' => $query,
            'cle' => 'articles.id',
            'tri' => 'vues',
            'recherche' => 'Rechercher un titre…',
            'avertissement' => $this->stats->hasVisites() ? null : 'Le suivi des vues n\'est pas encore activé (php artisan migrate).',
            'colonnes' => [
                'annonce' => ['label' => 'Annonce', 'format' => 'annonce', 'tri' => 'articles.titre', 'sens' => 'asc'],
                'vendeur' => ['label' => 'Vendeur', 'format' => 'texte'],
                'ville' => ['label' => 'Ville', 'format' => 'texte'],
                'prix' => ['label' => 'Prix', 'format' => 'money', 'tri' => 'articles.prix_ht'],
                'vues' => ['label' => 'Vues', 'format' => 'num', 'tri' => 'vues'],
                'visiteurs' => ['label' => 'Visiteurs uniques', 'format' => 'num', 'tri' => 'visiteurs'],
                'likes' => ['label' => 'Likes (total)', 'format' => 'num', 'tri' => 'likes'],
                'taux' => ['label' => 'Likes / vues', 'format' => 'pct'],
                'publie_le' => ['label' => 'Publiée le', 'format' => 'date', 'tri' => 'articles.created_at'],
            ],
            'hydrate' => fn ($items) => $this->articleRows($items, fn ($row) => [
                'vues' => (int) $row->vues,
                'visiteurs' => (int) $row->visiteurs,
                'likes' => (int) $row->likes,
                'taux' => $this->stats->percent((int) $row->likes, (int) $row->vues),
            ]),
        ];
    }

    private function annoncesAimees(): array
    {
        $query = $this->stats->articles()->select('articles.id');
        $this->searchArticles($query);
        $query->whereExists(fn ($q) => $this->stats->likedDuring(
            $q->select(DB::raw(1))->from('article_user_like')->whereColumn('article_user_like.article_id', 'articles.id'),
            $this->stats->period(),
            true,
        ));

        $this->addSub($query, $this->likesOfArticle(true), 'likes_periode');
        $this->addSub($query, $this->likesOfArticle(false), 'likes_total');
        $this->addSub($query, $this->viewsOfArticle(), 'vues');
        $query->addSelect('articles.prix_ht', 'articles.created_at', 'articles.titre');

        return [
            'description' => 'Annonces ajoutées en favoris pendant la période. Les anciens likes sans date ne comptent que dans « Depuis le début ».',
            'query' => $query,
            'cle' => 'articles.id',
            'tri' => 'likes_periode',
            'recherche' => 'Rechercher un titre…',
            'colonnes' => [
                'annonce' => ['label' => 'Annonce', 'format' => 'annonce', 'tri' => 'articles.titre', 'sens' => 'asc'],
                'vendeur' => ['label' => 'Vendeur', 'format' => 'texte'],
                'prix' => ['label' => 'Prix', 'format' => 'money', 'tri' => 'articles.prix_ht'],
                'likes_periode' => ['label' => 'Likes (période)', 'format' => 'num', 'tri' => 'likes_periode'],
                'likes_total' => ['label' => 'Likes (total)', 'format' => 'num', 'tri' => 'likes_total'],
                'vues' => ['label' => 'Vues (période)', 'format' => 'num', 'tri' => 'vues'],
                'publie_le' => ['label' => 'Publiée le', 'format' => 'date', 'tri' => 'articles.created_at'],
            ],
            'hydrate' => fn ($items) => $this->articleRows($items, fn ($row) => [
                'likes_periode' => (int) $row->likes_periode,
                'likes_total' => (int) $row->likes_total,
                'vues' => (int) $row->vues,
            ]),
        ];
    }

    private function likes(): array
    {
        $query = DB::table('article_user_like')
            ->join('articles', 'articles.id', '=', 'article_user_like.article_id')
            ->join('users as fans', 'fans.id', '=', 'article_user_like.user_id')
            ->select('article_user_like.article_id as id', 'article_user_like.created_at as aime_le', 'fans.id as fan_id');
        $this->stats->filterArticles($query);
        $this->stats->likedDuring($query, $this->stats->period(), true);

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('articles.titre', 'like', "%{$this->search}%")->orWhere('fans.name', 'like', "%{$this->search}%"));
        }

        return [
            'description' => 'Qui a aimé quelle annonce, et quand.',
            'query' => $query,
            'tri' => 'aime_le',
            'recherche' => 'Rechercher une annonce ou une personne…',
            'colonnes' => [
                'aime_le' => ['label' => 'Aimée le', 'format' => 'datetime', 'tri' => 'article_user_like.created_at'],
                'annonce' => ['label' => 'Annonce', 'format' => 'annonce', 'tri' => 'articles.titre', 'sens' => 'asc'],
                'fan' => ['label' => 'Aimée par', 'format' => 'personne', 'tri' => 'fans.name', 'sens' => 'asc'],
            ],
            'hydrate' => function ($items) {
                $fans = User::whereIn('id', $items->pluck('fan_id'))->get()->keyBy('id');

                return $this->articleRows($items, fn ($row) => [
                    'aime_le' => $row->aime_le,
                    'fan' => $this->personne($fans->get($row->fan_id), (int) $row->fan_id),
                ]);
            },
        ];
    }

    // ------------------------------------------------------------------
    // Utilisateurs
    // ------------------------------------------------------------------

    private function inscrits(): array
    {
        $query = DB::table('users')->select('users.id')->whereBetween('users.created_at', $this->stats->period());
        $filtres = [];

        if ($this->request->query('vendeur') === '1') {
            $query->whereExists(fn ($q) => $q->select(DB::raw(1))->from('articles')->whereColumn('articles.user_id', 'users.id'));
            $filtres['vendeur'] = 'Seulement ceux qui ont publié';
        }

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('users.name', 'like', "%{$this->search}%")->orWhere('users.email', 'like', "%{$this->search}%"));
        }

        $query->addSelect('users.created_at', 'users.name')
            ->selectSub(DB::table('articles')->selectRaw('COUNT(*)')->whereColumn('articles.user_id', 'users.id'), 'annonces')
            ->selectSub(DB::table('article_user_like')->selectRaw('COUNT(*)')->whereColumn('article_user_like.user_id', 'users.id'), 'likes_donnes');

        return [
            'description' => 'Comptes créés pendant la période.',
            'query' => $query,
            'cle' => 'users.id',
            'tri' => 'inscrit_le',
            'filtres' => $filtres,
            'recherche' => 'Rechercher un nom ou un email…',
            'colonnes' => [
                'personne' => ['label' => 'Utilisateur', 'format' => 'personne', 'tri' => 'users.name', 'sens' => 'asc'],
                'inscrit_le' => ['label' => 'Inscrit le', 'format' => 'datetime', 'tri' => 'users.created_at'],
                'telephone' => ['label' => 'Téléphone', 'format' => 'texte'],
                'verifie' => ['label' => 'Email vérifié', 'format' => 'bool'],
                'certifie' => ['label' => 'Certifié', 'format' => 'bool'],
                'bloque' => ['label' => 'Bloqué', 'format' => 'bool'],
                'annonces' => ['label' => 'Annonces publiées', 'format' => 'num', 'tri' => 'annonces'],
                'likes_donnes' => ['label' => 'Likes donnés', 'format' => 'num', 'tri' => 'likes_donnes'],
            ],
            'hydrate' => fn ($items) => $this->userRows($items, fn ($row, User $u) => [
                'inscrit_le' => $u->created_at,
                'telephone' => $u->telephone ?: $u->whatsapp,
                'verifie' => $u->email_verified_at !== null,
                'certifie' => $u->estCertifie(),
                'bloque' => (bool) $u->is_blocked,
                'annonces' => (int) $row->annonces,
                'likes_donnes' => (int) $row->likes_donnes,
            ]),
        ];
    }

    private function vendeurs(): array
    {
        $period = $this->stats->period();
        $ofUser = fn (Builder $q, string $column = 'articles.user_id') => $q->whereColumn($column, 'users.id');

        $query = DB::table('users')->select('users.id', 'users.name')
            ->whereExists(fn ($q) => $ofUser($this->stats->filterArticles($q->select(DB::raw(1))->from('articles')))
                ->whereBetween('articles.created_at', $period));

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('users.name', 'like', "%{$this->search}%")->orWhere('users.email', 'like', "%{$this->search}%"));
        }

        $query->selectSub($ofUser($this->stats->articles()->selectRaw('COUNT(*)'))->whereBetween('articles.created_at', $period), 'annonces')
            ->selectSub($ofUser($this->stats->articles()->selectRaw('COUNT(*)'))->whereBetween('articles.created_at', $period)->where('articles.status', 'approved'), 'approuvees')
            ->selectSub($ofUser($this->stats->articles()->selectRaw('COUNT(*)')), 'annonces_total')
            ->selectSub($ofUser($this->stats->filterArticles(
                DB::table('article_user_like')->join('articles', 'articles.id', '=', 'article_user_like.article_id')->selectRaw('COUNT(*)')
            )), 'likes');

        if ($this->stats->hasVisites()) {
            $query->selectSub($ofUser($this->stats->articleVisits()->selectRaw('COUNT(*)'), 'stat_visites.vendeur_id')->whereBetween('stat_visites.created_at', $period), 'vues')
                ->selectSub($ofUser($this->stats->shopVisits()->selectRaw('COUNT(*)'), 'stat_visites.vendeur_id')->whereBetween('stat_visites.created_at', $period), 'visites_boutique');
        } else {
            $query->selectRaw('0 as vues, 0 as visites_boutique');
        }

        return [
            'description' => 'Vendeurs ayant publié pendant la période.',
            'query' => $query,
            'cle' => 'users.id',
            'tri' => 'annonces',
            'recherche' => 'Rechercher un vendeur…',
            'colonnes' => [
                'personne' => ['label' => 'Vendeur', 'format' => 'personne', 'tri' => 'users.name', 'sens' => 'asc'],
                'annonces' => ['label' => 'Annonces (période)', 'format' => 'num', 'tri' => 'annonces'],
                'approuvees' => ['label' => 'Approuvées', 'format' => 'num', 'tri' => 'approuvees'],
                'annonces_total' => ['label' => 'Annonces (total)', 'format' => 'num', 'tri' => 'annonces_total'],
                'vues' => ['label' => 'Vues reçues', 'format' => 'num', 'tri' => 'vues'],
                'visites_boutique' => ['label' => 'Visites boutique', 'format' => 'num', 'tri' => 'visites_boutique'],
                'likes' => ['label' => 'Likes reçus (total)', 'format' => 'num', 'tri' => 'likes'],
                'inscrit_le' => ['label' => 'Inscrit le', 'format' => 'date'],
            ],
            'hydrate' => fn ($items) => $this->userRows($items, fn ($row, User $u) => [
                'annonces' => (int) $row->annonces,
                'approuvees' => (int) $row->approuvees,
                'annonces_total' => (int) $row->annonces_total,
                'vues' => (int) $row->vues,
                'visites_boutique' => (int) $row->visites_boutique,
                'likes' => (int) $row->likes,
                'inscrit_le' => $u->created_at,
            ]),
        ];
    }

    private function boutiques(): array
    {
        $period = $this->stats->period();
        $query = DB::table('users')->select('users.id', 'users.name');

        if (! $this->stats->hasVisites()) {
            $query->whereRaw('1 = 0')->selectRaw('0 as visites_boutique, 0 as vues_annonces, 0 as visiteurs, 0 as annonces_en_ligne');
        } else {
            $ofVendeur = fn (Builder $q) => $q->whereColumn('stat_visites.vendeur_id', 'users.id')->whereBetween('stat_visites.created_at', $period);

            $query->where(fn ($w) => $w
                ->whereExists($ofVendeur($this->stats->shopVisits()->select(DB::raw(1))))
                ->orWhereExists($ofVendeur($this->stats->articleVisits()->select(DB::raw(1)))))
                ->selectSub($ofVendeur($this->stats->shopVisits()->selectRaw('COUNT(*)')), 'visites_boutique')
                ->selectSub($ofVendeur($this->stats->articleVisits()->selectRaw('COUNT(*)')), 'vues_annonces')
                ->selectSub($ofVendeur(DB::table('stat_visites')->selectRaw('COUNT(DISTINCT stat_visites.visiteur_hash)')), 'visiteurs')
                ->selectSub($this->stats->articles()->selectRaw('COUNT(*)')->whereColumn('articles.user_id', 'users.id')->where('articles.status', 'approved'), 'annonces_en_ligne');
        }

        if ($this->search !== '') {
            $query->where('users.name', 'like', "%{$this->search}%");
        }

        return [
            'description' => 'Boutiques (profils vendeurs) visitées pendant la période, avec l\'audience de leurs annonces.',
            'query' => $query,
            'cle' => 'users.id',
            'tri' => 'visites_boutique',
            'recherche' => 'Rechercher un vendeur…',
            'avertissement' => $this->stats->hasVisites() ? null : 'Le suivi des visites n\'est pas encore activé (php artisan migrate).',
            'colonnes' => [
                'personne' => ['label' => 'Vendeur', 'format' => 'personne', 'tri' => 'users.name', 'sens' => 'asc'],
                'visites_boutique' => ['label' => 'Visites boutique', 'format' => 'num', 'tri' => 'visites_boutique'],
                'vues_annonces' => ['label' => 'Vues de ses annonces', 'format' => 'num', 'tri' => 'vues_annonces'],
                'visiteurs' => ['label' => 'Visiteurs uniques', 'format' => 'num', 'tri' => 'visiteurs'],
                'annonces_en_ligne' => ['label' => 'Annonces en ligne', 'format' => 'num', 'tri' => 'annonces_en_ligne'],
                'meilleure' => ['label' => 'Son annonce la plus vue', 'format' => 'annonce_mini'],
            ],
            'hydrate' => function ($items) use ($period) {
                $best = [];
                if ($this->stats->hasVisites() && $items->isNotEmpty()) {
                    $this->stats->articleVisits()
                        ->whereBetween('stat_visites.created_at', $period)
                        ->whereIn('stat_visites.vendeur_id', $items->pluck('id'))
                        ->whereNotNull('stat_visites.article_id')
                        ->selectRaw('stat_visites.vendeur_id as vid, stat_visites.article_id as aid, COUNT(*) as vues')
                        ->groupBy('stat_visites.vendeur_id', 'stat_visites.article_id')
                        ->get()
                        ->each(function ($r) use (&$best) {
                            if (! isset($best[$r->vid]) || $r->vues > $best[$r->vid]['vues']) {
                                $best[$r->vid] = ['id' => (int) $r->aid, 'vues' => (int) $r->vues];
                            }
                        });
                }
                $articles = $this->stats->loadArticles(array_column($best, 'id'));

                return $this->userRows($items, function ($row, User $u) use ($best, $articles) {
                    $top = $best[$u->id] ?? null;

                    return [
                        'visites_boutique' => (int) $row->visites_boutique,
                        'vues_annonces' => (int) $row->vues_annonces,
                        'visiteurs' => (int) $row->visiteurs,
                        'annonces_en_ligne' => (int) $row->annonces_en_ligne,
                        'meilleure' => $top ? $this->annonce($articles->get($top['id']), $top['id']) + ['note' => $top['vues'] . ' vues'] : null,
                    ];
                });
            },
        ];
    }

    // ------------------------------------------------------------------
    // Catégories, villes, mots-clés
    // ------------------------------------------------------------------

    private function categories(): array
    {
        $period = $this->stats->period();
        $articlesOf = fn () => $this->stats->articles()
            ->join('sous_categories', 'sous_categories.id', '=', 'articles.sous_categorie_id')
            ->whereColumn('sous_categories.categorie_id', 'categories.id')
            ->whereBetween('articles.created_at', $period);

        $query = DB::table('categories')->select('categories.id', 'categories.nom')
            ->selectSub($articlesOf()->selectRaw('COUNT(*)'), 'annonces')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('sous_categories')->whereColumn('sous_categories.categorie_id', 'categories.id'));

        $this->addViewsByJoin($query, 'sous_categories.categorie_id', 'categories.id');

        if ($this->search !== '') {
            $query->where('categories.nom', 'like', "%{$this->search}%");
        }

        $total = $this->stats->articles()->whereBetween('articles.created_at', $period)->count();
        $params = $this->stats->queryParams();

        return [
            'description' => 'Nombre d\'annonces publiées par catégorie pendant la période. Cliquez une catégorie pour filtrer toute la page Statistiques.',
            'query' => $query,
            'cle' => 'categories.id',
            'tri' => 'annonces',
            'recherche' => 'Rechercher une catégorie…',
            'colonnes' => [
                'nom' => ['label' => 'Catégorie', 'format' => 'texte_fort', 'tri' => 'categories.nom', 'sens' => 'asc'],
                'annonces' => ['label' => 'Annonces', 'format' => 'num', 'tri' => 'annonces'],
                'part' => ['label' => 'Part', 'format' => 'barre'],
                'vues' => ['label' => 'Vues', 'format' => 'num', 'tri' => 'vues'],
            ],
            'hydrate' => fn ($items) => $items->map(fn ($row) => [
                'nom' => $row->nom,
                'annonces' => (int) $row->annonces,
                'part' => $this->stats->percent((int) $row->annonces, $total),
                'vues' => (int) $row->vues,
                '_href' => route('admin.statistics.index', ['categorie' => 'c' . $row->id] + $params),
            ])->all(),
        ];
    }

    private function sousCategories(): array
    {
        $period = $this->stats->period();

        $query = DB::table('sous_categories')
            ->leftJoin('categories', 'categories.id', '=', 'sous_categories.categorie_id')
            ->select('sous_categories.id', 'sous_categories.nom', 'categories.nom as categorie')
            ->selectSub(
                $this->stats->articles()->selectRaw('COUNT(*)')->whereColumn('articles.sous_categorie_id', 'sous_categories.id')->whereBetween('articles.created_at', $period),
                'annonces'
            );

        $this->addViewsByJoin($query, 'articles.sous_categorie_id', 'sous_categories.id');

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('sous_categories.nom', 'like', "%{$this->search}%")->orWhere('categories.nom', 'like', "%{$this->search}%"));
        }

        $total = $this->stats->articles()->whereBetween('articles.created_at', $period)->count();
        $params = $this->stats->queryParams();

        return [
            'description' => 'Cliquez une sous-catégorie pour filtrer toute la page Statistiques.',
            'query' => $query,
            'cle' => 'sous_categories.id',
            'tri' => 'annonces',
            'recherche' => 'Rechercher une sous-catégorie…',
            'colonnes' => [
                'nom' => ['label' => 'Sous-catégorie', 'format' => 'texte_fort', 'tri' => 'sous_categories.nom', 'sens' => 'asc'],
                'categorie' => ['label' => 'Catégorie', 'format' => 'texte', 'tri' => 'categories.nom', 'sens' => 'asc'],
                'annonces' => ['label' => 'Annonces', 'format' => 'num', 'tri' => 'annonces'],
                'part' => ['label' => 'Part', 'format' => 'barre'],
                'vues' => ['label' => 'Vues', 'format' => 'num', 'tri' => 'vues'],
            ],
            'hydrate' => fn ($items) => $items->map(fn ($row) => [
                'nom' => $row->nom,
                'categorie' => $row->categorie ?? '—',
                'annonces' => (int) $row->annonces,
                'part' => $this->stats->percent((int) $row->annonces, $total),
                'vues' => (int) $row->vues,
                '_href' => route('admin.statistics.index', ['categorie' => 's' . $row->id] + $params),
            ])->all(),
        ];
    }

    /** Ajoute la colonne « vues » (période) d'une catégorie / sous-catégorie. */
    private function addViewsByJoin(Builder $query, string $column, string $outer): void
    {
        if (! $this->stats->hasVisites()) {
            $query->selectRaw('0 as vues');

            return;
        }

        $views = $this->stats->filterArticles(
            DB::table('stat_visites')
                ->join('articles', 'articles.id', '=', 'stat_visites.article_id')
                ->join('sous_categories', 'sous_categories.id', '=', 'articles.sous_categorie_id')
                ->selectRaw('COUNT(*)')
        )
            ->where('stat_visites.type', 'article')
            ->whereBetween('stat_visites.created_at', $this->stats->period())
            ->whereColumn($column, $outer);

        $query->selectSub($views, 'vues');
    }

    private function villes(): array
    {
        $rows = $this->stats->villeRows();

        // Vues par ville (orthographes fusionnées)
        $views = [];
        if ($this->stats->hasVisites()) {
            $this->stats->filterArticles(DB::table('stat_visites')->join('articles', 'articles.id', '=', 'stat_visites.article_id'))
                ->where('stat_visites.type', 'article')
                ->whereBetween('stat_visites.created_at', $this->stats->period())
                ->selectRaw('articles.lieu as lieu, COUNT(*) as total')
                ->groupBy('articles.lieu')
                ->get()
                ->each(function ($r) use (&$views) {
                    $key = StatTracker::normalize((string) $r->lieu);
                    $views[$key] = ($views[$key] ?? 0) + (int) $r->total;
                });
        }

        $params = $this->stats->queryParams();

        return [
            'description' => 'Villes où les annonces de la période ont été publiées. Cliquez une ville pour filtrer toute la page Statistiques.',
            'items' => array_map(fn ($v) => $v + [
                'vues' => $views[StatTracker::normalize($v['nom'])] ?? 0,
                '_href' => route('admin.statistics.index', ['ville' => $v['nom']] + $params),
            ], $rows),
            'champ_recherche' => 'nom',
            'tri' => 'annonces',
            'recherche' => 'Rechercher une ville…',
            'colonnes' => [
                'nom' => ['label' => 'Ville', 'format' => 'texte_fort', 'tri' => 'nom', 'sens' => 'asc'],
                'annonces' => ['label' => 'Annonces', 'format' => 'num', 'tri' => 'annonces'],
                'part' => ['label' => 'Part', 'format' => 'barre', 'tri' => 'part'],
                'vues' => ['label' => 'Vues', 'format' => 'num', 'tri' => 'vues'],
            ],
        ];
    }

    private function motsCles(): array
    {
        $kind = $this->request->query('liste') === 'expressions' ? 'expressions' : 'mots';
        $rows = $this->stats->keywordRows()[$kind];
        $params = $this->stats->queryParams();

        return [
            'titre' => $kind === 'expressions' ? 'Expressions des titres (2 mots)' : 'Mots-clés des titres',
            'description' => 'Nombre d\'annonces de la période dont le titre contient ce mot. Cliquez pour voir ces annonces.',
            'items' => array_map(fn ($m) => $m + [
                '_href' => route('admin.statistics.details', ['type' => 'annonces', 'mot' => $m['mot']] + $params),
            ], $rows),
            'champ_recherche' => 'mot',
            'tri' => 'annonces',
            'recherche' => 'Rechercher un mot…',
            'onglets' => [
                'mots' => ['Mots', $kind === 'mots'],
                'expressions' => ['Expressions', $kind === 'expressions'],
            ],
            'colonnes' => [
                'mot' => ['label' => $kind === 'expressions' ? 'Expression' : 'Mot', 'format' => 'texte_fort', 'tri' => 'mot', 'sens' => 'asc'],
                'annonces' => ['label' => 'Annonces', 'format' => 'num', 'tri' => 'annonces'],
                'part' => ['label' => '% des titres', 'format' => 'barre', 'tri' => 'part'],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Recherches
    // ------------------------------------------------------------------

    private function recherches(): array
    {
        $filtres = [];
        $query = DB::table('stat_recherches');

        if (! $this->stats->hasRecherches()) {
            return [
                'query' => DB::table('users')->whereRaw('1 = 0')->selectRaw("'' as terme, 0 as recherches, 0 as chercheurs, 0 as resultats, 0 as sans_resultat, '' as premiere, '' as derniere"),
                'tri' => 'recherches',
                'avertissement' => 'Le suivi des recherches n\'est pas encore activé (php artisan migrate).',
                'colonnes' => $this->rechercheColumns(),
                'hydrate' => fn ($items) => [],
            ];
        }

        $query->whereBetween('created_at', $this->stats->period());

        if ($this->request->query('sans_resultat') === '1') {
            $query->where('resultats', 0);
            $filtres['sans_resultat'] = 'Seulement les recherches sans résultat';
        }

        $source = $this->request->query('source');
        $sources = ['accueil' => 'Barre d\'accueil', 'recherche' => 'Page de recherche', 'recherche_directe' => 'Recherche en direct'];
        if (array_key_exists($source, $sources)) {
            $query->where('source', $source);
            $filtres['source'] = 'Origine : ' . $sources[$source];
        }

        if ($this->search !== '') {
            $query->where('terme_normalise', 'like', '%' . StatTracker::normalize($this->search) . '%');
        }

        $query->selectRaw('terme_normalise, MAX(terme) as terme, COUNT(*) as recherches, COUNT(DISTINCT visiteur_hash) as chercheurs,
                AVG(resultats) as resultats, SUM(CASE WHEN resultats = 0 THEN 1 ELSE 0 END) as sans_resultat,
                MIN(created_at) as premiere, MAX(created_at) as derniere')
            ->groupBy('terme_normalise');

        return [
            'description' => 'Ce que les visiteurs ont tapé dans la recherche. Cliquez une recherche pour voir les annonces qui y correspondent aujourd\'hui.',
            'query' => $query,
            'tri' => 'recherches',
            'filtres' => $filtres,
            'recherche' => 'Rechercher un terme…',
            'colonnes' => $this->rechercheColumns(),
            'hydrate' => fn ($items) => $items->map(fn ($row) => [
                'terme' => $row->terme,
                'recherches' => (int) $row->recherches,
                'chercheurs' => (int) $row->chercheurs,
                'resultats' => (int) round((float) $row->resultats),
                'sans_resultat' => (int) $row->sans_resultat,
                'premiere' => $row->premiere,
                'derniere' => $row->derniere,
                '_href' => route('admin.statistics.details', ['type' => 'annonces', 'recherche' => $row->terme, 'periode' => 'tout']),
            ])->all(),
        ];
    }

    private function rechercheColumns(): array
    {
        return [
            'terme' => ['label' => 'Recherche', 'format' => 'texte_fort', 'tri' => 'terme', 'sens' => 'asc'],
            'recherches' => ['label' => 'Fois', 'format' => 'num', 'tri' => 'recherches'],
            'chercheurs' => ['label' => 'Visiteurs', 'format' => 'num', 'tri' => 'chercheurs'],
            'resultats' => ['label' => 'Résultats moyens', 'format' => 'num_alerte', 'tri' => 'resultats'],
            'sans_resultat' => ['label' => 'Sans résultat', 'format' => 'num', 'tri' => 'sans_resultat'],
            'premiere' => ['label' => 'Première', 'format' => 'datetime', 'tri' => 'premiere'],
            'derniere' => ['label' => 'Dernière', 'format' => 'datetime', 'tri' => 'derniere'],
        ];
    }

    // ------------------------------------------------------------------
    // Mise en forme des lignes
    // ------------------------------------------------------------------

    /**
     * Lignes d'annonces : charge les annonces de la page en une requête.
     */
    private function articleRows($items, callable $extra): array
    {
        $articles = $this->stats->loadArticles($items->pluck('id')->unique()->all());

        return $items->map(function ($row) use ($articles, $extra) {
            $article = $articles->get($row->id);
            $cols = $this->stats->articleColumns($article, (int) $row->id);

            return [
                'annonce' => $this->annonce($article, (int) $row->id),
                'vendeur' => $cols['vendeur'],
                'ville' => $cols['lieu'],
                'prix' => $cols['prix'],
                'statut' => $cols['statut'],
                'publie_le' => $article?->created_at,
                '_href' => route('admin.statistics.annonce', ['article' => $row->id] + $this->stats->queryParams()),
            ] + $extra($row, $article);
        })->all();
    }

    private function annonce($article, int $id): array
    {
        $cols = $this->stats->articleColumns($article, $id);

        return [
            'titre' => $cols['titre'],
            'photo' => $cols['photo'],
            'sous' => trim(($cols['categorie'] ?? '') . ($cols['lieu'] ? ' · ' . $cols['lieu'] : ''), ' ·'),
            'url' => $cols['url'],
            'fiche' => $article ? route('admin.statistics.annonce', ['article' => $id] + $this->stats->queryParams()) : null,
        ];
    }

    private function userRows($items, callable $extra): array
    {
        $users = User::whereIn('id', $items->pluck('id'))->get()->keyBy('id');

        return $items->map(function ($row) use ($users, $extra) {
            $user = $users->get($row->id);
            if (! $user) {
                return ['personne' => $this->personne(null, (int) $row->id)];
            }

            return [
                'personne' => $this->personne($user, (int) $row->id),
                '_href' => route('admin.statistics.vendeur', ['user' => $user->id] + $this->stats->queryParams()),
            ] + $extra($row, $user);
        })->all();
    }

    private function personne(?User $user, int $id): array
    {
        $cols = $this->stats->userColumns($user, $id);

        return [
            'nom' => $cols['nom'],
            'photo' => $cols['photo'],
            'sous' => $cols['email'],
            'certifie' => $cols['certifie'],
            'fiche' => $user ? route('admin.statistics.vendeur', ['user' => $id] + $this->stats->queryParams()) : null,
        ];
    }

    /**
     * Valeur texte d'une cellule (export CSV).
     */
    public static function plainValue(mixed $value, string $format): string
    {
        return match (true) {
            $value === null => '',
            is_array($value) => (string) ($value['titre'] ?? $value['nom'] ?? ''),
            $format === 'bool' => $value ? 'oui' : 'non',
            $format === 'statut' => self::STATUTS[$value] ?? (string) $value,
            in_array($format, ['date', 'datetime'], true) => $value ? CarbonImmutable::parse($value)->format($format === 'date' ? 'd/m/Y' : 'd/m/Y H:i') : '',
            default => (string) $value,
        };
    }

    public static function statutLabel(?string $status): string
    {
        return self::STATUTS[$status] ?? '—';
    }
}
