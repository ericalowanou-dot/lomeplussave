<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Messagerie entre les utilisateurs et l'administration, commune au site et à l'API.
 * Un utilisateur écrit toujours au support (l'admin) ; l'admin répond depuis le backoffice.
 */
class MessageService
{
    public function inbox(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Message::with('sender')
            ->whereHas('recipients', fn ($q) => $q->where('recipient_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /** Marque le message comme lu si l'utilisateur en est destinataire. */
    public function markRead(Message $message, User $user): void
    {
        $message->recipients()
            ->wherePivot('recipient_id', $user->id)
            ->wherePivotNull('read_at')
            ->updateExistingPivot($user->id, ['read_at' => now()]);
    }

    /**
     * @throws NoSupportAdminException s'il n'existe aucun compte administrateur
     */
    public function sendToSupport(User $sender, string $body, ?Message $parent = null): Message
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            throw new NoSupportAdminException();
        }

        $message = Message::create([
            'sender_id' => $sender->id,
            'subject' => $parent ? 'Re: ' . ($parent->subject ?? 'Message') : null,
            'body' => $body,
            'parent_message_id' => $parent?->id,
            'is_group_message' => false,
        ]);

        $message->recipients()->sync([$admin->id]);

        AdminMailNotifier::messageReceived($message, $sender);

        return $message;
    }

    public function unreadCount(User $user): int
    {
        return $user->belongsToMany(Message::class, 'message_recipients', 'recipient_id', 'message_id')
            ->wherePivotNull('read_at')
            ->count();
    }
}
