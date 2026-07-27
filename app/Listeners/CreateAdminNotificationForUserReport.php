<?php

namespace App\Listeners;

use App\Events\UserReportCreated;
use App\Models\AdminNotification;

class CreateAdminNotificationForUserReport
{
    public function handle(UserReportCreated $event): void
    {
        AdminNotification::createForUserReport($event->report);
    }
}
