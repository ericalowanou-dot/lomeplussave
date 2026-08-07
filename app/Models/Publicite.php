<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publicite extends Model
{
    protected $table = 'publicites';

    protected $fillable = [
        'titre',
        'image',
        'lien_url',
        'position',
        'apres_n_articles',
        'date_debut',
        'date_fin',
        'is_active',
        'ordre',
        'clics',
        'affichages',
        'notes'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'is_active' => 'boolean',
        'clics' => 'integer',
        'affichages' => 'integer',
        'ordre' => 'integer',
        'apres_n_articles' => 'integer',
    ];

    /**
     * Labels des positions d'affichage.
     */
    public static function positions(): array
    {
        return [
            'header' => 'Header (sous le titre Annonces)',
            'sidebar' => 'Sidebar (barre latérale)',
            'footer' => 'Footer (pied de page)',
            'entre_articles' => 'Section annonces (carrousel / scroll)',
            'homepage_top' => 'Page d\'accueil - Haut',
            'homepage_bottom' => 'Page d\'accueil - Bas',
            'popup' => 'Popup (fenêtre flottante)',
        ];
    }

    /**
     * Publicités actives pour la section feed (carrousel mobile / scroll desktop).
     */
    public static function activeForFeed(): \Illuminate\Support\Collection
    {
        try {
            return static::active()
                ->byPosition('entre_articles')
                ->orderBy('ordre')
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $e) {
            \Log::error('Erreur publicités feed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Une seule popup active à afficher (la plus prioritaire).
     */
    public static function activePopup(): ?self
    {
        try {
            return static::active()
                ->byPosition('popup')
                ->orderBy('ordre')
                ->orderByDesc('created_at')
                ->first();
        } catch (\Throwable $e) {
            \Log::error('Erreur publicité popup: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @deprecated Conservé pour compatibilité — préférer activeForFeed().
     */
    public static function activeSlotsEntreArticles(): \Illuminate\Support\Collection
    {
        return collect();
    }

    /**
     * Vérifier si la publicité est actuellement active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->startOfDay();

        // Si date de début définie et pas encore atteinte
        if ($this->date_debut) {
            $dateDebut = \Carbon\Carbon::parse($this->date_debut)->startOfDay();
            if ($dateDebut->isFuture()) {
                return false;
            }
        }

        // Si date de fin définie et dépassée
        if ($this->date_fin) {
            $dateFin = \Carbon\Carbon::parse($this->date_fin)->startOfDay();
            if ($dateFin->isPast()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope pour récupérer les publicités actives
     */
    public function scopeActive($query)
    {
        $now = now()->format('Y-m-d');
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('date_debut')
                  ->orWhereDate('date_debut', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('date_fin')
                  ->orWhereDate('date_fin', '>=', $now);
            });
    }

    /**
     * Scope pour récupérer par position
     */
    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Incrémenter le compteur d'affichages
     */
    public function incrementViews()
    {
        $this->increment('affichages');
    }

    /**
     * Incrémenter le compteur de clics
     */
    public function incrementClicks()
    {
        $this->increment('clics');
    }

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/placeholder.png');
        }

        $path = $this->image;

        // Préférer le dossier neutre (moins bloqué par les adblockers)
        if (str_starts_with($path, 'advertisements/')) {
            $neutral = 'media/spotlight/' . substr($path, strlen('advertisements/'));
            if (is_file(public_path($neutral))) {
                return asset($neutral);
            }
        }

        if (
            str_starts_with($path, 'media/spotlight/')
            || str_starts_with($path, 'advertisements/')
            || str_starts_with($path, 'publicites/')
        ) {
            return asset($path);
        }

        $neutral = 'media/spotlight/' . basename($path);
        if (is_file(public_path($neutral))) {
            return asset($neutral);
        }

        if (is_file(public_path('advertisements/' . basename($path)))) {
            return asset('advertisements/' . basename($path));
        }

        return asset('images/placeholder.png');
    }
}
