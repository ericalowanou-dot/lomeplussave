<?php

namespace App\Events;

use App\Models\ProblemReport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProblemReportCreated
{
    use Dispatchable, SerializesModels;

    public $report;

    /**
     * Create a new event instance.
     */
    public function __construct(ProblemReport $report)
    {
        $this->report = $report;
    }
}
