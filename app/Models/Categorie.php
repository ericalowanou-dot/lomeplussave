<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        if ($this->image) {
            // Si le chemin commence déjà par categories/images, on l'utilise tel quel
            if (str_starts_with($this->image, 'categories/images/')) {
                return asset($this->image);
            }
            // Sinon, on adapte les anciens chemins
            return asset('categories/images/' . basename($this->image));
        }
        return asset('images/placeholder.png');
    }

}
