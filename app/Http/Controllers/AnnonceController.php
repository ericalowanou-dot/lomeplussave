<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Annonce; 
use App\Models\Article;
use App\Models\User;
use App\Models\Categorie;
use App\Models\SousCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnnonceController extends Controller
{

public function index(Request $request)
{
    // Utiliser le cache pour les catégories
    $categories = \Cache::remember('categories_with_souscategories', 3600, function () {
        return Categorie::with('sousCategories')->get();
    });

    // 1️⃣ on construit la requête mais sans exécuter
    $query = Article::where('user_id', Auth::id());

    // 2️⃣ filtres supplémentaires
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('categorie')) {
        // Attention au nom du modèle : SousCategorie - avec cache
        $sousCategoriesIds = \Cache::remember("souscategories_categorie_{$request->categorie}", 3600, function () use ($request) {
            return SousCategorie::where('categorie_id', $request->categorie)->pluck('id');
        });
        $query->whereIn('sous_categorie_id', $sousCategoriesIds);
    }

    if ($request->filled('prix_min')) {
        $query->where('prix_ht', '>=', $request->prix_min);
    }

    if ($request->filled('prix_max')) {
        $query->where('prix_ht', '<=', $request->prix_max);
    }

    if ($request->filled('ville')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('ville', 'like', '%' . $request->ville . '%');
        });
    }

    if ($request->filled('boosted')) {
        if ($request->boosted == '1') {
            $query->whereNotNull('boosted_until')->where('boosted_until', '>', now());
        } else {
            $query->where(function($q) {
                $q->whereNull('boosted_until')->orWhere('boosted_until', '<=', now());
            });
        }
    }

    // Filtre par période
    if ($request->filled('date_filter')) {
        $dateFilter = $request->date_filter;
        if ($dateFilter === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($dateFilter === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        } elseif ($dateFilter === 'custom') {
            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }
        }
    }

    $articles = $query
        ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
        ->withLikeCounts(Auth::id())
        ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
        ->orderBy('created_at', 'desc')
        ->paginate(30);

    // Statistiques optimisées : une seule requête avec groupBy
    $statsQuery = Article::where('user_id', Auth::id())
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked,
            SUM(CASE WHEN boosted_until IS NOT NULL AND boosted_until > NOW() THEN 1 ELSE 0 END) as boosted
        ')
        ->first();
    
    $stats = [
        'total' => (int) $statsQuery->total,
        'pending' => (int) $statsQuery->pending,
        'approved' => (int) $statsQuery->approved,
        'blocked' => (int) $statsQuery->blocked,
        'boosted' => (int) $statsQuery->boosted,
    ];

    return view('pages.mesannonces', [
        'articles' => $articles,
        'contextPage' => 'mesannonces',
        'categories' => $categories,
        'stats' => $stats
    ]);
}



    public function show($id)
    {
        // Ancienne URL /annonce/{id} : on redirige vers l'URL SEO canonique
        // (qui applique aussi le controle de visibilite : annonce en attente/bloquee
        // reservee au vendeur et aux admins, voir DetailArticleController::loadArticle).
        $article = Article::findOrFail($id);

        $user = Auth::user();
        $isOwnerOrAdmin = $user && ($user->id === $article->user_id || $user->isAdmin());
        if (! $article->isApproved() && ! $isOwnerOrAdmin) {
            abort(404);
        }

        return redirect($article->url(), 301);
    }


    

    public function maPaginnation(){

        $articles = Article::paginate(5);
            
        return view('pages.pagination', compact('articles'));
    }


    
    public function mesFavoris()
    {
        // Vérifiez si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour voir vos favoris.');
        }
    
        $favoris = Auth::user()->favoris()
            ->select('articles.id', 'articles.user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'articles.created_at', 'neuf', 'livraison')
            ->withLikeCounts(Auth::id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->orderBy('articles.created_at', 'desc')
            ->paginate(30);
    
        // Statistiques pour l'utilisateur
        $stats = [
            'total' => Auth::user()->favoris()->count(),
        ];

        return view('pages.mes_favoris', [
            'favoris' => $favoris,
            'contextPage' => 'favoris',
            'stats' => $stats
        ]);
    }







    public function searchArticles(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $articles = Article::query()
            ->where('status', 'approved')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('titre', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('lieu', 'like', "%{$q}%");
                });
            })
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
            ->withLikeCounts(Auth::id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        if ($request->ajax()) {
            return response()->json([
                'list' => view('partials.articles-list', compact('articles'))->render(),
                'pagination' => (string) $articles->links(),
                'total' => $articles->total(),
            ]);
        }

        return view('partials.articles-list', compact('articles'));
    }

    public function searchFavoris(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $favoris = Auth::user()->favoris()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('titre', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('lieu', 'like', "%{$q}%");
                });
            })
            ->select('articles.id', 'articles.user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'articles.created_at', 'neuf', 'livraison')
            ->withLikeCounts(Auth::id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->orderBy('articles.created_at', 'desc')
            ->paginate(30);

        if ($request->ajax()) {
            return response()->json([
                'list' => view('partials.favoris-list', compact('favoris'))->render(),
                'pagination' => '',
                'total' => $favoris->total(),
            ]);
        }

        return view('partials.favoris-list', compact('favoris'));
    }

    public function searchMesAnnonces(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $articlesQuery = Article::where('user_id', Auth::id())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('titre', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('lieu', 'like', "%{$q}%");
                });
            });

        // Conserver les filtres éventuels de la page Mes annonces
        if ($request->filled('status')) {
            $articlesQuery->where('status', $request->status);
        }
        if ($request->filled('boosted')) {
            if ($request->boosted == '1') {
                $articlesQuery->whereNotNull('boosted_until')->where('boosted_until', '>', now());
            } else {
                $articlesQuery->where(function ($q2) {
                    $q2->whereNull('boosted_until')->orWhere('boosted_until', '<=', now());
                });
            }
        }

        $articles = $articlesQuery
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
            ->withLikeCounts(Auth::id())
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'list' => view('partials.annonces-list', compact('articles'))->render(),
                'pagination' => (string) $articles->links(),
                'total' => $articles->total(),
            ]);
        }

        return view('partials.annonces-list', compact('articles'));
    }
}
