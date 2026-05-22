<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\AdminNotification;

class CreateAdminNotification
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        AdminNotification::createForNewUser($event->user);
    }
}
