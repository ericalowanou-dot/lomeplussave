<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Article;
use App\Models\User;
use App\Models\Categorie;
use App\Models\SousCategorie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DetailArticleController extends Controller
{
    //
    public function show($id)
    {
      
        $article = Article::with(['user', 'comments.user' => function($query) {
            $query->select('id', 'name', 'photo_profil', 'certifie');
        }])->findOrFail($id);
        
        // Trier les commentaires par date (plus récents en premier)
        $article->comments = $article->comments->sortByDesc('created_at')->values();

        // Récupérer les articles de la même sous-catégorie (avec eager loading)
        $articlesParCategorie = Article::where('sous_categorie_id', $article->sous_categorie_id)
            ->where('id', '!=', $article->id) // Exclure l'article actuel
            ->where('status', 'approved') // Seulement les articles approuvés
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom'])
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
            ->take(5) // Limiter à 5 articles
            ->get();

        // Si aucun article de la même sous-catégorie, récupérer les articles de la même catégorie
        if ($articlesParCategorie->isEmpty()) {
            $articlesParCategorie = Article::whereHas('sousCategorie', function ($query) use ($article) {
                $query->where('categorie_id', $article->sousCategorie->categorie_id);
            })
            ->where('id', '!=', $article->id) // Exclure l'article actuel
            ->where('status', 'approved') // Seulement les articles approuvés
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom'])
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
            ->take(5) // Limiter à 5 articles
            ->get();
        }

        return view('pages.detail_article', compact('article', 'articlesParCategorie'));



    }
}
