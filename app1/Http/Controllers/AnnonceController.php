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

    // 3️⃣ et là seulement on pagine (ça exécute) - avec eager loading
    $articles = $query
        ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom,categorie_id'])
        ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
        ->orderBy('created_at', 'desc')
        ->paginate(15);

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
        $article = Article::findOrFail($id); // recuperer un article par son id
        return view('pages.detail_article', compact('article'));


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
    
        // Récupérez les favoris de l'utilisateur connecté
        $favoris = Auth::user()->favoris()->paginate(15); // Assurez-vous que la relation "favoris" est définie dans le modèle User
    
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
    $q = $request->input('q');

    $articles = Article::query()
        ->where('titre', 'like', "%$q%")
        ->orWhere('description', 'like', "%$q%")
        ->paginate(15);

    return view('partials.articles-list', compact('articles'));
}


        public function searchFavoris(Request $request)
{
    $q = $request->input('q');

    $favoris = Auth::user()->favoris() // relation favoris
        ->where('titre', 'like', "%$q%")
        ->paginate(15);

    return view('partials.favoris-list', compact('favoris'));
}


public function searchMesAnnonces(Request $request)
{
    $q = $request->input('q');

    $articles = Article::where('user_id', Auth::id())
        ->where('titre', 'like', "%$q%")
        ->paginate(15);

    return view('partials.annonces-list', compact('articles'));
}

  
}
