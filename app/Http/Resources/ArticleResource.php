<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Services\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Annonce telle que l'appli mobile la reçoit.
 * Liste : champs de carte. Détail (avec description chargée) : tout le reste en plus.
 *
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwner = $user && (int) $user->id === (int) $this->user_id;

        $photos = [];
        foreach (array_slice(Article::PHOTO_FIELDS, 0, 6) as $field) {
            if ($this->resource->getAttribute($field)) {
                $photos[] = MediaStorage::url($this->resource->getAttribute($field));
            }
        }

        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'prix' => (float) $this->prix_ht,
            'lieu' => $this->lieu,
            'neuf' => (bool) $this->neuf,
            'livraison' => (bool) $this->livraison,
            'photo' => MediaStorage::url($this->photo),
            'photos' => $this->when($this->hasAttribute('description'), fn () => $photos ?: [MediaStorage::url(null)]),
            'description' => $this->when($this->hasAttribute('description'), fn () => $this->description),
            'boosted' => $this->isBoosted(),
            'boosted_until' => $this->boosted_until?->toIso8601String(),
            'likes' => (int) ($this->users_who_liked_count ?? $this->usersWhoLiked()->count()),
            'liked' => $user ? $this->isLikedByCurrentUser() : false,
            'comments_count' => $this->when(isset($this->comments_count), fn () => (int) $this->comments_count),
            'status' => $this->when($isOwner || $user?->isAdmin(), $this->status),
            'block_reason' => $this->when($isOwner && $this->status === 'blocked', $this->block_reason),
            'categorie' => $this->whenLoaded('sousCategorie', fn () => $this->sousCategorie?->categorie ? [
                'id' => $this->sousCategorie->categorie->id,
                'nom' => $this->sousCategorie->categorie->nom,
            ] : null),
            'sous_categorie' => $this->whenLoaded('sousCategorie', fn () => $this->sousCategorie ? [
                'id' => $this->sousCategorie->id,
                'nom' => $this->sousCategorie->nom,
            ] : null),
            'vendeur' => new SellerResource($this->whenLoaded('user')),
            'url' => $this->whenLoaded('sousCategorie', fn () => $this->url()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Indique en une seule requête quelles annonces l'utilisateur a likées
     * (sinon une requête par annonce de la liste).
     *
     * @template T of Article|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection
     *
     * @param  T  $items
     * @return T
     */
    public static function markLikedBy($items, ?User $user)
    {
        if (! $user) {
            return $items;
        }

        $articles = match (true) {
            $items instanceof Article => collect([$items]),
            $items instanceof Paginator => $items->getCollection(),
            default => collect($items),
        };

        $liked = DB::table('article_user_like')
            ->where('user_id', $user->id)
            ->whereIn('article_id', $articles->pluck('id'))
            ->pluck('article_id')
            ->flip();

        $articles->each(fn (Article $a) => $a->setAttribute('liked_by_me_count', isset($liked[$a->id]) ? 1 : 0));

        return $items;
    }

    private function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->resource->getAttributes());
    }
}
