<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Message $message)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $senderName = $this->message->sender->name ?? 'Lome+';
        $excerpt = \Illuminate\Support\Str::limit(strip_tags($this->message->body), 100);

        return (new WebPushMessage())
            ->title('Nouveau message de ' . $senderName)
            ->icon('/images/icons/icon-192.png')
            ->body($excerpt)
            ->data(['url' => route('messages.show', $this->message)]);
    }
}
