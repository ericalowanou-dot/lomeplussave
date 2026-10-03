<?php

namespace App\Http\Resources;

use App\Models\Categorie;
use App\Services\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Categorie */
class CategorieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'image' => MediaStorage::url($this->image),
            'sous_categories' => $this->whenLoaded('sousCategories', fn () => $this->sousCategories->map(fn ($sc) => [
                'id' => $sc->id,
                'nom' => $sc->nom,
                'image' => MediaStorage::url($sc->image),
            ])->values()),
        ];
    }
}
