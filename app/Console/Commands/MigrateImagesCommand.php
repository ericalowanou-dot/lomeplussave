<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\SousCategorie;
use App\Models\User;
use App\Models\Publicite;
use Illuminate\Support\Facades\File;

class MigrateImagesCommand extends Command
{
    protected $signature = 'images:migrate';
    protected $description = 'Migrer les anciennes images vers la nouvelle structure dans public/';

    public function handle()
    {
        $this->info('Migration des images vers la nouvelle structure...');

        // Créer les dossiers nécessaires
        $directories = [
            'users/profil',
            'articles',
            'categories/images',
            'souscategories/images',
            'advertisements'
        ];

        foreach ($directories as $dir) {
            $path = public_path($dir);
            if (!file_exists($path)) {
                File::makeDirectory($path, 0755, true);
                $this->info("Dossier créé: {$dir}");
            }
        }

        // Migrer les images des articles
        $articles = Article::whereNotNull('photo')->get();
        $count = 0;
        foreach ($articles as $article) {
            $photos = ['photo', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5'];
            foreach ($photos as $photoField) {
                if ($article->$photoField) {
                    $oldPath = $article->$photoField;
                    
                    // Si le chemin commence déjà par 'articles/', on le garde
                    if (str_starts_with($oldPath, 'articles/')) {
                        continue;
                    }
                    
                    // Si c'est un chemin storage, on le cherche dans storage/app/public
                    if (str_starts_with($oldPath, 'storage/')) {
                        $storagePath = storage_path('app/public/' . str_replace('storage/', '', $oldPath));
                        if (file_exists($storagePath)) {
                            $filename = basename($oldPath);
                            $newPath = public_path('articles/' . $filename);
                            File::copy($storagePath, $newPath);
                            $article->$photoField = 'articles/' . $filename;
                            $article->save();
                            $count++;
                        }
                    }
                    // Si c'est juste un nom de fichier, on cherche dans images/articles
                    elseif (!str_contains($oldPath, '/')) {
                        $oldImagePath = public_path('images/articles/' . $oldPath);
                        if (file_exists($oldImagePath)) {
                            $newPath = public_path('articles/' . $oldPath);
                            File::copy($oldImagePath, $newPath);
                            $article->$photoField = 'articles/' . $oldPath;
                            $article->save();
                            $count++;
                        }
                    }
                }
            }
        }
        $this->info("Articles migrés: {$count} images");

        // Migrer les images des utilisateurs
        $users = User::whereNotNull('photo_profil')->get();
        $count = 0;
        foreach ($users as $user) {
            $oldPath = $user->photo_profil;
            
            if (str_starts_with($oldPath, 'users/profil/')) {
                continue;
            }
            
            $filename = basename($oldPath);
            $oldImagePath = public_path('images/profil/' . $filename);
            
            if (file_exists($oldImagePath)) {
                $newPath = public_path('users/profil/' . $filename);
                File::copy($oldImagePath, $newPath);
                $user->photo_profil = 'users/profil/' . $filename;
                $user->save();
                $count++;
            }
        }
        $this->info("Utilisateurs migrés: {$count} images");

        // Migrer les images des catégories
        $categories = Categorie::whereNotNull('image')->get();
        $count = 0;
        foreach ($categories as $categorie) {
            $oldPath = $categorie->image;
            
            if (str_starts_with($oldPath, 'categories/images/')) {
                continue;
            }
            
            $filename = basename($oldPath);
            $oldImagePath = public_path('images/' . $filename);
            
            if (file_exists($oldImagePath)) {
                $newPath = public_path('categories/images/' . $filename);
                File::copy($oldImagePath, $newPath);
                $categorie->image = 'categories/images/' . $filename;
                $categorie->save();
                $count++;
            }
        }
        $this->info("Catégories migrées: {$count} images");

        // Migrer les images des sous-catégories
        $sousCategories = SousCategorie::whereNotNull('image')->get();
        $count = 0;
        foreach ($sousCategories as $sousCategorie) {
            $oldPath = $sousCategorie->image;
            
            if (str_starts_with($oldPath, 'souscategories/images/')) {
                continue;
            }
            
            $filename = basename($oldPath);
            $oldImagePath = public_path('images/souscategories/' . $filename);
            
            if (file_exists($oldImagePath)) {
                $newPath = public_path('souscategories/images/' . $filename);
                File::copy($oldImagePath, $newPath);
                $sousCategorie->image = 'souscategories/images/' . $filename;
                $sousCategorie->save();
                $count++;
            }
        }
        $this->info("Sous-catégories migrées: {$count} images");

        // Migrer les publicités
        $publicites = Publicite::whereNotNull('image')->get();
        $count = 0;
        foreach ($publicites as $publicite) {
            $oldPath = $publicite->image;
            
            if (str_starts_with($oldPath, 'advertisements/') || str_starts_with($oldPath, 'publicites/')) {
                // Si c'est déjà publicites/, on le migre vers advertisements/
                if (str_starts_with($oldPath, 'publicites/')) {
                    $filename = basename($oldPath);
                    $oldImagePath = public_path($oldPath);
                    
                    if (file_exists($oldImagePath)) {
                        $newPath = public_path('advertisements/' . $filename);
                        File::copy($oldImagePath, $newPath);
                        $publicite->image = 'advertisements/' . $filename;
                        $publicite->save();
                        $count++;
                    }
                }
                continue;
            }
            
            $filename = basename($oldPath);
            $oldImagePath = public_path('publicites/' . $filename);
            
            if (file_exists($oldImagePath)) {
                $newPath = public_path('advertisements/' . $filename);
                File::copy($oldImagePath, $newPath);
                $publicite->image = 'advertisements/' . $filename;
                $publicite->save();
                $count++;
            }
        }
        $this->info("Publicités migrées: {$count} images");

        $this->info('Migration terminée avec succès !');
    }
}
