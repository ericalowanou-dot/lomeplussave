<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        if ($this->image) {
            // Si le chemin commence déjà par souscategories/images, on l'utilise tel quel
            if (str_starts_with($this->image, 'souscategories/images/')) {
                return asset($this->image);
            }
            // Sinon, on adapte les anciens chemins
            return asset('souscategories/images/' . basename($this->image));
        }
        return asset('images/placeholder.png');
    }

}
