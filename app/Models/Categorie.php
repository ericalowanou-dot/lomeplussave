<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\MediaStorage;
use HasFactory;
use App\Models\SousCategorie;
use App\Models\Article; 


class Categorie extends Model
{
    //
     
        //  public function Sous_Categorie()
        //  {
        //      return $this->hasMany(Sous_Categorie::class, 'categorie_id');
        //  }
     
     

     // Définir la table associée
     protected $table = 'categories';
 
     // Définir les colonnes modifiables
     protected $fillable = [
         'nom',
         'description',
         'image'
     ];
 
     // Définir les relations
 
     // Une catégorie peut avoir plusieurs articles
     public function articles()
     {
         return $this->hasMany(Article::class);
     }

     public function sousCategories()
    {
        return $this->hasMany(SousCategorie::class, 'categorie_id');
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
        $path = str_starts_with($this->image, 'categories/images/') || str_starts_with($this->image, 'http')
            ? $this->image
            : 'categories/images/' . basename($this->image);

        return MediaStorage::url($path);
    }

}
