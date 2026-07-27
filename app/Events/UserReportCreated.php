<?php

namespace App\Events;

use App\Models\UserReport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReportCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public UserReport $report)
    {
    }
}
