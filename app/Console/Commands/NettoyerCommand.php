<?php

namespace App\Console\Commands;

use App\Services\Maintenance;
use Illuminate\Console\Command;

class NettoyerCommand extends Command
{
    protected $signature = 'lomeplus:nettoyer';

    protected $description = 'Supprime les sessions de visiteurs non connectés inactifs depuis plus de 2 jours et le cache expiré';

    public function handle(Maintenance $maintenance): int
    {
        $this->info('Nettoyage en cours…');
        $result = $maintenance->run();

        $this->line(sprintf('  Sessions de visiteurs supprimées : %d', $result['sessions']));
        $this->line(sprintf('  Entrées de cache expirées supprimées : %d', $result['cache']));
        $this->info('Terminé. Les comptes connectés n\'ont pas été déconnectés.');

        return self::SUCCESS;
    }
}
