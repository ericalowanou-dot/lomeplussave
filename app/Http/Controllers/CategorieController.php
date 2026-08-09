<?php

namespace App\Http\Controllers;
use App\Models\Categorie;
use App\Models\Article;
use App\Models\User;
use App\Models\SousCategorie;

use Illuminate\Http\Request;

class CategorieController extends Controller
{
    //
     //
     
    public function index()
    {
       
         dd($categories); //debugging
    


         return view('includes.navigation', compact('categories'));
    
    
    
    }
 
    
    public function listes_categries(){
        $categories = Categorie::all();
        return view('pages.categories.liste', compact('categories'));
    }
 

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|unique:categories|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:5120',
            'description' => 'nullable|string',
        ]);

   


        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Déplacement vers le dossier public/categories/images
            $destinationPath = public_path('categories/images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Optimiser et compresser l'image avant de la sauvegarder
            $imageOptimizer = new \App\Services\ImageOptimizer();
            if (!$imageOptimizer->optimizeCategoryImage($image, $destinationPath, $imageName)) {
                // Si l'optimisation échoue, sauvegarder l'image originale
                $image->move($destinationPath, $imageName);
            }
            
            $imageName = 'categories/images/' . $imageName; // Chemin relatif complet
        }

        Categorie::create([
            'nom' => $request->nom,
            'image' => $imageName,
            'description' => $request->description,
        ]);

        // Invalider le cache des catégories
        \Cache::forget('categories_with_souscategories');
        // Note: Le cache des sous-catégories sera créé à la demande

        return redirect()->route('categories-liste')->with('success', 'Catégorie ajoutée avec succès');
    }

 
    public function destroy($id){
        try {
            $categorie = Categorie::findOrFail($id);
            
            // Vérifier si la catégorie a des sous-catégories ou articles
            $hasSousCategories = $categorie->sousCategories()->exists();
            $hasArticles = Article::whereHas('sousCategorie', function($q) use ($categorie) {
                $q->where('categorie_id', $categorie->id);
            })->exists();
            
            if ($hasSousCategories || $hasArticles) {
                $message = 'Impossible de supprimer cette catégorie.';
                $solutions = [];
                
                if ($hasSousCategories) {
                    $message .= ' Elle contient des sous-catégories.';
                    $solutions[] = 'Supprimez d\'abord toutes les sous-catégories associées';
                }
                
                if ($hasArticles) {
                    $message .= ' Elle contient des articles.';
                    $solutions[] = 'Supprimez ou déplacez d\'abord tous les articles associés';
                }
                
                $solutions[] = 'Contactez l\'administrateur si vous avez besoin d\'aide';
                
                if (request()->routeIs('admin.*')) {
                    return redirect()->route('admin.categories.index')
                        ->with('error', $message)
                        ->with('error_solutions', $solutions);
                }
                return redirect()->route('categories-liste')
                    ->with('error', $message)
                    ->with('error_solutions', $solutions);
            }
            
            $categorie->delete();
            
            // Invalider le cache
            \Cache::forget('categories_with_souscategories');
            
            // Redirection selon le contexte (admin ou public)
            if (request()->routeIs('admin.*')) {
                return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
            }
            return redirect()->route('categories-liste')->with('success', 'Catégorie supprimée.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
            $errorMessage = 'Impossible de supprimer la catégorie pour le moment.';
            $solutions = [
                'Réessayez dans quelques instants',
                'Vérifiez que la catégorie n\'est pas utilisée par des articles ou sous-catégories',
                'Si le problème persiste, contactez l\'administrateur'
            ];
            
            if (request()->routeIs('admin.*')) {
                return redirect()->route('admin.categories.index')
                    ->with('error', $errorMessage)
                    ->with('error_solutions', $solutions);
            }
            return redirect()->route('categories-liste')
                ->with('error', $errorMessage)
                ->with('error_solutions', $solutions);
        }
    }

   
     


    public function edit(Categorie $categorie){
        return view('pages.categories.edit', compact('categories'));
    }



    public function update(Request $request, Categorie $categorie)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:5120',
            ], [
                'nom.required' => 'Le nom de la catégorie est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'photo.image' => 'Le fichier doit être une image.',
                'photo.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif, svg, webp ou bmp.',
                'photo.max' => 'L\'image ne doit pas dépasser 5 Mo.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Vérifiez que le nom est rempli et ne dépasse pas 255 caractères',
                    'Assurez-vous que l\'image est au bon format',
                    'Vérifiez que l\'image ne dépasse pas 5 Mo'
                ])
                ->withInput();
        }

        try {
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne image si elle existe
                if ($categorie->image && file_exists(public_path($categorie->image))) {
                    unlink(public_path($categorie->image));
                }
                
                $nomImage = time() . '_' . uniqid() . '.' . $request->photo->extension();
                $destinationPath = public_path('categories/images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                // Optimiser et compresser l'image avant de la sauvegarder
                $imageOptimizer = new \App\Services\ImageOptimizer();
                if (!$imageOptimizer->optimizeCategoryImage($request->photo, $destinationPath, $nomImage)) {
                    // Si l'optimisation échoue, sauvegarder l'image originale
                    $request->photo->move($destinationPath, $nomImage);
                }
                
                $categorie->image = 'categories/images/' . $nomImage;
            }

            $categorie->nom = $request->nom;
            $categorie->save();

            // Invalider le cache des catégories
            \Cache::forget('categories_with_souscategories');

            return redirect()->route('categories.create')->with('success', 'Catégorie mise à jour');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour la catégorie pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants'
                ])
                ->withInput();
        }
    }

    // Admin: liste avec gestion via modals
    public function adminIndex()
    {
        $categories = Categorie::orderBy('created_at', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    // Admin: update via modals (image facultative)
    public function adminUpdate(Request $request, Categorie $categorie)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:5120',
            ], [
                'nom.required' => 'Le nom de la catégorie est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif, svg, webp ou bmp.',
                'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Vérifiez que le nom est rempli et ne dépasse pas 255 caractères',
                    'Assurez-vous que l\'image est au bon format',
                    'Vérifiez que l\'image ne dépasse pas 5 Mo'
                ])
                ->withInput();
        }

        try {
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($categorie->image && file_exists(public_path($categorie->image))) {
                    unlink(public_path($categorie->image));
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('categories/images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                // Optimiser et compresser l'image avant de la sauvegarder
                $imageOptimizer = new \App\Services\ImageOptimizer();
                if (!$imageOptimizer->optimizeCategoryImage($image, $destinationPath, $imageName)) {
                    // Si l'optimisation échoue, sauvegarder l'image originale
                    $image->move($destinationPath, $imageName);
                }
                
                $categorie->image = 'categories/images/' . $imageName;
            }

            $categorie->nom = $request->nom;
            $categorie->save();

            // Invalider le cache des catégories
            \Cache::forget('categories_with_souscategories');

            return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour la catégorie pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants'
                ])
                ->withInput();
        }
    }




















     

    // public function update(Request $request, Category $category)
    // {
    //     $data = $request->validate([
    //         'nom' => 'required|string|max:255',
    //         'image' => 'nullable|image|max:2048',
    //     ]);

    //     if ($request->hasFile('photo')) {
    //         if ($category->photo) {
    //             Storage::disk('public')->delete($category->photo);
    //         }
    //         $data['photo'] = $request->file('photo')->store('categories', 'public');
    //     }

    //     $category->update($data);

    //     return redirect()->route('categories.create')->with('status', 'Catégorie modifiée !');
    // }









    public function getCategories()
    {
        $categories = Categorie::with('sousCategories')->get();
        return response()->json($categories);
    }


    
    public function getSousCategories($id)
    {
        $sousCategories = SousCategorie::where('categorie_id', $id)->get();

        if ($sousCategories->isEmpty()) {
            return response()->json([], 200); // Retourne un tableau vide si aucune sous-catégorie
        }
        
        return response()->json($sousCategories);
    }




    // lorsqu'on scroll sur les categories
  
public function getSubcategories($id)
{
    // Utiliser le cache pour éviter des requêtes répétées
    $cacheKey = "subcategories_for_category_{$id}";

    $sousCategories = cache()->remember($cacheKey, 60, function () use ($id) {
        return SousCategorie::where('categorie_id', $id)
            ->select('id', 'nom') // Sélectionner uniquement les colonnes nécessaires
            ->get();
    });

    // Retourner les sous-catégories en JSON
    return response()->json($sousCategories);
}

    public function articleParSubcategorie(Request $request)
    {
        $categories = Categorie::all();

        // demarrer les requetes des articles
        $articles = Article::query();

        $selectedSubcategory = null;

        // 🔹 Filtrer par sous-catégorie (prioritaire) ou par catégorie
        if ($request->filled('subcategory')) {
            $articles->where('sous_categorie_id', $request->subcategory);
            $selectedSubcategory = SousCategorie::with('categorie')->find($request->subcategory);
        } elseif ($request->filled('categorie')) {
            $sousCategoriesIds = SousCategorie::where('categorie_id', $request->categorie)->pluck('id');
            $articles->whereIn('sous_categorie_id', $sousCategoriesIds);
        }

        $articles->where('status', 'approved');

        $articles = $articles
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom'])
            ->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > NOW()) DESC')
            ->orderBy('created_at', 'desc')
            ->paginate(120)
            ->withQueryString();

        // Retourner la vue avec les articles
        return view('pages.articles_par_subcategorie', [
            'articles' => $articles,
            'categories' => $categories,
            'selectedSubcategory' => $selectedSubcategory,
        ]);


    }

}
