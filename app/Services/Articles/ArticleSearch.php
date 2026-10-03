<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Models\SousCategorie;
use Illuminate\Database\Eloquent\Builder;
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
