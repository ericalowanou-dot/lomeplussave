<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckImageExtensionsCommand extends Command
{
    protected $signature = 'images:check';

    protected $description = 'Vérifie les extensions PHP et la configuration nécessaires pour l\'upload d\'images.';

    private array $errors = [];
    private array $warnings = [];

    public function handle(): int
    {
        $this->info('Vérification des extensions et de la configuration pour l\'upload d\'images…');
        $this->newLine();

        $this->checkPhpExtensions();
        $this->checkGdCapabilities();
        $this->checkInterventionImage();
        $this->checkUploadDirs();
        $this->checkPhpUploadLimits();

        $this->newLine();
        $this->printSummary();

        return count($this->errors) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkPhpExtensions(): void
    {
        $this->info('▸ Extensions PHP');

        $required = ['gd', 'fileinfo'];
        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                $this->line("  <fg=green>✓</> {$ext}");
            } else {
                $this->errors[] = "Extension PHP manquante : <strong>{$ext}</strong>. Activez-la dans php.ini (extension={$ext}) puis redémarrez le serveur.";
                $this->line("  <fg=red>✗</> {$ext} <fg=red>(manquante)</>");
            }
        }

        if (extension_loaded('exif')) {
            $this->line('  <fg=green>✓</> exif (optionnel, utile pour EXIF)');
        } else {
            $this->line('  <fg=yellow>○</> exif (optionnel, non chargée)');
        }

        $this->newLine();
    }

    private function checkGdCapabilities(): void
    {
        $this->info('▸ Capacités GD');

        if (!extension_loaded('gd')) {
            $this->warnings[] = 'GD non chargé : impossible de vérifier les formats.';
            $this->newLine();
            return;
        }

        $info = gd_info();
        $formats = [
            'JPEG Support' => 'JPEG',
            'PNG Support' => 'PNG',
            'GIF Create Support' => 'GIF',
            'WebP Support' => 'WebP',
            'AVIF Support' => 'AVIF',
            'BMP Support' => 'BMP',
        ];

        foreach ($formats as $key => $label) {
            $ok = !empty($info[$key]);
            if ($ok) {
                $this->line("  <fg=green>✓</> {$label}");
            } else {
                $this->warnings[] = "GD : {$label} non supporté par cette build.";
                $this->line("  <fg=yellow>○</> {$label} <fg=yellow>(non supporté)</>");
            }
        }

        $this->line('  <fg=blue>i</> HEIC/HEIF : non supporté par GD (stocké tel quel si uploadé).');
        $this->newLine();
        $this->info('▸ Formats (articles)');
        $this->line('  Optimisés (GD) : JPEG, PNG, GIF, WebP, BMP, AVIF*.');
        $this->line('  Stockés tel quel : SVG, HEIC/HEIF. *AVIF selon build PHP.');
        $this->newLine();
    }

    private function checkInterventionImage(): void
    {
        $this->info('▸ Intervention Image (driver GD)');

        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $this->line('  <fg=green>✓</> ImageManager initialisé');
        } catch (\Throwable $e) {
            $this->errors[] = 'Impossible d\'initialiser Intervention Image : ' . $e->getMessage();
            $this->line('  <fg=red>✗</> ' . $e->getMessage());
        }

        try {
            $optimizer = new \App\Services\ImageOptimizer();
            $this->line('  <fg=green>✓</> ImageOptimizer opérationnel');
        } catch (\Throwable $e) {
            $this->errors[] = 'ImageOptimizer : ' . $e->getMessage();
            $this->line('  <fg=red>✗</> ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function checkUploadDirs(): void
    {
        $this->info('▸ Dossiers d’upload');

        $dirs = [
            'articles' => public_path('articles'),
            'users/profil' => public_path('users/profil'),
            'categories/images' => public_path('categories/images'),
            'souscategories/images' => public_path('souscategories/images'),
            'advertisements' => public_path('advertisements'),
        ];

        foreach ($dirs as $label => $path) {
            if (!file_exists($path)) {
                $this->warnings[] = "Dossier manquant : {$label} ({$path}). Exécutez <info>php artisan images:migrate</info> si besoin.";
                $this->line("  <fg=yellow>○</> {$label} <fg=yellow>(absent)</>");
            } elseif (!is_writable($path)) {
                $this->errors[] = "Dossier non accessible en écriture : {$label} ({$path}).";
                $this->line("  <fg=red>✗</> {$label} <fg=red>(non writable)</>");
            } else {
                $this->line("  <fg=green>✓</> {$label}");
            }
        }

        $this->newLine();
    }

    private function checkPhpUploadLimits(): void
    {
        $this->info('▸ Limites PHP (upload)');

        $maxUpload = ini_get('upload_max_filesize');
        $maxPost = ini_get('post_max_size');
        $this->line("  upload_max_filesize : <info>{$maxUpload}</info>");
        $this->line("  post_max_size      : <info>{$maxPost}</info>");

        $uploadBytes = $this->iniSizeToBytes($maxUpload);
        $postBytes = $this->iniSizeToBytes($maxPost);
        $expected = 30 * 1024 * 1024; // 30 Mo pour les articles

        if ($uploadBytes > 0 && $uploadBytes < $expected) {
            $this->warnings[] = 'upload_max_filesize (' . $maxUpload . ') < 30 Mo. Les articles acceptent jusqu’à 30 Mo par image.';
        }
        if ($postBytes > 0 && $postBytes < 6 * $expected) {
            $this->warnings[] = 'post_max_size (' . $maxPost . ') < 180 Mo. Recommandé ≥ 200M pour 6 images × 30 Mo.';
        }

        $this->newLine();
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $num = (int) $value;
        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $value,
        };
    }

    private function printSummary(): void
    {
        if (count($this->errors) > 0) {
            $this->error('Erreurs :');
            foreach ($this->errors as $msg) {
                $this->line('  • ' . strip_tags($msg));
            }
            $this->newLine();
        }

        if (count($this->warnings) > 0) {
            $this->warn('Avertissements :');
            foreach ($this->warnings as $msg) {
                $this->line('  • ' . $msg);
            }
            $this->newLine();
        }

        if (count($this->errors) === 0 && count($this->warnings) === 0) {
            $this->info('Tout est OK pour l’upload d’images.');
            return;
        }

        if (count($this->errors) === 0) {
            $this->info('Aucune erreur bloquante. Vérifiez les avertissements si besoin.');
        }
    }
}
