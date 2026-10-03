<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\StoreArticleRequest;
use App\Http\Requests\Articles\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\Articles\ArticleSearch;
use App\Services\Articles\ArticleService;
use App\Services\Articles\PhotoStorageException;
use App\Services\CoinService;
use App\Services\InsufficientCoinsException;
use App\Services\StatTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Annonces dans l'appli : mêmes règles que le site (ArticleService, ArticleSearch, ArticlePolicy).
 */
class ArticleController extends Controller
{
    /** Liste publique avec les filtres de l'accueil (voir ArticleSearch::BROWSE_FILTERS). */
    public function index(Request $request, ArticleSearch $search)
    {
        $request->validate([
            'prix_min' => ['nullable', 'numeric', 'min:0'],
            'prix_max' => ['nullable', 'numeric', 'min:0'],
            'categorie' => ['nullable', 'integer'],
            'sous_categorie' => ['nullable', 'integer'],
            'etat' => ['nullable', 'in:neuf,occasion'],
            'order_by' => ['nullable', 'in:recent,pro,prix_asc,prix_desc'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $articles = $search->browse($request->only(ArticleSearch::BROWSE_FILTERS))
            ->paginate($this->perPage($request))
            ->withQueryString();

        $this->trackSearch($request, (string) $request->input('q', ''), $articles);

        return ArticleResource::collection(ArticleResource::markLikedBy($articles, $request->user()));
    }

    /** Recherche large : titre, description, lieu, vendeur, catégories. */
    public function search(Request $request, ArticleSearch $search)
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:255']]);

        $articles = $search->search($data['q'])
            ->paginate($this->perPage($request))
            ->withQueryString();

        $this->trackSearch($request, $data['q'], $articles);

        return ArticleResource::collection(ArticleResource::markLikedBy($articles, $request->user()));
    }

    public function show(Request $request, int $article): ArticleResource
    {
        $article = Article::with([
            'user:id,name,telephone,whatsapp,photo_profil,certifie,certifie_from,certifie_until,ville,created_at',
            'sousCategorie.categorie',
        ])
            ->withCount('comments')
            ->withLikeCounts()
            ->findOrFail($article);

        Gate::authorize('view', $article);

        app(StatTracker::class)->recordArticleView($article, $request);

        return new ArticleResource(ArticleResource::markLikedBy($article, $request->user()));
    }

    /** Publication (multipart/form-data, photos[] : 1 à 6 images). */
    public function store(StoreArticleRequest $request, ArticleService $articles): JsonResponse
    {
        try {
            $article = $articles->publish($request->user(), $request->articleAttributes(), $request->photos());
        } catch (PhotoStorageException $e) {
            report($e);

            return $this->photoFailure();
        }

        return (new ArticleResource($this->reload($article)))
            ->additional(['message' => 'Annonce envoyée. Elle sera visible après validation par un administrateur.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Modification. En multipart, utiliser POST /articles/{id} (PHP ne lit pas les
     * fichiers d'un PUT) ; PUT/PATCH restent possibles sans photo.
     */
    public function update(UpdateArticleRequest $request, Article $article, ArticleService $articles): JsonResponse
    {
        try {
            $needsReview = $articles->update($article, $request->user(), $request->articleAttributes(), $request->photos());
        } catch (PhotoStorageException $e) {
            report($e);

            return $this->photoFailure();
        }

        return (new ArticleResource($this->reload($article)))
            ->additional([
                'message' => $needsReview
                    ? 'Annonce modifiée. Elle a été renvoyée en validation avant republication.'
                    : 'Annonce modifiée.',
                'en_validation' => $needsReview,
            ])
            ->response();
    }

    public function destroy(Request $request, Article $article, ArticleService $articles): JsonResponse
    {
        Gate::authorize('delete', $article);

        $articles->delete($article);

        return response()->json(['message' => 'Annonce supprimée.']);
    }

    public function like(Request $request, Article $article, ArticleService $articles): JsonResponse
    {
        Gate::authorize('view', $article);

        return response()->json($articles->toggleLike($article, $request->user()));
    }

    public function boost(Request $request, Article $article, CoinService $coins): JsonResponse
    {
        Gate::authorize('boost', $article);
        $data = $request->validate(['days' => ['required', 'integer', 'min:1', 'max:365']]);

        try {
            $coins->boostArticle($request->user(), $article, (int) $data['days']);
        } catch (InsufficientCoinsException $e) {
            return response()->json([
                'message' => 'Solde de coins insuffisant : il faut ' . $e->needed . ' coins pour ' . $e->needed . ' jour(s).',
                'code' => 'insufficient_coins',
            ], 422);
        }

        return response()->json([
            'boosted_until' => $article->boosted_until->toIso8601String(),
            'coins' => (int) $request->user()->coins,
        ]);
    }

    private function reload(Article $article): Article
    {
        return Article::with(['user', 'sousCategorie.categorie'])->withLikeCounts()->findOrFail($article->id);
    }

    private function photoFailure(): JsonResponse
    {
        return response()->json([
            'message' => 'Une erreur est survenue lors de l\'enregistrement des photos. Réessayez dans quelques instants.',
            'errors' => ['photos' => ['Impossible d\'enregistrer les photos pour le moment.']],
        ], 503);
    }

    private function trackSearch(Request $request, string $q, $articles): void
    {
        if (trim($q) !== '' && $articles->currentPage() === 1) {
            app(StatTracker::class)->recordSearch($q, $articles->total(), $request, 'appli');
        }
    }

    private function perPage(Request $request): int
    {
        return max(1, min(50, (int) $request->input('per_page', 20)));
    }
}
