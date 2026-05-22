<?php

namespace App\Listeners;

use App\Events\ArticlePending;
use App\Models\AdminNotification;

class CreateAdminNotificationForArticle
{
    /**
     * Handle the event.
     */
    public function handle(ArticlePending $event): void
    {
        // Ne créer une notification que si l'article est en attente
        if ($event->article->status === 'pending') {
            AdminNotification::createForPendingArticle($event->article);
        }
    }
}
