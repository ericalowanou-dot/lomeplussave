<?php

namespace App\Console\Commands;

use App\Services\Maintenance;
use Illuminate\Console\Command;

class PhotosOrphelinesCommand extends Command
{
    protected $signature = 'lomeplus:photos-orphelines
                            {--deplacer : Déplacer les photos orphelines dans storage/app/photos-orphelines (rien n\'est effacé)}
                            {--force : Ne pas demander de confirmation}';

    protected $description = 'Liste (ou met de côté) les photos de public/articles qui n\'appartiennent plus à aucune annonce';

    public function handle(Maintenance $maintenance): int
    {
        $orphans = $maintenance->orphanPhotos();
        $size = array_sum(array_column($orphans, 'size'));

        if (! $orphans) {
            $this->info('Aucune photo orpheline. Tout est propre.');

            return self::SUCCESS;
        }

        $this->line(sprintf('%d photos orphelines (%s Mo) dans public/articles.', count($orphans), number_format($size / 1048576, 1, ',', ' ')));
        $this->line('Les fichiers de moins de 24 h sont ignorés (publication peut-être en cours).');

        foreach (array_slice($orphans, 0, 10) as $orphan) {
            $this->line('  - ' . basename($orphan['path']));
        }
        if (count($orphans) > 10) {
            $this->line(sprintf('  … et %d autres', count($orphans) - 10));
        }

        if (! $this->option('deplacer')) {
            $this->newLine();
            $this->info('Rien n\'a été modifié. Pour les mettre de côté : php artisan lomeplus:photos-orphelines --deplacer');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Déplacer ces photos dans storage/app/photos-orphelines ?', true)) {
            $this->line('Annulé.');

            return self::SUCCESS;
        }

        $result = $maintenance->moveOrphanPhotos();
        $this->info(sprintf('%d photos déplacées (%s Mo) dans :', $result['moved'], number_format($result['size'] / 1048576, 1, ',', ' ')));
        $this->line('  ' . $result['folder']);
        $this->line('Vérifiez le site pendant quelques jours, puis supprimez ce dossier pour libérer la place.');

        return self::SUCCESS;
    }
}
