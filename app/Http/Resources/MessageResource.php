<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $me = $request->user();
        $sender = $this->whenLoaded('sender');

        return [
            'id' => $this->id,
            'sujet' => $this->subject,
            'contenu' => $this->body,
            'de_moi' => $me !== null && (int) $me->id === (int) $this->sender_id,
            'expediteur' => $this->whenLoaded('sender', fn () => [
                'nom' => $sender?->isAdmin() ? 'Équipe Lome+' : $sender?->name,
                'photo' => $sender?->getProfilPhotoUrl(),
                'admin' => (bool) $sender?->isAdmin(),
            ]),
            'lu' => $this->when(isset($this->pivot) || $this->relationLoaded('recipients'), fn () => $this->readBy($me)),
            'reponse_a' => $this->parent_message_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function readBy($user): ?bool
    {
        if (! $user || (int) $user->id === (int) $this->sender_id) {
            return null;
        }

        $recipient = $this->recipients->firstWhere('id', $user->id);

        return $recipient ? $recipient->pivot->read_at !== null : null;
    }
}
