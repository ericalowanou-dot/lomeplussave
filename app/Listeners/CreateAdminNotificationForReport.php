<?php

namespace App\Listeners;

use App\Events\ProblemReportCreated;
use App\Models\AdminNotification;

class CreateAdminNotificationForReport
{
    /**
     * Handle the event.
     */
    public function handle(ProblemReportCreated $event): void
    {
        AdminNotification::createForProblemReport($event->report);
    }
}
