<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DetailArticleController extends Controller
{
    /**
     * Nouvelle URL SEO :
     * /annonce/{categorie}/{sousCategorie}/{slug}-{id}
     */
    public function show(string $categorie, string $sousCategorie, string $slugId): View|RedirectResponse
    {
        if (! preg_match('/-(\d+)$/', $slugId, $matches)) {
            abort(404);
        }

        $article = $this->loadArticle((int) $matches[1]);

        // Canonical : redirige si catégorie / slug obsolètes
        $params = $article->routeParameters();
        if (
            $categorie !== $params['categorie']
            || $sousCategorie !== $params['sousCategorie']
            || $slugId !== $params['slugId']
        ) {
            return redirect()->route('article.details', $params, 301);
        }

        return $this->render($article);
    }

    /**
     * Ancienne URL /article/{id} → redirection 301 vers l'URL SEO.
     */
    public function showLegacy(int $id): RedirectResponse
    {
        $article = $this->loadArticle($id);

        return redirect($article->url(), 301);
    }

    protected function loadArticle(int $id): Article
    {
        $article = Article::with([
            'user:id,name,email,telephone,whatsapp,photo_profil,certifie,certifie_until,created_at',
            'sousCategorie.categorie',
        ])
            ->withCount('comments')
            ->withLikeCounts(auth()->id())
            ->findOrFail($id);

        // Seuls le vendeur et les admins peuvent voir une annonce en attente/bloquée
        // (sinon fuite du statut de modération et des coordonnées du vendeur par ID deviné).
        $user = auth()->user();
        $isOwnerOrAdmin = $user && ($user->id === $article->user_id || $user->isAdmin());
        if (! $article->isApproved() && ! $isOwnerOrAdmin) {
            abort(404);
        }

        return $article;
    }

    protected function render(Article $article): View
    {
        // TEMPORAIRE — Avis désactivés pour le lancement (section masquée dans detail_article.blade.php).
        // Pour réactiver : décommenter le chargement ci-dessous et activer le @if(false) → true dans la vue.
        $comments = collect();
        $commentsTotal = 0;
        // $comments = $article->comments()
        //     ->with('user:id,name,photo_profil,certifie')
        //     ->orderBy('created_at', 'desc')
        //     ->limit(10)
        //     ->get();
        // $commentsTotal = $article->comments_count;

        $articlesParCategorie = Article::where('sous_categorie_id', $article->sous_categorie_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'approved')
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie', 'sousCategorie.categorie'])
            ->take(5)
            ->get();

        if ($articlesParCategorie->isEmpty() && $article->sousCategorie) {
            $articlesParCategorie = Article::whereHas('sousCategorie', function ($query) use ($article) {
                $query->where('categorie_id', $article->sousCategorie->categorie_id);
            })
                ->where('id', '!=', $article->id)
                ->where('status', 'approved')
                ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'created_at')
                ->withLikeCounts(auth()->id())
                ->with(['user:id,name,photo_profil,certifie', 'sousCategorie.categorie'])
                ->take(5)
                ->get();
        }

        $sellerTotalLikes = (int) DB::table('article_user_like')
            ->join('articles', 'articles.id', '=', 'article_user_like.article_id')
            ->where('articles.user_id', $article->user_id)
            ->count();

        return view('pages.detail_article', compact(
            'article',
            'articlesParCategorie',
            'comments',
            'commentsTotal',
            'sellerTotalLikes'
        ));
    }
}
