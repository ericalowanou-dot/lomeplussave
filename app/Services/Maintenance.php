<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Petits nettoyages qui évitent que la base et le disque grossissent sans fin.
 * Sans tâche cron : lancés une fois par jour à la première visite d'un admin,
 * ou à la main avec « php artisan lomeplus:nettoyer ».
 */
class Maintenance
{
    /** Sessions de visiteurs non connectés gardées au plus ce nombre de jours. */
    public const GUEST_SESSION_DAYS = 2;

    private const CHUNK = 5000;

    /**
     * Lance le nettoyage quotidien si ce n'est pas déjà fait aujourd'hui.
     * Il s'exécute après l'envoi de la page, pour ne pas ralentir l'admin.
     */
    public static function runDailyIfDue(): void
    {
        try {
            if (! Cache::add('lomeplus:maintenance-quotidienne', now()->toIso8601String(), now()->addDay())) {
                return;
            }
        } catch (\Throwable $e) {
            return; // cache indisponible : on réessaiera plus tard
        }

        app()->terminating(function () {
            try {
                $result = (new self())->run();
                Log::info('Nettoyage quotidien effectué', $result);
            } catch (\Throwable $e) {
                Log::warning('Nettoyage quotidien : ' . $e->getMessage());
            }
        });
    }

    /**
     * @return array{sessions: int, cache: int}
     */
    public function run(): array
    {
        return [
            'sessions' => $this->cleanGuestSessions(),
            'cache' => $this->cleanExpiredCache(),
        ];
    }

    /**
     * Supprime les sessions de visiteurs non connectés inactifs depuis plus de 2 jours.
     * Les sessions des comptes connectés ne sont jamais touchées.
     */
    public function cleanGuestSessions(int $days = self::GUEST_SESSION_DAYS): int
    {
        $table = config('session.table', 'sessions');
        if (config('session.driver') !== 'database' || ! Schema::hasTable($table)) {
            return 0;
        }

        $before = now()->subDays($days)->getTimestamp();

        return $this->deleteInChunks(fn () => DB::table($table)
            ->whereNull('user_id')
            ->where('last_activity', '<', $before), 'id');
    }

    /**
     * Supprime les entrées de cache expirées (Laravel ne les efface pas seul en base).
     */
    public function cleanExpiredCache(): int
    {
        $store = config('cache.default');
        if (config("cache.stores.{$store}.driver") !== 'database') {
            return 0;
        }

        $deleted = 0;
        foreach ([config("cache.stores.{$store}.table", 'cache'), config("cache.stores.{$store}.lock_table", 'cache_locks')] as $table) {
            if ($table && Schema::hasTable($table)) {
                $deleted += $this->deleteInChunks(fn () => DB::table($table)->where('expiration', '<', time()), 'key');
            }
        }

        return $deleted;
    }

    /**
     * Supprime par lots (évite de bloquer la table sur une grosse suppression).
     */
    private function deleteInChunks(callable $query, string $key): int
    {
        $total = 0;
        for ($i = 0; $i < 200; $i++) { // garde-fou : 1 million de lignes au plus par passage
            $ids = $query()->limit(self::CHUNK)->pluck($key);
            if ($ids->isEmpty()) {
                break;
            }
            $total += $query()->whereIn($key, $ids)->delete();
            if ($ids->count() < self::CHUNK) {
                break;
            }
        }

        return $total;
    }

    // ------------------------------------------------------------------
    // Photos orphelines (lancé uniquement à la main)
    // ------------------------------------------------------------------

    /**
     * Photos de public/articles qui n'appartiennent plus à aucune annonce.
     * Les fichiers de moins de 24 h sont ignorés (annonce peut-être en cours de publication).
     *
     * @return array<int, array{path: string, size: int}>
     */
    public function orphanPhotos(): array
    {
        // Outil manuel prévu pour les photos stockées sur ce serveur (public/articles).
        if (! \App\Services\MediaStorage::isLocal()) {
            return [];
        }

        $folder = public_path('articles');
        if (! is_dir($folder)) {
            return [];
        }

        $used = [];
        foreach (Article::PHOTO_FIELDS as $field) {
            DB::table('articles')->whereNotNull($field)->pluck($field)->each(function ($path) use (&$used) {
                $used[basename(str_replace('\\', '/', (string) $path))] = true;
            });
        }

        $recent = now()->subDay()->getTimestamp();
        $orphans = [];
        foreach (File::files($folder) as $file) {
            if (! isset($used[$file->getFilename()]) && $file->getMTime() < $recent) {
                $orphans[] = ['path' => $file->getPathname(), 'size' => $file->getSize()];
            }
        }

        return $orphans;
    }

    /**
     * Déplace les photos orphelines dans storage/app/photos-orphelines/<date>/ (rien n'est effacé).
     *
     * @return array{moved: int, size: int, folder: string}
     */
    public function moveOrphanPhotos(): array
    {
        $target = storage_path('app/photos-orphelines/' . now()->format('Y-m-d_His'));
        File::ensureDirectoryExists($target);

        $moved = $size = 0;
        foreach ($this->orphanPhotos() as $orphan) {
            if (File::move($orphan['path'], $target . DIRECTORY_SEPARATOR . basename($orphan['path']))) {
                $moved++;
                $size += $orphan['size'];
            }
        }

        return ['moved' => $moved, 'size' => $size, 'folder' => $target];
    }
}
