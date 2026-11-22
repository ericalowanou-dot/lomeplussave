<?php

namespace App\Console\Commands;

use App\Models\AdminNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Supprime les notifications admin créées en double (listeners enregistrés deux fois
 * avant le correctif). Garde la plus ancienne de chaque groupe ; si une copie avait
 * déjà été lue, la notification gardée est marquée lue.
 */
class NotificationsDedupeCommand extends Command
{
    protected $signature = 'notifications:dedupe {--force : Supprimer réellement (sinon simple aperçu)}';

    protected $description = 'Supprime les notifications admin en double (même admin, même type, même élément).';

    public function handle(): int
    {
        $groups = AdminNotification::query()
            ->select('admin_id', 'type', 'related_type', 'related_id')
            ->selectRaw('MIN(id) as keep_id, COUNT(*) as total, MAX(is_read) as any_read, MAX(read_at) as last_read_at')
            ->whereNotNull('related_id')
            ->groupBy('admin_id', 'type', 'related_type', 'related_id')
            ->having('total', '>', 1)
            ->get();

        $toDelete = (int) $groups->sum(fn ($g) => $g->total - 1);

        if ($toDelete === 0) {
            $this->info('Aucune notification en double.');
            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->info("{$toDelete} notification(s) en double dans {$groups->count()} groupe(s).");
            $this->line('Relancez avec --force pour les supprimer.');
            return self::SUCCESS;
        }

        $deleted = 0;

        DB::transaction(function () use ($groups, &$deleted) {
            foreach ($groups as $g) {
                $deleted += AdminNotification::query()
                    ->where('admin_id', $g->admin_id)
                    ->where('type', $g->type)
                    ->where('related_type', $g->related_type)
                    ->where('related_id', $g->related_id)
                    ->where('id', '!=', $g->keep_id)
                    ->delete();

                if ($g->any_read) {
                    AdminNotification::whereKey($g->keep_id)
                        ->where('is_read', false)
                        ->update(['is_read' => true, 'read_at' => $g->last_read_at ?? now()]);
                }
            }
        });

        $this->info("{$deleted} notification(s) en double supprimée(s).");

        return self::SUCCESS;
    }
}
