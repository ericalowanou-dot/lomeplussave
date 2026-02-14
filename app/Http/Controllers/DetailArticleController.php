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
    public function show($id)
    {
        $article = Article::with(['user'])
            ->withCount('comments')
            ->withLikeCounts(auth()->id())
            ->findOrFail($id);

        $comments = $article->comments()
            ->with('user:id,name,photo_profil,certifie')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $commentsTotal = $article->comments_count;

        $articlesParCategorie = Article::where('sous_categorie_id', $article->sous_categorie_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'approved')
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom'])
            ->take(5)
            ->get();

        if ($articlesParCategorie->isEmpty()) {
            $articlesParCategorie = Article::whereHas('sousCategorie', function ($query) use ($article) {
                $query->where('categorie_id', $article->sousCategorie->categorie_id);
            })
                ->where('id', '!=', $article->id)
                ->where('status', 'approved')
                ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
                ->withLikeCounts(auth()->id())
                ->with(['user:id,name,photo_profil,certifie', 'sousCategorie:id,nom'])
                ->take(5)
                ->get();
        }

        $sellerTotalLikes = (int) DB::table('article_user_like')
            ->join('articles', 'articles.id', '=', 'article_user_like.article_id')
            ->where('articles.user_id', $article->user_id)
            ->count();

        return view('pages.detail_article', compact('article', 'articlesParCategorie', 'comments', 'commentsTotal', 'sellerTotalLikes'));
    }
}
