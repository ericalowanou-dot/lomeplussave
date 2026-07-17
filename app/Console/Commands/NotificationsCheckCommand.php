<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AdminNotification;
use App\Models\ProblemReport;
use Illuminate\Console\Command;

class NotificationsCheckCommand extends Command
{
    protected $signature = 'notifications:check';

    protected $description = 'Vérifie les admins et les notifications (signalements, etc.).';

    public function handle(): int
    {
        $this->info('Vérification des notifications admin…');
        $this->newLine();

        $admins = User::all()->filter(fn (User $u) => $u->isAdmin());
        $this->info('Admins (isAdmin) : ' . $admins->count());
        foreach ($admins as $a) {
            $this->line('  • ' . $a->email . ' (id=' . $a->id . ', role=' . ($a->role ?? 'null') . ')');
        }
        $this->newLine();

        $reports = ProblemReport::orderByDesc('created_at')->limit(5)->get();
        $this->info('Derniers signalements : ' . $reports->count());
        foreach ($reports as $r) {
            $this->line('  • #' . $r->id . ' ' . $r->created_at?->format('Y-m-d H:i') . ' – ' . \Str::limit($r->message, 40));
        }
        $this->newLine();

        try {
            $notifs = AdminNotification::orderByDesc('created_at')->limit(10)->get();
            $this->info('Dernières notifications : ' . $notifs->count());
            foreach ($notifs as $n) {
                $this->line('  • #' . $n->id . ' admin_id=' . ($n->admin_id ?? 'null') . ' type=' . $n->type . ' ' . $n->created_at?->format('Y-m-d H:i'));
            }
        } catch (\Throwable $e) {
            $this->warn('Impossible de lire les notifications : ' . $e->getMessage());
            $this->line('Exécutez : php artisan migrate');
        }

        if ($admins->isEmpty()) {
            $this->newLine();
            $this->warn('Aucun admin trouvé. Les notifications ne sont pas créées.');
            $this->line('Créez un utilisateur avec role=admin.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('OK.');
        return self::SUCCESS;
    }
}
