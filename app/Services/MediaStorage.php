<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Point d'entrée unique pour les photos du site : enregistrement, URL, suppression.
 *
 * Les chemins gardés en base sont relatifs (« articles/xxx.jpg ») et ne changent jamais.
 * Le disque utilisé vient de config('filesystems.media') : aujourd'hui public/ sur le
 * serveur, demain un stockage cloud, sans toucher au code ni à la base.
 */
class MediaStorage
{
    /** Dossiers où le site a le droit d'écrire et d'effacer. */
    public const DIRECTORIES = [
        'articles',
        'users/profil',
        'categories/images',
        'souscategories/images',
        'media/spotlight',
        'publicites',
        'advertisements',
    ];

    public const PLACEHOLDER = 'images/placeholder.png';

    public static function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.media', 'uploads'));
    }

    /** Vrai tant que les photos sont servies depuis public/ par ce serveur. */
    public static function isLocal(): bool
    {
        return config('filesystems.disks.' . config('filesystems.media', 'uploads') . '.driver') === 'local';
    }

    /**
     * Optimise (redimensionne, compresse) puis enregistre une image.
     *
     * @param  string  $profile  article | profile | category | raw (sans retouche)
     * @return string Chemin relatif à enregistrer en base.
     */
    public static function storeImage(UploadedFile $file, string $directory, string $profile = 'raw'): string
    {
        $directory = trim($directory, '/');
        if (! in_array($directory, self::DIRECTORIES, true)) {
            throw new \InvalidArgumentException("Dossier d'images non autorisé : {$directory}");
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = now()->format('YmdHis') . '_' . Str::random(16) . '.' . $extension;

        $optimized = $profile === 'raw' ? null : self::optimizeToTemp($file, $profile, $filename);

        try {
            $stored = self::disk()->putFileAs(
                $directory,
                $optimized ? new File($optimized) : $file,
                $filename,
                ['visibility' => 'public']
            );
        } finally {
            if ($optimized) {
                @unlink($optimized);
            }
        }

        if (! $stored) {
            throw new \RuntimeException("Impossible d'enregistrer l'image dans {$directory}.");
        }

        return $directory . '/' . $filename;
    }

    /** Efface un fichier, seulement s'il est dans un des dossiers d'images du site. */
    public static function delete(?string $path): bool
    {
        $path = self::normalize($path);
        if ($path === null || ! self::isManaged($path)) {
            return false;
        }

        try {
            return self::disk()->delete($path);
        } catch (\Throwable $e) {
            Log::warning("Impossible d'effacer l'image {$path} : " . $e->getMessage());

            return false;
        }
    }

    public static function exists(?string $path): bool
    {
        $path = self::normalize($path);

        return $path !== null && ! Str::startsWith($path, ['http://', 'https://']) && self::disk()->exists($path);
    }

    /**
     * URL publique d'une image, ou du visuel par défaut si elle manque.
     * En local on vérifie que le fichier existe (comportement historique du site) ;
     * sur un stockage cloud, on ne fait pas d'appel réseau par image.
     */
    public static function url(?string $path, ?string $fallback = self::PLACEHOLDER): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return $fallback === null ? null : asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (self::isLocal()) {
            return self::disk()->exists($path) ? asset($path) : ($fallback === null ? null : asset($fallback));
        }

        return self::disk()->url($path);
    }

    /**
     * Premier chemin existant parmi plusieurs (anciens emplacements de fichiers).
     * Sur un stockage cloud, le premier candidat est pris tel quel.
     *
     * @param  list<string>  $candidates
     */
    public static function firstExisting(array $candidates): ?string
    {
        $candidates = array_values(array_filter(array_map([self::class, 'normalize'], $candidates)));
        if ($candidates === []) {
            return null;
        }

        if (! self::isLocal()) {
            return $candidates[0];
        }

        foreach (array_unique($candidates) as $candidate) {
            if (self::disk()->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /** URL qui pointe vers le stockage cloud (et non vers ce site). */
    public static function isRemoteUrl(?string $url): bool
    {
        if (self::isLocal() || ! $url || ! Str::startsWith($url, ['http://', 'https://'])) {
            return false;
        }

        $base = rtrim((string) self::disk()->url(''), '/');

        return $base !== '' && Str::startsWith($url, $base);
    }

    /** @return list<string> Chemins relatifs des fichiers d'un dossier d'images. */
    public static function files(string $directory): array
    {
        return self::disk()->files(trim($directory, '/'));
    }

    public static function lastModified(string $path): int
    {
        return self::disk()->lastModified($path);
    }

    public static function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : ltrim($path, '/');
    }

    private static function isManaged(string $path): bool
    {
        if (str_contains($path, '..') || Str::startsWith($path, ['http://', 'https://'])) {
            return false;
        }

        foreach (self::DIRECTORIES as $directory) {
            if (str_starts_with($path, $directory . '/')) {
                return true;
            }
        }

        return false;
    }

    private static function optimizeToTemp(UploadedFile $file, string $profile, string $filename): ?string
    {
        $tempDir = storage_path('app/tmp-images');
        if (! is_dir($tempDir) && ! @mkdir($tempDir, 0755, true) && ! is_dir($tempDir)) {
            return null;
        }

        try {
            $optimizer = new ImageOptimizer();
            $ok = match ($profile) {
                'article' => $optimizer->optimizeArticleImage($file, $tempDir, $filename),
                'profile' => $optimizer->optimizeProfileImage($file, $tempDir, $filename),
                'category' => $optimizer->optimizeCategoryImage($file, $tempDir, $filename),
                default => false,
            };
        } catch (\Throwable $e) {
            // GD absent ou image illisible : on garde le fichier d'origine
            Log::warning("Optimisation d'image impossible, fichier d'origine conservé : " . $e->getMessage());
            $ok = false;
        }

        $path = $tempDir . DIRECTORY_SEPARATOR . $filename;

        return $ok && is_file($path) ? $path : null;
    }
}
