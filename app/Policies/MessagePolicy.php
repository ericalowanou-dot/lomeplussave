<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /** Un message n'est lisible que par son expéditeur et ses destinataires. */
    public function view(User $user, Message $message): Response
    {
        return $this->involved($user, $message)
            ? Response::allow()
            : Response::deny('Accès non autorisé à ce message.');
    }

    public function reply(User $user, Message $message): Response
    {
        return $this->involved($user, $message)
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas répondre à ce message.');
    }

    private function involved(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id
            || $message->recipients()->where('recipient_id', $user->id)->exists();
    }
}
