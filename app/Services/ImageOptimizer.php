<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageOptimizer
{
    private ImageManager $manager;
    
    // Tailles maximales recommandées
    private const MAX_WIDTH_ARTICLE = 1280;
    private const MAX_HEIGHT_ARTICLE = 1280;
    private const MAX_WIDTH_PROFILE = 800;
    private const MAX_HEIGHT_PROFILE = 800;
    private const MAX_WIDTH_CATEGORY = 512;
    private const MAX_HEIGHT_CATEGORY = 512;
    
    // Qualité de compression (0-100)
    // private const JPEG_QUALITY = 85;
    // private const PNG_QUALITY = 90;
    // private const WEBP_QUALITY = 85;

    private const JPEG_QUALITY = 35;
    private const PNG_QUALITY = 35;
    private const WEBP_QUALITY = 35;


    public function __construct()
    {
        // Vérifier que GD est disponible avant d'instancier le driver
        if (!extension_loaded('gd')) {
            throw new \RuntimeException(
                'L\'extension GD n\'est pas disponible. Veuillez activer l\'extension GD dans votre configuration PHP.'
            );
        }
        
        try {
            $this->manager = new ImageManager(new Driver());
        } catch (\Exception $e) {
            Log::error('Impossible d\'initialiser ImageManager avec le driver GD', [
                'error' => $e->getMessage(),
                'gd_loaded' => extension_loaded('gd'),
                'gd_info' => extension_loaded('gd') ? gd_info() : null
            ]);
            throw new \RuntimeException(
                'Impossible d\'initialiser le gestionnaire d\'images. Veuillez vérifier que l\'extension GD est correctement configurée.'
            );
        }
    }

    /** Au-delà, on refuse l'image même si la mémoire PHP est illimitée (~100 mégapixels). */
    private const MAX_PIXELS = 100_000_000;

    /**
     * Vérifie, avant de la décoder, qu'une image tient dans la mémoire disponible.
     * GD décompresse toute l'image en mémoire (~5 octets par pixel) : une photo de
     * 108 mégapixels demande plus de 500 Mo et ferait planter la requête (erreur 500).
     *
     * @return string|null Message à afficher à l'utilisateur, ou null si l'image est acceptable.
     */
    public static function memoryProblem(UploadedFile $file): ?string
    {
        $size = @getimagesize($file->getRealPath());
        if (! $size || empty($size[0]) || empty($size[1])) {
            return null; // format non lisible ici (SVG, HEIC…) : laissé au traitement normal
        }

        [$width, $height] = $size;
        $pixels = $width * $height;
        $needed = $pixels * 5;

        $limit = self::memoryLimitBytes();
        $available = $limit === null ? PHP_INT_MAX : $limit - memory_get_usage(true) - 32 * 1024 * 1024;

        if ($pixels <= self::MAX_PIXELS && $needed <= $available) {
            return null;
        }

        return sprintf(
            'La photo « %s » est trop grande (%d × %d pixels, %s mégapixels). Réduisez sa taille ou faites une capture d\'écran de la photo, puis réessayez.',
            $file->getClientOriginalName(),
            $width,
            $height,
            number_format($pixels / 1_000_000, 0, ',', ' ')
        );
    }

    private static function memoryLimitBytes(): ?int
    {
        $value = trim((string) ini_get('memory_limit'));
        if ($value === '' || $value === '-1') {
            return null;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Optimise une image d'article
     */
    public function optimizeArticleImage(UploadedFile $file, string $destinationPath, string $filename): bool
    {
        try {
            // Vérifier que le fichier est valide
            if (!$file->isValid()) {
                Log::error('Fichier uploadé invalide', [
                    'error_code' => $file->getError(),
                    'filename' => $file->getClientOriginalName()
                ]);
                return false;
            }
            
            // Vérifier que le dossier de destination existe et est accessible
            if (!file_exists($destinationPath)) {
                if (!mkdir($destinationPath, 0755, true)) {
                    Log::error('Impossible de créer le dossier de destination', ['path' => $destinationPath]);
                    return false;
                }
            }
            
            if (!is_writable($destinationPath)) {
                Log::error('Le dossier de destination n\'est pas accessible en écriture', [
                    'path' => $destinationPath,
                    'permissions' => substr(sprintf('%o', fileperms($destinationPath)), -4)
                ]);
                return false;
            }
            
            // Vérifier si c'est un SVG (pas de compression pour SVG)
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'svg') {
                $fullPath = $destinationPath . '/' . $filename;
                $file->move($destinationPath, $filename);
                return file_exists($fullPath);
            }
            
            // Vérifier que le fichier temporaire existe
            $tempPath = $file->getRealPath();
            if (!$tempPath || !file_exists($tempPath)) {
                Log::error('Le fichier temporaire n\'existe pas', [
                    'temp_path' => $tempPath,
                    'original_name' => $file->getClientOriginalName()
                ]);
                return false;
            }
            
            // Lire l'image avec Intervention Image
            try {
                $image = $this->manager->read($tempPath);
            } catch (\Exception $readException) {
                Log::error('Impossible de lire l\'image avec Intervention Image', [
                    'error' => $readException->getMessage(),
                    'file' => $readException->getFile(),
                    'line' => $readException->getLine(),
                    'temp_path' => $tempPath,
                    'extension' => $extension
                ]);
                return false;
            }
            
            // Redimensionner si nécessaire (en gardant les proportions)
            $image = $this->resizeImage($image, self::MAX_WIDTH_ARTICLE, self::MAX_HEIGHT_ARTICLE);
            
            // Optimiser selon le format
            $fullPath = $destinationPath . '/' . $filename;
            
            return $this->saveOptimizedImage($image, $fullPath, $extension);
            
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'optimisation de l\'image d\'article', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName() ?? 'unknown',
                'extension' => $file->getClientOriginalExtension() ?? 'unknown'
            ]);
            return false;
        }
    }

    /**
     * Optimise une image de profil utilisateur
     */
    public function optimizeProfileImage(UploadedFile $file, string $destinationPath, string $filename): bool
    {
        try {
            // Vérifier si c'est un SVG (pas de compression pour SVG)
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'svg') {
                $fullPath = $destinationPath . '/' . $filename;
                $directory = dirname($fullPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $file->move($destinationPath, $filename);
                return file_exists($fullPath);
            }
            
            $image = $this->manager->read($file->getRealPath());
            
            // Redimensionner si nécessaire
            $image = $this->resizeImage($image, self::MAX_WIDTH_PROFILE, self::MAX_HEIGHT_PROFILE);
            
            // Optimiser selon le format
            $fullPath = $destinationPath . '/' . $filename;
            
            return $this->saveOptimizedImage($image, $fullPath, $extension);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de l\'image de profil: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimise une image de catégorie
     */
    public function optimizeCategoryImage(UploadedFile $file, string $destinationPath, string $filename): bool
    {
        try {
            // Vérifier si c'est un SVG (pas de compression pour SVG)
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'svg') {
                $fullPath = $destinationPath . '/' . $filename;
                $directory = dirname($fullPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $file->move($destinationPath, $filename);
                return file_exists($fullPath);
            }
            
            $image = $this->manager->read($file->getRealPath());
            
            // Redimensionner si nécessaire
            $image = $this->resizeImage($image, self::MAX_WIDTH_CATEGORY, self::MAX_HEIGHT_CATEGORY);
            
            // Optimiser selon le format
            $fullPath = $destinationPath . '/' . $filename;
            
            return $this->saveOptimizedImage($image, $fullPath, $extension);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de l\'image de catégorie: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Redimensionne une image en gardant les proportions
     */
    private function resizeImage($image, int $maxWidth, int $maxHeight)
    {
        $width = $image->width();
        $height = $image->height();
        
        // Si l'image est plus petite que les limites, ne pas redimensionner
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }
        
        // Calculer les nouvelles dimensions en gardant les proportions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);
        
        // Utiliser scaleDown pour redimensionner seulement si plus grand
        return $image->scaleDown($newWidth, $newHeight);
    }

    /**
     * Sauvegarde l'image optimisée selon son format
     */
    private function saveOptimizedImage($image, string $fullPath, string $extension): bool
    {
        try {
            // Créer le dossier s'il n'existe pas
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image->toJpeg(self::JPEG_QUALITY)->save($fullPath);
                    break;
                    
                case 'png':
                    // PNG avec compression
                    $image->toPng()->save($fullPath);
                    break;
                    
                case 'webp':
                    $image->toWebp(self::WEBP_QUALITY)->save($fullPath);
                    break;
                    
                case 'gif':
                    $image->toGif()->save($fullPath);
                    break;
                    
                case 'svg':
                    // SVG devrait être géré avant d'arriver ici
                    // Si on arrive ici, essayer de sauvegarder tel quel
                    $image->save($fullPath);
                    break;
                    
                default:
                    // Pour les autres formats, essayer de sauvegarder en JPEG
                    try {
                        $image->toJpeg(self::JPEG_QUALITY)->save($fullPath);
                    } catch (\Exception $e) {
                        // Si échec, essayer de sauvegarder tel quel
                        $image->save($fullPath);
                    }
                    break;
            }
            
            return file_exists($fullPath);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde de l\'image optimisée: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimise une image depuis un chemin de fichier existant
     */
    public function optimizeExistingImage(string $filePath, int $maxWidth = 1920, int $maxHeight = 1920, int $quality = 85): bool
    {
        try {
            if (!file_exists($filePath)) {
                return false;
            }
            
            $image = $this->manager->read($filePath);
            $image = $this->resizeImage($image, $maxWidth, $maxHeight);
            
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            return $this->saveOptimizedImage($image, $filePath, $extension);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de l\'image existante: ' . $e->getMessage());
            return false;
        }
    }
}
