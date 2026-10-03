<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\MediaStorage;
use App\Models\Categorie;
use App\Models\Article;

class SousCategorie extends Model
{
    //

    protected $fillable = ['nom', 'image', 'description', 'categorie_id'];
    protected $table = 'sous_categories'; // Nom de la table associée dans la base de données

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'sous_categorie_id');
    }

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getImageUrlAttribute()
    {
        if (! $this->image) {
            return MediaStorage::url(null);
        }

        // Les anciens chemins venaient d'un autre dossier : on garde le nom du fichier
        $path = str_starts_with($this->image, 'souscategories/images/') || str_starts_with($this->image, 'http')
            ? $this->image
            : 'souscategories/images/' . basename($this->image);

        return MediaStorage::url($path);
    }

}
