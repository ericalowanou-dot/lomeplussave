<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use App\Models\User;
use App\Models\SousCategorie;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;



class Article extends Model
{
    use HasFactory;

        
        public function usersWhoLiked()
        {
            return $this->belongsToMany(User::class, 'article_user_like', 'article_id', 'user_id');
        }
        
        public function isLikeByLoggedInUser()
        {
            // Vérifier si l'utilisateur est authentifié
            if (auth()->check()) {
                return $this->usersWhoLiked()->where('user_id', auth()->user()->id)->exists();
            }
            // Si l'utilisateur n'est pas authentifié, retourner false
            return false;
        }
        


   
       public function user()
       {
         return $this->belongsTo(User::class);
       }

       // Définir la table associée
       protected $table = 'articles';
   
       // Définir les colonnes modifiables en masse (fillable)
       protected $fillable = [
        'user_id', 
        'titre',
        'description',  
        'prix_ht', 
        'lieu',
        'sous_categorie_id', 
        'neuf', 
        'livraison', 
        'photo', 'photo1', 'photo2', 'photo3', 'photo4',
        'photo5', 'photo6',
        'status',
        'approved_at',
        'blocked_at',
        'block_reason',
        'boosted_until'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approved_at' => 'datetime',
        'blocked_at' => 'datetime',
        'boosted_until' => 'datetime',
    ];
    
       // Définir les relations
   
       // Un article appartient à une catégorie
       public function categorie()
       {
           return $this->belongsTo(Categorie::class, 'categorie_id');
       }

       public function sousCategorie()
       {
            return $this->belongsTo(SousCategorie::class, 'sous_categorie_id');
       }
   
         // Un article peut avoir plusieurs commentaires 
        public function comments()
        {
            return $this->hasMany(Comment::class)->orderBy('created_at', 'desc'); // La relation "hasMany" pour les commentaires
        }

        // Méthodes pour l'administration
        public function isPending()
        {
            return $this->status === 'pending';
        }

        public function isApproved()
        {
            return $this->status === 'approved';
        }

        public function isBlocked()
        {
            return $this->status === 'blocked';
        }

        public function isBoosted(): bool
        {
            return !is_null($this->boosted_until) && $this->boosted_until->isFuture();
        }

        public function approve()
        {
            $this->update([
                'status' => 'approved',
                'approved_at' => now(),
                'blocked_at' => null,
                'block_reason' => null,
            ]);
        }

        public function block($reason = null)
        {
            $this->update([
                'status' => 'blocked',
                'blocked_at' => now(),
                'block_reason' => $reason,
            ]);
        }

        // Scope pour récupérer seulement les articles approuvés
        public function scopeApproved($query)
        {
            return $query->where('status', 'approved');
        }

        // Scope pour récupérer seulement les articles en attente
        public function scopePending($query)
        {
            return $query->where('status', 'pending');
        }

        // Scope pour récupérer seulement les articles bloqués
        public function scopeBlocked($query)
        {
            return $query->where('status', 'blocked');
        }

        /**
         * Obtenir l'URL de l'image principale avec placeholder par défaut
         */
        public function getPhotoUrlAttribute()
        {
            $path = $this->photo;

            if (!$path) {
                return asset('images/placeholder.png');
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
            $absolutePath = public_path($normalizedPath);

            if (File::exists($absolutePath)) {
                return asset($normalizedPath);
            }

            return asset('images/placeholder.png');
        }

        /**
         * Obtenir toutes les images de l'article avec placeholders
         */
        public function getAllPhotos()
        {
            $photos = [];
            $photoFields = ['photo', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5'];
            
            foreach ($photoFields as $field) {
                if ($this->$field) {
                    $photos[] = $this->buildPhotoUrl($this->$field);
                }
            }
            
            // Si aucune photo, retourner au moins le placeholder
            if (empty($photos)) {
                $photos[] = asset('images/placeholder.png');
            }
            
            return $photos;
        }

        /**
         * Obtenir l'URL d'une photo spécifique par index (0-5)
         */
        public function getPhotoUrlByIndex($index)
        {
            $photoFields = ['photo', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5'];
            
            if (isset($photoFields[$index]) && $this->{$photoFields[$index]}) {
                return $this->buildPhotoUrl($this->{$photoFields[$index]});
            }
            
            return asset('images/placeholder.png');
        }

        protected function buildPhotoUrl($path)
        {
            if (!$path) {
                return asset('images/placeholder.png');
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
            $absolutePath = public_path($normalizedPath);

            if (File::exists($absolutePath)) {
                return asset($normalizedPath);
            }

            return asset('images/placeholder.png');
        }
}
