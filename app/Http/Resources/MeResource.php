<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compte de l'utilisateur connecté (lui seul le voit).
 *
 * @mixin User
 */
class MeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->name,
            'email' => $this->email,
            'email_verifie' => $this->email_verified_at !== null,
            'telephone' => $this->telephone,
            'whatsapp' => $this->whatsapp,
            'photo' => $this->getProfilPhotoUrl(),
            'coins' => (int) ($this->coins ?? 0),
            'certifie' => $this->estCertifie(),
            'certifie_until' => $this->certifie_until?->toIso8601String(),
            'boutique_url' => $this->shopUrl(),
            'is_admin' => $this->isAdmin(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
