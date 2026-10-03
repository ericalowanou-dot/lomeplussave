<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Article;

use App\Models\User;

use App\Models\Categorie;

use App\Models\SousCategorie;

use App\Http\Requests\Articles\StoreArticleRequest;
use App\Http\Requests\Articles\UpdateArticleRequest;
use App\Services\Articles\ArticleService;
use App\Services\Articles\PhotoStorageException;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;

use App\Services\StatTracker;








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

        // Validation (assouplie en AJAX live : q optionnel si on nettoie)
        $isAjax = $request->ajax() || $request->wantsJson();

        if (!$isAjax) {
            $request->validate([
                'q' => 'required|min:3|string|max:255'
            ]);
        } else {
            $request->validate([
                'q' => 'nullable|string|max:255'
            ]);
        }

        $q = trim((string) $request->input('q', ''));

        // Recherche optimisée avec eager loading
        $articlesQuery = Article::where('status', 'approved');

        if ($q !== '') {
            $articlesQuery->where(function($query) use ($q) {
                $query->where('titre', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%")
                      ->orWhere('lieu', 'like', "%$q%")
                      ->orWhereHas('user', function ($userQuery) use ($q) {
                          // Pas l'email : taper « gmail » ressortait toutes les annonces des vendeurs Gmail
                          $userQuery->where('name', 'like', "%$q%")
                                    ->orWhere('ville', 'like', "%$q%");
                      })
                      ->orWhereHas('sousCategorie', function ($subQuery) use ($q) {
                          $subQuery->where('nom', 'like', "%$q%")
                                   ->orWhereHas('categorie', function ($catQuery) use ($q) {
                                       $catQuery->where('nom', 'like', "%$q%");
                                   });
                      });
            });
        }

        $articles = $articlesQuery
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > ?) DESC', [now()])
            ->orderBy('created_at', 'desc')
            ->paginate(120)
            ->appends($request->query());

        if ($q !== '' && $articles->currentPage() === 1) {
            app(StatTracker::class)->recordSearch($q, $articles->total(), $request, $isAjax ? 'recherche_directe' : 'recherche');
        }

        // Récupérer les catégories pour la navigation (avec cache)
        $categories = \Cache::remember('categories_with_souscategories', 3600, function () {
            return \App\Models\Categorie::with('sousCategories')->get();
        });

        if ($isAjax) {
            return response()->json([
                'list' => view('partials.articles-list', ['articles' => $articles])->render(),
                'pagination' => (string) $articles->links(),
                'total' => $articles->total(),
                'q' => $q,
            ]);
        }

        return view('products.search', [
            'articles' => $articles,
            'q' => $q,
            'categories' => $categories,
            'contextPage' => 'articles',
        ]);
    }



  

    public function index(Request $request){

        // Récupérer toutes les catégories avec leurs sous-catégories (avec cache)

        $categories = \Cache::remember('categories_with_souscategories', 3600, function () {

            return Categorie::with('sousCategories')->get();

        });

      

        $articlesQuery = Article::query();

        // 🔹 Recherche texte (barre "Rechercher...")
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            if ($q !== '') {
                $articlesQuery->where(function ($query) use ($q) {
                    $query->where('titre', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('lieu', 'like', "%{$q}%");
                });
            }
        }



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

                $articlesQuery->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > ?) DESC', [now()])

                    ->orderBy('created_at', 'desc');

                break;

            case 'recent':

            default:

                $articlesQuery->orderByRaw('(boosted_until IS NOT NULL AND boosted_until > ?) DESC', [now()])

                    ->orderBy('created_at', 'desc');

                break;

        }



        // 🔹 Nombre d'articles par page
        // Défaut initial du projet.

        $perPage = $request->get('per_page', 120);

        $allowedPerPage = [12, 24, 40, 48, 80, 96, 120];

        if (!in_array((int)$perPage, $allowedPerPage)) {

            $perPage = 120;

        }



        $articles = $articlesQuery

            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')

            ->withLikeCounts(auth()->id())

            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])

            ->paginate($perPage)

            ->appends($request->query());

        $searchTerm = trim((string) $request->input('q', ''));
        if ($searchTerm !== '' && $articles->currentPage() === 1) {
            app(StatTracker::class)->recordSearch($searchTerm, $articles->total(), $request, 'accueil');
        }



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





    public function store(StoreArticleRequest $request, ArticleService $articles)
    {
        $wantsJson = $request->wantsJson() || $request->ajax();

        try {
            $articles->publish($request->user(), $request->articleAttributes(), $request->photos());
        } catch (PhotoStorageException $e) {
            report($e);

            return $this->photoStorageFailure($e, $wantsJson);
        } catch (\Throwable $e) {
            report($e);

            $solutions = [
                'Vérifiez que tous les champs sont correctement remplis',
                'Vérifiez votre connexion Internet',
                'Réessayez dans quelques instants',
            ];
            if ($wantsJson) {
                return response()->json([
                    'message' => 'Une erreur serveur est survenue lors de la publication. Réessayez dans quelques instants.',
                    'errors' => ['general' => ['Impossible de publier l\'annonce pour le moment.']],
                    'error_solutions' => $solutions,
                ], 500);
            }

            return back()
                ->withErrors(['general' => ['Impossible d\'enregistrer l\'article pour le moment. Veuillez réessayer.']])
                ->with('error_solutions', $solutions)
                ->withInput();
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

    public function toggleLike(Request $request, Article $article, ArticleService $articles): JsonResponse
    {
        return response()->json($articles->toggleLike($article, $request->user()));
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

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

    public function update(UpdateArticleRequest $request, Article $article, ArticleService $articles)
    {
        try {
            $needsReview = $articles->update($article, $request->user(), $request->articleAttributes(), $request->photos());
        } catch (PhotoStorageException $e) {
            report($e);

            return $this->photoStorageFailure($e, false);
        } catch (\Throwable $exception) {
            \Log::error('Erreur lors de la modification de l\'article', [
                'article_id' => $article->id,
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine(),
            ]);

            return back()
                ->withErrors(['general' => 'Une erreur est survenue lors de la modification de l\'article.'])
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                ])
                ->withInput();
        }

        $successMessage = $needsReview
            ? 'Article modifié. Il a été renvoyé en validation : un administrateur doit le réexaminer avant republication.'
            : 'Article modifié avec succès.';

        return redirect()->route('mes_annonces')->with('success', $successMessage);
    }

    /**
     * Une photo n'a pas pu être écrite sur le disque : message clair plutôt qu'une erreur 500.
     */
    private function photoStorageFailure(PhotoStorageException $e, bool $wantsJson)
    {
        $exceptionMessage = strtolower($e->getMessage());
        $errorMessage = str_contains($exceptionMessage, 'permission') || str_contains($exceptionMessage, 'writable')
            ? 'Erreur de permissions : le dossier de destination n\'est pas accessible en écriture.'
            : 'Une erreur est survenue lors du téléchargement des images.';

        $solutions = [
            'Vérifiez que les images sont au format JPG, PNG ou WEBP',
            'Réduisez la taille des images si nécessaire',
            'Réessayez dans quelques instants',
            'Si le problème persiste, contactez-nous : lomeplus80@gmail.com',
        ];

        if ($wantsJson) {
            return response()->json([
                'message' => $errorMessage,
                'errors' => ['photos' => [$errorMessage]],
                'error_solutions' => $solutions,
            ], 422);
        }

        return back()->withErrors(['photos' => [$errorMessage]])->with('error_solutions', $solutions)->withInput();
    }

    public function destroy(Article $article, ArticleService $articles)
    {
        Gate::authorize('delete', $article);

        try {
            $articles->delete($article);

            return redirect()->route('mes_annonces')->with('success', 'Article supprimé avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de l\'article: ' . $e->getMessage());

            return back()
                ->with('error', 'Impossible de supprimer l\'article pour le moment.')
                ->with('error_solutions', [
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com',
                ]);
        }
    }

    public function transfer(Article $article)
    {
        Gate::authorize('transfer', $article);

        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get(['id', 'name', 'email']);

        return view('pages.articles.transfer', compact('article', 'users'));
    }

    public function doTransfer(Request $request, Article $article, ArticleService $articles)
    {
        Gate::authorize('transfer', $article);

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
                    'Vérifiez que l\'utilisateur existe dans le système',
                ])
                ->withInput();
        }

        try {
            $articles->transfer($article, User::findOrFail($request->user_id));

            return redirect()->route('mes_annonces')->with('success', 'Article transféré avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors du transfert de l\'article: ' . $e->getMessage());

            return back()
                ->with('error', 'Impossible de transférer l\'article pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que l\'utilisateur de destination existe',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com',
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

