<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Models\SousCategorie;
use App\Services\Search\RankedPaginator;
use App\Services\Search\SearchEngine;
use App\Services\Search\SearchOutcome;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

/**
 * Requêtes de liste d'annonces publiques (accueil, recherche), communes au site et à l'API.
 * Seules les annonces validées sont renvoyées.
 */
class ArticleSearch
{
    /** Colonnes utiles à une carte d'annonce dans une liste. */
    public const CARD_COLUMNS = ['id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison'];

    /** Paramètres acceptés par browse(), dans l'URL du site comme dans l'API. */
    public const BROWSE_FILTERS = ['q', 'sous_categorie', 'categorie', 'prix_min', 'prix_max', 'ville', 'etat', 'pro_only', 'livraison_only', 'order_by'];

    public const CARD_RELATIONS = ['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'];

    public function __construct(private SearchEngine $engine)
    {
    }

    public function usesCascade(): bool
    {
        return config('search.engine') === 'cascade';
    }

    /** Accueil et liste de l'API : le texte passe par le moteur, les filtres restent stricts. */
    public function paginateBrowse(array $filters, int $perPage): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));

        if (! $this->usesCascade() || $q === '') {
            return $this->browse($filters)->paginate($perPage);
        }

        $base = $this->approved();
        $this->applyFilters($base, $filters);
        $active = array_filter(
            array_diff_key(array_intersect_key($filters, array_flip(self::BROWSE_FILTERS)), ['q' => 1, 'order_by' => 1]),
            fn ($value) => $value !== null && $value !== '' && $value !== []
        );

        return $this->page($this->engine->run($q, $base, $active, $this->userCity()), $perPage, $filters['order_by'] ?? null);
    }

    /** Page /search, /search/articles et recherche de l'API. */
    public function paginateSearch(string $q, int $perPage): LengthAwarePaginator
    {
        $q = trim($q);

        if (! $this->usesCascade() || $q === '') {
            return $this->search($q)->paginate($perPage);
        }

        return $this->page($this->engine->run($q, $this->approved(), [], $this->userCity()), $perPage);
    }

    private function userCity(): ?string
    {
        return auth()->user()?->ville;
    }

    /**
     * Filtres de la page d'accueil.
     *
     * @param  array{q?: ?string, sous_categorie?: mixed, categorie?: mixed, prix_min?: mixed, prix_max?: mixed,
     *               ville?: ?string, etat?: ?string, pro_only?: mixed, livraison_only?: mixed, order_by?: ?string}  $filters
     */
    public function browse(array $filters): Builder
    {
        $query = Article::query();

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('titre', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('lieu', 'like', "%{$q}%");
            });
        }

        $this->applyFilters($query, $filters);

        $query->where('status', 'approved');

        match ($filters['order_by'] ?? 'recent') {
            'prix_asc' => $query->orderBy('prix_ht', 'asc')->orderBy('created_at', 'desc'),
            'prix_desc' => $query->orderBy('prix_ht', 'desc')->orderBy('created_at', 'desc'),
            default => $this->boostedFirst($query),
        };

        return $this->asCards($query);
    }

    /**
     * Recherche large : titre, description, lieu, vendeur (nom, ville), catégories.
     */
    public function search(string $q): Builder
    {
        $q = trim($q);
        $query = Article::where('status', 'approved');

        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('titre', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhere('lieu', 'like', "%$q%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        // Pas l'email : taper « gmail » ressortait toutes les annonces des vendeurs Gmail
                        $userQuery->where('name', 'like', "%$q%")
                            ->orWhere('ville', 'like', "%$q%");
                    })
                    ->orWhereHas('sousCategorie', function ($subQuery) use ($q) {
                        $subQuery->where('nom', 'like', "%$q%")
                            ->orWhereHas('categorie', function ($catQuery) use ($q) {
                                $catQuery->where('nom', 'like', "%$q%");
                            });
                    });
            });
        }

        return $this->boostedFirst($this->asCards($query));
    }

    /** Filtres choisis par l'utilisateur : ils excluent, contrairement aux critères déduits du texte. */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Sous-catégorie prioritaire sur catégorie
        if (self::filled($filters, 'sous_categorie')) {
            $query->where('sous_categorie_id', $filters['sous_categorie']);
        } elseif (self::filled($filters, 'categorie')) {
            $query->whereIn('sous_categorie_id', $this->subcategoryIds($filters['categorie']));
        }

        if (self::filled($filters, 'prix_min')) {
            $query->where('prix_ht', '>=', $filters['prix_min']);
        }
        if (self::filled($filters, 'prix_max')) {
            $query->where('prix_ht', '<=', $filters['prix_max']);
        }
        if (self::filled($filters, 'ville')) {
            $query->where('lieu', $filters['ville']);
        }
        if (in_array($filters['etat'] ?? null, ['neuf', 'occasion'], true)) {
            $query->where('neuf', $filters['etat'] === 'neuf');
        }
        if (filter_var($filters['pro_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('boosted_until')->where('boosted_until', '>', now());
        }
        if (filter_var($filters['livraison_only'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('livraison', true);
        }
    }

    private function approved(): Builder
    {
        return Article::query()->where('status', 'approved');
    }

    private function page(SearchOutcome $outcome, int $perPage, ?string $orderBy = null): RankedPaginator
    {
        $ids = in_array($orderBy, ['prix_asc', 'prix_desc'], true)
            ? $this->sortedByPrice($outcome->ids, $orderBy)
            : $outcome->ids;

        $page = Paginator::resolveCurrentPage();
        $pageIds = array_slice($ids, ($page - 1) * $perPage, $perPage);

        $articles = $this->asCards(Article::query()->whereIn('id', $pageIds))->get()
            ->sortBy(fn (Article $article) => array_search($article->id, $pageIds, true))
            ->values();

        $paginator = new RankedPaginator($articles, count($ids), $perPage, $page, ['path' => Paginator::resolveCurrentPath()]);
        $paginator->outcome = $outcome;

        return $paginator;
    }

    /** Tri demandé par l'utilisateur : il remplace l'ordre de la cascade, comme sur la liste normale. */
    private function sortedByPrice(array $ids, string $orderBy): array
    {
        $rows = [];
        foreach (array_chunk($ids, 1000) as $chunk) {
            foreach (Article::query()->whereIn('id', $chunk)->get(['id', 'prix_ht', 'created_at']) as $article) {
                $rows[] = [$article->id, (float) $article->prix_ht, $article->created_at?->getTimestamp() ?? 0];
            }
        }

        $direction = $orderBy === 'prix_asc' ? 1 : -1;
        usort($rows, fn ($a, $b) => $direction * ($a[1] <=> $b[1]) ?: [$b[2], $b[0]] <=> [$a[2], $a[0]]);

        return array_column($rows, 0);
    }

    private function asCards(Builder $query): Builder
    {
        return $query->select(self::CARD_COLUMNS)
            ->withLikeCounts(auth()->id())
            ->with(self::CARD_RELATIONS);
    }

    private function boostedFirst(Builder $query): Builder
    {
        return $query->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > ?) DESC', [now()])
            ->orderBy('created_at', 'desc');
    }

    private function subcategoryIds($categorieId)
    {
        return Cache::remember("souscategories_categorie_{$categorieId}", 3600, function () use ($categorieId) {
            return SousCategorie::where('categorie_id', $categorieId)->pluck('id');
        });
    }

    private static function filled(array $filters, string $key): bool
    {
        $value = $filters[$key] ?? null;

        return ! ($value === null || (is_string($value) && trim($value) === '') || $value === []);
    }
}
