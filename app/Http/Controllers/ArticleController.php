<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Article;

use App\Models\User;

use App\Models\Categorie;

use App\Models\SousCategorie;

use App\Events\ArticlePending;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Str;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; 







class ArticleController extends Controller

{



    // public function liveSearch(Request $request)

    // {

    //     $q = $request->input('q');



    //     // Pas de validation stricte ici pour permettre la recherche immédiate

    //     $articles = Article::where('titre', 'like', "%$q%")

    //         ->orWhere('description', 'like', "%$q%")

    //         ->orWhereHas('user', function ($query) use ($q) {

    //             $query->where('name', 'like', "%$q%");

    //         })

    //         ->orWhereHas('sousCategorie', function ($query) use ($q) {

    //             $query->where('nom', 'like', "%$q%")

    //                 ->orWhereHas('categorie', function ($query) use ($q) {

    //                     $query->where('nom', 'like', "%$q%");

    //                 });

    //         })

    //         ->take(20) // limiter pour plus de vitesse

    //         ->get();



    //     // Retourner la vue partielle des articles

    //     return view('partials.articles-list', compact('articles'))->render();

    // }





    public function search(Request $request){

        // Validation

        $request->validate([

            'q' => 'required|min:3|string|max:255'

        ]);



        $q = $request->input('q');



        // Recherche optimisée avec eager loading

        $articles = Article::where('status', 'approved') // Seulement les articles approuvés

            ->where(function($query) use ($q) {

                // Recherche dans le titre

                $query->where('titre', 'like', "%$q%")

                      // Recherche dans la description

                      ->orWhere('description', 'like', "%$q%")

                      // Recherche dans le lieu

                      ->orWhere('lieu', 'like', "%$q%")

                      // Recherche dans le nom de l'utilisateur (optimisé avec join)

                      ->orWhereHas('user', function ($userQuery) use ($q) {

                          $userQuery->where('name', 'like', "%$q%")

                                    ->orWhere('email', 'like', "%$q%")

                                    ->orWhere('ville', 'like', "%$q%");

                      })

                      // Recherche dans le nom de la sous-catégorie

                      ->orWhereHas('sousCategorie', function ($subQuery) use ($q) {

                          $subQuery->where('nom', 'like', "%$q%")

                                   // Recherche dans le nom de la catégorie parente

                                   ->orWhereHas('categorie', function ($catQuery) use ($q) {

                                       $catQuery->where('nom', 'like', "%$q%");

                                   });

                      });

            })

            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')

            ->withLikeCounts(auth()->id())

            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id'])

            ->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > NOW()) DESC')

            ->orderBy('created_at', 'desc')

            ->paginate(40);



        // Récupérer les catégories pour la navigation (avec cache)

        $categories = \Cache::remember('categories_with_souscategories', 3600, function () {

            return \App\Models\Categorie::with('sousCategories')->get();

        });



        return view('products.search', compact('articles', 'q', 'categories'));

    }



  

    public function index(Request $request){

        // Récupérer toutes les catégories avec leurs sous-catégories (avec cache)

        $categories = \Cache::remember('categories_with_souscategories', 3600, function () {

            return Categorie::with('sousCategories')->get();

        });

      

        $articlesQuery = Article::query();



         // ð¹ Filtrer par sous-catégorie spécifique (prioritaire sur catégorie)

        if ($request->filled('sous_categorie')) {

            $articlesQuery->where('sous_categorie_id', $request->sous_categorie);

        }

        // ð¹ Filtrer par catégorie (via sous-catégorie) - avec cache

        elseif ($request->filled('categorie')) {

            $sousCategoriesIds = \Cache::remember("souscategories_categorie_{$request->categorie}", 3600, function () use ($request) {

                return SousCategorie::where('categorie_id', $request->categorie)->pluck('id');

            });

            $articlesQuery->whereIn('sous_categorie_id', $sousCategoriesIds);

        }



        // ð¹ Filtrer par prix minimum

        if ($request->filled('prix_min')) {

            $articlesQuery->where('prix_ht', '>=', $request->prix_min);

        }



        // ð¹ Filtrer par prix maximum

        if ($request->filled('prix_max')) {

            $articlesQuery->where('prix_ht', '<=', $request->prix_max);

        }



        // ð¹ Filtrer par ville (lieu de l'article)

        if ($request->filled('ville')) {

            $articlesQuery->where('lieu', $request->ville);

        }



        // ð¹ Filtrer par état (neuf / occasion)

        if ($request->filled('etat') && in_array($request->etat, ['neuf', 'occasion'])) {

            $articlesQuery->where('neuf', $request->etat === 'neuf');

        }



        // ð¹ Produits Pro uniquement

        if ($request->boolean('pro_only')) {

            $articlesQuery->whereNotNull('boosted_until')

                ->where('boosted_until', '>', now());

        }



        // ð¹ Livraison disponible

        if ($request->boolean('livraison_only')) {

            $articlesQuery->where('livraison', true);

        }



        // N'afficher que les articles approuvés sur le site public

        $articlesQuery->where('status', 'approved');



        // ð¹ Tri

        $orderBy = $request->get('order_by', 'recent');



        switch ($orderBy) {

            case 'prix_asc':

                $articlesQuery->orderBy('prix_ht', 'asc')

                    ->orderBy('created_at', 'desc');

                break;

            case 'prix_desc':

                $articlesQuery->orderBy('prix_ht', 'desc')

                    ->orderBy('created_at', 'desc');

                break;

            case 'pro':

                $articlesQuery->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > NOW()) DESC')

                    ->orderBy('created_at', 'desc');

                break;

            case 'recent':

            default:

                $articlesQuery->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > NOW()) DESC')

                    ->orderBy('created_at', 'desc');

                break;

        }



        // ð¹ Nombre d'articles par page (12, 24, 48, 96)

        $perPage = $request->get('per_page', 40);

        $allowedPerPage = [12, 24, 40, 48, 80, 96];

        if (!in_array((int)$perPage, $allowedPerPage)) {

            $perPage = 40;

        }



        $articles = $articlesQuery

            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')

            ->withLikeCounts(auth()->id())

            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id'])

            ->paginate($perPage)

            ->appends($request->query());



        if ($request->ajax()) {

            return response()->json([

                'list' => view('partials.articles-list', ['articles' => $articles])->render(),

                'pagination' => (string) $articles->links(),

                'total' => $articles->total(),

            ]);

        }



        return view('pages.articles', [

            'articles' => $articles,

            'contextPage' => 'articles',

            'categories' => $categories

        ]);            

    }





    public function create(){

        $categories = Categorie::select('id', 'nom')

            ->orderBy('nom')

            ->get();



        $sousCategories = SousCategorie::select('id', 'nom', 'categorie_id')

            ->orderBy('nom')

            ->get()

            ->groupBy('categorie_id');



        return view('pages.articles.create', [

            'categories' => $categories,

            'sousCategoriesGrouped' => $sousCategories,

        ]);

    }





    public function store(Request $request)
    {
        $wantsJson = $request->wantsJson() || $request->ajax();
        $jsonError = function (array $errors, ?string $message = null, array $solutions = []) {
            $payload = ['errors' => $errors];
            if ($message !== null) {
                $payload['message'] = $message;
            }
            if ($solutions !== []) {
                $payload['error_solutions'] = $solutions;
            }
            return response()->json($payload, 422);
        };

        $photos = $request->file('photos');

        

        // Si photos est null, essayer de récupérer comme tableau

        if ($photos === null) {

            $photos = $request->file('photos', []);

        }

        

        // Si photos n'est toujours pas un tableau, le convertir

        if (!is_array($photos)) {

            $photos = $photos ? [$photos] : [];

        }

        

        // Filtrer les fichiers valides (non null et valides)

        $validPhotos = [];

        foreach ($photos as $photo) {

            if ($photo !== null && 

                is_object($photo) && 

                method_exists($photo, 'isValid') && 

                $photo->isValid() &&

                $photo->getError() === UPLOAD_ERR_OK) {

                $validPhotos[] = $photo;

            }

        }

        

        $photos = $validPhotos;



        if (empty($photos)) {
            $err = ['photos' => ['Au moins une photo est obligatoire. Veuillez sélectionner au moins une image.']];
            if ($wantsJson) {
                return $jsonError($err, $err['photos'][0]);
            }
            return back()->withErrors($err)->withInput();
        }

        if (count($photos) > 6) {
            $err = ['photos' => ['Vous ne pouvez pas télécharger plus de 6 photos.']];
            if ($wantsJson) {
                return $jsonError($err, $err['photos'][0]);
            }
            return back()->withErrors($err)->withInput();
        }



        $validated = $request->validate([

            'categorie' => 'required|exists:categories,id',

            'sous_categorie_id' => 'required|exists:sous_categories,id',

            'titre' => 'required|string|max:255',

            'prix_ht' => 'required|numeric|min:0|max:999999999',

            'lieu' => 'required|string|max:255',

            'description' => 'required|string|min:20|max:1500',

            'etat' => 'required|in:neuf,occasion',

            'livraison' => 'nullable|boolean',

        ], [

            'categorie.required' => 'La catégorie est obligatoire.',

            'categorie.exists' => 'La catégorie sélectionnée n\'existe pas.',

            'sous_categorie_id.required' => 'La sous-catégorie est obligatoire.',

            'sous_categorie_id.exists' => 'La sous-catégorie sélectionnée n\'existe pas.',

            'titre.required' => 'Le titre est obligatoire.',

            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',

            'prix_ht.required' => 'Le prix est obligatoire.',

            'prix_ht.numeric' => 'Le prix doit être un nombre.',

            'prix_ht.min' => 'Le prix doit être supérieur ou égal à 0.',

            'lieu.required' => 'Le lieu est obligatoire.',

            'description.required' => 'La description est obligatoire.',

            'description.min' => 'La description doit contenir au moins 20 caractères.',

            'description.max' => 'La description ne peut pas dépasser 1500 caractères.',

            'etat.required' => 'L\'état du produit est obligatoire.',

            'etat.in' => 'L\'état doit être "neuf" ou "occasion".',

        ]);



        // Valider chaque photo individuellement

        foreach ($photos as $idx => $photo) {
            if (!$photo->isValid()) {
                $err = ['photos' => ['Une ou plusieurs photos sont invalides.']];
                if ($wantsJson) {
                    return $jsonError($err, $err['photos'][0]);
                }
                return back()->withErrors($err)->withInput();
            }

            $allowedMimes = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'bmp', 'heic', 'heif', 'svg', 'avif'];
            $extension = strtolower($photo->getClientOriginalExtension());
            if (!in_array($extension, $allowedMimes)) {
                $msg = 'Le format de fichier n\'est pas accepté. Formats acceptés : ' . implode(', ', $allowedMimes) . '.';
                $err = ['photos' => [$msg]];
                if ($wantsJson) {
                    return $jsonError($err, $msg);
                }
                return back()->withErrors($err)->withInput();
            }

            if ($photo->getSize() > 30720 * 1024) {
                $msg = 'Une ou plusieurs photos dépassent la taille maximale de 30 Mo. L\'application optimisera automatiquement vos images.';
                $err = ['photos' => [$msg]];
                if ($wantsJson) {
                    return $jsonError($err, $msg);
                }
                return back()->withErrors($err)->withInput();
            }
        }



        $categoryId = (int) $validated['categorie'];

        $sousCategorie = SousCategorie::select('id', 'categorie_id')->find($validated['sous_categorie_id']);

        if (!$sousCategorie || (int) $sousCategorie->categorie_id !== $categoryId) {
            $msg = 'La sous-catégorie sélectionnée n\'appartient pas à la catégorie choisie.';
            $err = ['sous_categorie_id' => [$msg]];
            if ($wantsJson) {
                return $jsonError($err, $msg);
            }
            return back()->withErrors($err)->withInput();
        }

        $images = array_fill(0, 6, null);

        $storedFiles = [];



        $destinationPath = public_path('articles');

        if (!File::exists($destinationPath)) {

            File::makeDirectory($destinationPath, 0777, true);

        }

        

        // Vérifier et corriger les permissions si nécessaire

        if (!is_writable($destinationPath)) {

            try {

                chmod($destinationPath, 0777);

                \Log::info('Permissions du dossier articles corrigées', ['path' => $destinationPath]);

            } catch (\Exception $permException) {

                \Log::error('Impossible de corriger les permissions du dossier', [

                    'path' => $destinationPath,

                    'error' => $permException->getMessage()

                ]);

            }

        }

        

        // Vérifier une dernière fois que le dossier est accessible

        if (!is_writable($destinationPath)) {
            \Log::error('Le dossier articles n\'est toujours pas accessible en écriture après correction', [
                'path' => $destinationPath,
                'permissions' => substr(sprintf('%o', fileperms($destinationPath)), -4)
            ]);
            $msg = 'Erreur de permissions : le dossier de destination n\'est pas accessible en écriture. Veuillez contacter l\'administrateur.';
            $err = ['photos' => [$msg]];
            if ($wantsJson) {
                return $jsonError($err, $msg);
            }
            return back()->withErrors($err)->withInput();
        }



        try {

            $imageOptimizer = new \App\Services\ImageOptimizer();

            

            $idx = 0;

            foreach ($photos as $photo) {

                if ($idx >= 6) {

                    break;

                }



                $filename = now()->format('YmdHis') . '_' . Str::random(16) . '.' . $photo->getClientOriginalExtension();

                

                // Optimiser et compresser l'image avant de la sauvegarder

                if (!$imageOptimizer->optimizeArticleImage($photo, $destinationPath, $filename)) {

                    // Si l'optimisation échoue, sauvegarder l'image originale

                    $photo->move($destinationPath, $filename);

                }



                $relativePath = 'articles/' . $filename;

                $images[$idx] = $relativePath;

                $storedFiles[] = $relativePath;

                $idx++;

            }

        } catch (\Throwable $exception) {

            // Logger l'erreur complète

            \Log::error('Erreur lors du traitement des images', [

                'message' => $exception->getMessage(),

                'file' => $exception->getFile(),

                'line' => $exception->getLine(),

                'trace' => $exception->getTraceAsString(),

                'stored_files' => $storedFiles,

                'destination_path' => $destinationPath,

                'path_exists' => File::exists($destinationPath),

                'path_writable' => File::exists($destinationPath) ? is_writable($destinationPath) : false

            ]);

            

            // Nettoyer les fichiers partiellement créés

            foreach ($storedFiles as $path) {

                $fullPath = public_path($path);

                if (File::exists($fullPath)) {

                    try {

                        File::delete($fullPath);

                    } catch (\Exception $deleteException) {

                        \Log::warning('Impossible de supprimer le fichier après erreur', [

                            'path' => $path,

                            'error' => $deleteException->getMessage()

                        ]);

                    }

                }

            }



            report($exception);



            // Message d'erreur plus détaillé

            $errorMessage = 'Une erreur est survenue lors du téléchargement des images.';
            $exceptionMessage = strtolower($exception->getMessage());

            if (str_contains($exceptionMessage, 'gd') || str_contains($exceptionMessage, 'driver')) {

                $errorMessage = 'L\'extension GD n\'est pas disponible. Veuillez activer GD dans votre configuration PHP et redémarrer Apache.';

            } elseif (str_contains($exceptionMessage, 'permission') || str_contains($exceptionMessage, 'writable')) {

                $errorMessage = 'Erreur de permissions : le dossier de destination n\'est pas accessible en écriture.';

            } elseif (str_contains($exceptionMessage, 'read') || str_contains($exceptionMessage, 'invalid')) {

                $errorMessage = 'Impossible de lire les images. Vérifiez que les fichiers ne sont pas corrompus.';

            }



            $err = ['photos' => [$errorMessage]];
            $solutions = $this->getErrorSolutions($exceptionMessage);
            if ($wantsJson) {
                return $jsonError($err, $errorMessage, $solutions);
            }
            return back()->withErrors($err)->with('error_solutions', $solutions)->withInput();
        }

        if (is_null($images[0])) {
            $err = ['photos' => ['La première image est obligatoire.']];
            if ($wantsJson) {
                return $jsonError($err, $err['photos'][0]);
            }
            return back()->withErrors($err)->withInput();
        }



        DB::beginTransaction();



        try {

            $article = new Article();



            $article->photo  = $images[0];

            $article->photo1 = $images[1];

            $article->photo2 = $images[2];

            $article->photo3 = $images[3];

            $article->photo4 = $images[4];

            $article->photo5 = $images[5];



            $article->user_id = auth()->id();

            $article->titre = $validated['titre'];

            $article->prix_ht = $validated['prix_ht'];

            $article->lieu = $validated['lieu'];

            $article->description = $validated['description'];

            $article->sous_categorie_id = $validated['sous_categorie_id'];

            $article->neuf = $validated['etat'] === 'neuf';

            $article->livraison = $request->boolean('livraison');

            $article->status = 'pending'; // Nouveaux articles en attente d'approbation



            $article->save();

            // Déclencher l'événement pour notifier l'admin
            event(new ArticlePending($article));



            DB::commit();

        } catch (\Throwable $exception) {

            DB::rollBack();



            foreach ($storedFiles as $path) {

                $fullPath = public_path($path);

                if (File::exists($fullPath)) {

                    File::delete($fullPath);

                }

            }



            report($exception);



            $msg = 'Impossible d\'enregistrer l\'article pour le moment. Veuillez réessayer.';
            $err = ['general' => [$msg]];
            $solutions = [
                'Vérifiez que tous les champs sont correctement remplis',
                'Assurez-vous que les images ne dépassent pas 5 Mo chacune',
                'Vérifiez votre connexion Internet',
                'Réessayez dans quelques instants',
            ];
            if ($wantsJson) {
                return $jsonError($err, $msg, $solutions);
            }
            return back()->withErrors($err)->with('error_solutions', $solutions)->withInput();

        }



        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'redirect' => route('mes_annonces'),
                'message' => 'Article ajouté avec succès !',
            ]);
        }
        return redirect()->route('mes_annonces')->with('success', 'Article ajouté avec succès !'); 

    }





    public function toggleLike(Request $request, Article $article)

    {

        $user = auth()->user();



        if (!$user) {

            return response()->json(['error' => 'Unauthorized'], 401);

        }



        // Vérifier si l'utilisateur a déjà liké

        if ($article->usersWhoLiked()->where('user_id', $user->id)->exists()) {

            $article->usersWhoLiked()->detach($user->id); // Déliker

            $liked = false;

        } else {

            $article->usersWhoLiked()->attach($user->id); // Liker

            $liked = true;

        }



        return response()->json([

            'liked' => $liked,

            'likeCount' => $article->usersWhoLiked()->count(),

        ]);

    }

         

    public function edit(Article $article)

    {

        if (auth()->id() !== $article->user_id) {

            abort(403, 'Accès refusé');

        }

        

        // Récupérer les catégories et sous-catégories comme pour la création

        $categories = Categorie::with('sousCategories')->get();

        

        // Grouper les sous-catégories par catégorie

        $sousCategories = $categories->mapWithKeys(function ($category) {

            return [$category->id => $category->sousCategories];

        });

        

        return view('pages.articles.edit', [

            'article' => $article,

            'categories' => $categories,

            'sousCategoriesGrouped' => $sousCategories,

        ]);

    }

 



    public function update(Request $request, Article $article)

    {

        // Vérifier que l'utilisateur est propriétaire

        if (auth()->id() !== $article->user_id) {

            abort(403, 'Vous n\'avez pas l\'autorisation de modifier cet article.');

        }



        try {

            $validated = $request->validate([

                'categorie' => 'required|exists:categories,id',

                'sous_categorie_id' => 'required|exists:sous_categories,id',

                'titre' => 'required|string|max:255',

                'prix_ht' => 'required|numeric|min:0|max:999999999',

                'lieu' => 'required|string|max:255',

                'description' => 'required|string|min:20|max:1500',

                'etat' => 'required|in:neuf,occasion',

                'livraison' => 'nullable|boolean',

                'photos' => 'nullable|array|max:6',

                'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp,bmp,heic,heif,svg,avif|max:30720',

            ], [

                'categorie.required' => 'La catégorie est obligatoire.',

                'categorie.exists' => 'La catégorie sélectionnée n\'existe pas.',

                'sous_categorie_id.required' => 'La sous-catégorie est obligatoire.',

                'sous_categorie_id.exists' => 'La sous-catégorie sélectionnée n\'existe pas.',

                'titre.required' => 'Le titre est obligatoire.',

                'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',

                'prix_ht.required' => 'Le prix est obligatoire.',

                'prix_ht.numeric' => 'Le prix doit être un nombre.',

                'prix_ht.min' => 'Le prix doit être supérieur ou égal à 0.',

                'lieu.required' => 'Le lieu est obligatoire.',

                'description.required' => 'La description est obligatoire.',

                'description.min' => 'La description doit contenir au moins 20 caractères.',

                'description.max' => 'La description ne peut pas dépasser 1500 caractères.',

                'etat.required' => 'L\'état du produit est obligatoire.',

                'etat.in' => 'L\'état doit être "neuf" ou "occasion".',

                'photos.max' => 'Vous ne pouvez pas télécharger plus de 6 images.',

                'photos.*.image' => 'Tous les fichiers doivent être des images.',

                'photos.*.mimes' => 'Les images doivent être au format : jpeg, png, jpg, gif, webp, bmp, heic, heif, svg ou avif.',

                'photos.*.max' => 'Chaque image ne doit pas dépasser 30 Mo. L\'application optimisera automatiquement vos images.',

            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return back()

                ->withErrors($e->errors())

                ->with('error_solutions', [

                    'Vérifiez que tous les champs obligatoires sont remplis',

                    'Assurez-vous que les images sont au bon format',

                    'Vérifiez que vous ne dépassez pas 6 images',

                    'Vérifiez que le prix est un nombre valide'

                ])

                ->withInput();

        }



        // Vérifier que la sous-catégorie appartient à la catégorie

        $categoryId = (int) $validated['categorie'];

        $sousCategorie = SousCategorie::select('id', 'categorie_id')->find($validated['sous_categorie_id']);



        if (!$sousCategorie || (int) $sousCategorie->categorie_id !== $categoryId) {

            return back()

                ->withErrors([

                    'sous_categorie_id' => 'La sous-catégorie sélectionnée n\'appartient pas à la catégorie choisie.'

                ])

                ->withInput();

        }



        DB::beginTransaction();



        try {

            // Mettre à jour les images si de nouvelles sont fournies

            if ($request->hasFile('photos')) {

                $photos = $request->file('photos');

                

                // Filtrer les fichiers valides

                $validPhotos = [];

                foreach ($photos as $photo) {

                    if ($photo !== null && 

                        is_object($photo) && 

                        method_exists($photo, 'isValid') && 

                        $photo->isValid() &&

                        $photo->getError() === UPLOAD_ERR_OK) {

                        $validPhotos[] = $photo;

                    }

                }



                if (count($validPhotos) > 0) {

                    $destinationPath = public_path('articles');

                    if (!File::exists($destinationPath)) {

                        File::makeDirectory($destinationPath, 0777, true);

                    }



                    $imageOptimizer = new \App\Services\ImageOptimizer();

                    $images = [$article->photo, $article->photo1, $article->photo2, $article->photo3, $article->photo4, $article->photo5];

                    

                    $idx = 0;

                    foreach ($validPhotos as $photo) {

                        if ($idx >= 6) break;



                        // Supprimer l'ancienne image si elle existe

                        if (isset($images[$idx]) && $images[$idx]) {

                            $oldPath = public_path($images[$idx]);

                            if (File::exists($oldPath)) {

                                try {

                                    File::delete($oldPath);

                                } catch (\Exception $e) {

                                    \Log::warning('Impossible de supprimer l\'ancienne image: ' . $e->getMessage());

                                }

                            }

                        }



                        $filename = now()->format('YmdHis') . '_' . Str::random(16) . '.' . $photo->getClientOriginalExtension();

                        

                        // Optimiser et sauvegarder

                        if (!$imageOptimizer->optimizeArticleImage($photo, $destinationPath, $filename)) {

                            $photo->move($destinationPath, $filename);

                        }



                        $relativePath = 'articles/' . $filename;

                        $article->{'photo' . ($idx === 0 ? '' : $idx)} = $relativePath;

                        $idx++;

                    }

                }

            }



            // Mettre à jour les champs

            $article->titre = $validated['titre'];

            $article->prix_ht = $validated['prix_ht'];

            $article->lieu = $validated['lieu'];

            $article->description = $validated['description'];

            $article->sous_categorie_id = $validated['sous_categorie_id'];

            $article->neuf = $validated['etat'] === 'neuf';

            $article->livraison = $request->boolean('livraison');



            $article->save();



            DB::commit();



            return redirect()->route('mes_annonces')->with('success', 'Article modifié avec succès.');

            

        } catch (\Throwable $exception) {

            DB::rollBack();

            

            \Log::error('Erreur lors de la modification de l\'article', [

                'article_id' => $article->id,

                'error_message' => $exception->getMessage(),

                'error_file' => $exception->getFile(),

                'error_line' => $exception->getLine(),

                'error_trace' => $exception->getTraceAsString()

            ]);



            return back()

                ->withErrors([

                    'general' => 'Une erreur est survenue lors de la modification de l\'article.'

                ])

                ->with('error_solutions', [

                    'Vérifiez que tous les champs sont correctement remplis',

                    'Assurez-vous que les images ne dépassent pas 5 Mo chacune',

                    'Vérifiez votre connexion Internet',

                    'Réessayez dans quelques instants'

                ])

                ->withInput();

        }

    }

    /**
     * Retourne les solutions d'erreur selon le type d'erreur
     */
    private function getErrorSolutions(string $exceptionMessage): array
    {
        if (str_contains($exceptionMessage, 'gd') || str_contains($exceptionMessage, 'driver')) {
            return [
                'L\'extension GD n\'est pas disponible. Veuillez activer GD dans votre configuration PHP',
                'Redémarrez Apache après avoir activé GD dans le fichier php.ini',
                'Vérifiez que vous avez modifié le bon fichier php.ini (celui utilisé par Apache, pas CLI)',
                'Consultez les logs Laravel pour plus de détails (storage/logs/laravel.log)'
            ];
        }
        
        return [
            'Vérifiez que les images sont au format JPG, PNG ou WEBP',
            'Assurez-vous que chaque image ne dépasse pas 5 Mo',
            'Réduisez la taille des images si nécessaire',
            'Vérifiez les permissions du dossier public/articles',
            'Consultez les logs Laravel pour plus de détails (storage/logs/laravel.log)'
        ];
    }

    

    public function destroy(Article $article)

    {

        if (auth()->id() !== $article->user_id) {

            abort(403, 'Vous n\'avez pas l\'autorisation de supprimer cet article.');

        }

        

        try {

            $article->delete();

            return redirect()->route('mes_annonces')->with('success', 'Article supprimé avec succès.');

        } catch (\Exception $e) {

            \Log::error('Erreur lors de la suppression de l\'article: ' . $e->getMessage());

            return back()

                ->with('error', 'Impossible de supprimer l\'article pour le moment.')

                ->with('error_solutions', [

                    'Réessayez dans quelques instants',

                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'

                ]);

        }

    }

    

    public function transfer(Article $article)

    {

        if (auth()->id() !== $article->user_id) {

            abort(403, 'Accès refusé');

        }

        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get(['id', 'name', 'email']);
        return view('pages.articles.transfer', compact('article', 'users'));

    }

    

    public function doTransfer(Request $request, Article $article)

    {

        if (auth()->id() !== $article->user_id) {

            abort(403, 'Vous n\'avez pas l\'autorisation de transférer cet article.');

        }

        

        try {

            $request->validate([

                'user_id' => 'required|exists:users,id',

            ], [

                'user_id.required' => 'Veuillez sélectionner un utilisateur.',

                'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',

            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return back()

                ->withErrors($e->errors())

                ->with('error_solutions', [

                    'Sélectionnez un utilisateur valide dans la liste',

                    'Vérifiez que l\'utilisateur existe dans le système'

                ])

                ->withInput();

        }

        

        try {

            $article->user_id = $request->user_id;

            $article->save();

            return redirect()->route('mes_annonces')->with('success', 'Article transféré avec succès.');

        } catch (\Exception $e) {

            \Log::error('Erreur lors du transfert de l\'article: ' . $e->getMessage());

            return back()

                ->with('error', 'Impossible de transférer l\'article pour le moment.')

                ->with('error_solutions', [

                    'Vérifiez que l\'utilisateur de destination existe',

                    'Réessayez dans quelques instants',

                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'

                ])

                ->withInput();

        }

    }

    

       

    public function show($id)

    {

        $article = Article::with('user')->findOrFail($id);



        $vendeur = $article->user;



        // Membre depuis

        $membreDepuis = $vendeur->created_at->diffForHumans(); 

        // Exemple : "il y a 2 ans"



        // Nombre d’articles publiés

        $nbArticles = $vendeur->articles()->count();



        // Nombre total de likes sur tous ses articles

        $totalLikes = $vendeur->articles()->withCount('likes')->sum('likes_count');



        return view('articles.show', compact('article', 'membreDepuis', 'nbArticles', 'totalLikes'));

    }



    





}

