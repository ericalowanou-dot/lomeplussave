<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SousCategorie;
use App\Models\Categorie;
use App\Models\Article;
use App\Models\User;

class SousCategorieController extends Controller
{

    public function index()
    {
        $sousCategories = SousCategorie::with('categorie')->get();
        $categories = Categorie::all();
        return view('admin.souscategories.index', compact('sousCategories', 'categories'));
    }








    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:5120',
            ], [
                'nom.required' => 'Le nom de la sous-catégorie est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'categorie_id.required' => 'Veuillez sélectionner une catégorie.',
                'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif, svg, webp ou bmp.',
                'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Vérifiez que le nom est rempli et ne dépasse pas 255 caractères',
                    'Sélectionnez une catégorie valide',
                    'Assurez-vous que l\'image est au bon format et ne dépasse pas 5 Mo'
                ])
                ->withInput();
        }

        try {
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
                $destinationPath = public_path('souscategories/images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                // Optimiser et compresser l'image avant de la sauvegarder
                $imageOptimizer = new \App\Services\ImageOptimizer();
                if (!$imageOptimizer->optimizeCategoryImage($request->image, $destinationPath, $imageName)) {
                    // Si l'optimisation échoue, sauvegarder l'image originale
                    $request->image->move($destinationPath, $imageName);
                }
                
                $imageName = 'souscategories/images/' . $imageName; // Chemin relatif complet
            }

            SousCategorie::create([
                'nom' => $request->nom,
                'categorie_id' => $request->categorie_id,
                'image' => $imageName,
            ]);

            // Invalider le cache des catégories et sous-catégories
            \Cache::forget('categories_with_souscategories');
            \Cache::forget("souscategories_categorie_{$request->categorie_id}");

            return redirect()->back()->with('success', 'Sous-catégorie ajoutée.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la sous-catégorie: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de créer la sous-catégorie pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Assurez-vous que l\'image ne dépasse pas 5 Mo',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants'
                ])
                ->withInput();
        }
    }












    public function edit(SousCategorie $sousCategorie)
    {
        $categories = Categorie::all();
        return view('admin.souscategories.edit', compact('sousCategorie', 'categories'));
    }









    public function update(Request $request, SousCategorie $sousCategorie)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:5120',
            ], [
                'nom.required' => 'Le nom de la sous-catégorie est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'categorie_id.required' => 'Veuillez sélectionner une catégorie.',
                'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif, svg, webp ou bmp.',
                'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Vérifiez que le nom est rempli et ne dépasse pas 255 caractères',
                    'Sélectionnez une catégorie valide',
                    'Assurez-vous que l\'image est au bon format et ne dépasse pas 5 Mo'
                ])
                ->withInput();
        }

        try {
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($sousCategorie->image && file_exists(public_path($sousCategorie->image))) {
                    unlink(public_path($sousCategorie->image));
                }
                
                $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
                $destinationPath = public_path('souscategories/images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $request->image->move($destinationPath, $imageName);
                $sousCategorie->image = 'souscategories/images/' . $imageName;
            }

            $sousCategorie->nom = $request->nom;
            $oldCategorieId = $sousCategorie->getOriginal('categorie_id');
            $sousCategorie->categorie_id = $request->categorie_id;

            $sousCategorie->save();

            // Invalider le cache des catégories et sous-catégories
            \Cache::forget('categories_with_souscategories');
            \Cache::forget("souscategories_categorie_{$oldCategorieId}");
            \Cache::forget("souscategories_categorie_{$request->categorie_id}");

            // Redirection selon le contexte (admin ou public)
            if (request()->routeIs('admin.*')) {
                return redirect()->route('admin.souscategories.index')->with('success', 'Sous-catégorie mise à jour.');
            }
            return redirect()->route('souscategories.index')->with('success', 'Sous-catégorie mise à jour.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la sous-catégorie: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour la sous-catégorie pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants'
                ])
                ->withInput();
        }
    }







    

    public function destroy(SousCategorie $sousCategorie)
    {
        try {
            // Vérifier si la sous-catégorie a des articles
            $hasArticles = Article::where('sous_categorie_id', $sousCategorie->id)->exists();
            
            if ($hasArticles) {
                $errorMessage = 'Impossible de supprimer cette sous-catégorie. Elle contient des articles.';
                $solutions = [
                    'Supprimez ou déplacez d\'abord tous les articles associés vers une autre sous-catégorie',
                    'Contactez l\'administrateur si vous avez besoin d\'aide'
                ];
                
                if (request()->routeIs('admin.*')) {
                    return redirect()->route('admin.souscategories.index')
                        ->with('error', $errorMessage)
                        ->with('error_solutions', $solutions);
                }
                return redirect()->route('souscategories.index')
                    ->with('error', $errorMessage)
                    ->with('error_solutions', $solutions);
            }
            
            $categorieId = $sousCategorie->categorie_id;
            $sousCategorie->delete();
            
            // Invalider le cache des catégories et sous-catégories
            \Cache::forget('categories_with_souscategories');
            \Cache::forget("souscategories_categorie_{$categorieId}");
            
            // Redirection selon le contexte (admin ou public)
            if (request()->routeIs('admin.*')) {
                return redirect()->route('admin.souscategories.index')->with('success', 'Sous-catégorie supprimée.');
            }
            return redirect()->route('souscategories.index')->with('success', 'Sous-catégorie supprimée.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la sous-catégorie: ' . $e->getMessage());
            $errorMessage = 'Impossible de supprimer la sous-catégorie pour le moment.';
            $solutions = [
                'Réessayez dans quelques instants',
                'Vérifiez que la sous-catégorie n\'est pas utilisée par des articles',
                'Si le problème persiste, contactez l\'administrateur'
            ];
            
            if (request()->routeIs('admin.*')) {
                return redirect()->route('admin.souscategories.index')
                    ->with('error', $errorMessage)
                    ->with('error_solutions', $solutions);
            }
            return redirect()->route('souscategories.index')
                ->with('error', $errorMessage)
                ->with('error_solutions', $solutions);
        }
    }

  

}
