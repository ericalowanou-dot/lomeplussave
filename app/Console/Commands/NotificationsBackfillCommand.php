<?php

namespace App\Console\Commands;

use App\Models\ProblemReport;
use App\Models\AdminNotification;
use App\Events\ProblemReportCreated;
use Illuminate\Console\Command;

class NotificationsBackfillCommand extends Command
{
    protected $signature = 'notifications:backfill';

    protected $description = 'Crée les notifications admin pour les signalements existants qui n\'en ont pas.';

    public function handle(): int
    {
        $this->info('Création des notifications pour les signalements existants…');

        $reports = ProblemReport::orderBy('id')->get();
        $created = 0;

        foreach ($reports as $report) {
            $exists = AdminNotification::where('related_type', ProblemReport::class)
                ->where('related_id', $report->id)
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                AdminNotification::createForProblemReport($report);
                $created++;
                $this->line('  • Signalement #' . $report->id);
            } catch (\Throwable $e) {
                $this->warn('  Erreur pour #' . $report->id . ': ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info($created . ' notification(s) créée(s) pour ' . $reports->count() . ' signalement(s).');

        return self::SUCCESS;
    }
}
