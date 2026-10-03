<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Profil public d'un vendeur : jamais d'email ni de données de compte.
 *
 * @mixin User
 */
class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->getAttributes();

        return [
            'id' => $this->id,
            'nom' => $this->name,
            'photo' => $this->getProfilPhotoUrl(),
            'certifie' => $this->estCertifie(),
            'ville' => $this->when(array_key_exists('ville', $attributes), fn () => $this->ville),
            'telephone' => $this->when(array_key_exists('telephone', $attributes), fn () => $this->telephone),
            'whatsapp_url' => $this->when(array_key_exists('whatsapp', $attributes) || array_key_exists('telephone', $attributes), fn () => $this->getWhatsAppUrl()),
            'boutique_url' => $this->shopUrl(),
            'membre_depuis' => $this->when(array_key_exists('created_at', $attributes), fn () => $this->created_at?->toIso8601String()),
        ];
    }
}
