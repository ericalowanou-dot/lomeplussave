<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ProblemReportCreated;
use App\Events\UserReportCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\CategorieResource;
use App\Http\Resources\SellerResource;
use App\Models\Categorie;
use App\Models\ProblemReport;
use App\Models\User;
use App\Models\UserReport;
use App\Services\Articles\ArticleSearch;
use App\Services\StatTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Catégories, boutiques et signalements.
 */
class CatalogController extends Controller
{
    public function categories()
    {
        // Même cache que le site (vidé quand l'admin modifie une catégorie)
        $categories = Cache::remember('categories_with_souscategories', 3600, fn () => Categorie::with('sousCategories')->get());

        return CategorieResource::collection($categories);
    }

    /** Boutique d'un vendeur : profil public et annonces en ligne. */
    public function shop(Request $request, User $user)
    {
        if ($user->isBlocked()) {
            abort(404);
        }

        app(StatTracker::class)->recordShopVisit($user, $request);

        $articles = $user->articles()
            ->where('status', 'approved')
            ->select(ArticleSearch::CARD_COLUMNS)
            ->withLikeCounts()
            ->with(ArticleSearch::CARD_RELATIONS)
            ->latest()
            ->paginate(max(1, min(50, (int) $request->input('per_page', 20))));

        return ArticleResource::collection(ArticleResource::markLikedBy($articles, $request->user()))->additional([
            'vendeur' => new SellerResource($user),
            'motifs_signalement' => UserReport::REASONS,
        ]);
    }

    public function reportShop(Request $request, User $user): JsonResponse
    {
        $reporter = $request->user();

        if ((int) $reporter->id === (int) $user->id || $user->isAdmin() || $user->isBlocked()) {
            return response()->json(['message' => 'Cette boutique ne peut pas être signalée.'], 422);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', Rule::in(array_keys(UserReport::REASONS))],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        if (UserReport::where('reporter_id', $reporter->id)->where('reported_user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà signalé cette boutique.', 'code' => 'already_reported'], 409);
        }

        $report = UserReport::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $user->id,
            'reason' => $data['reason'],
            'message' => $data['message'] ?? null,
            'status' => 'open',
        ]);

        rescue(fn () => event(new UserReportCreated($report->load(['reporter:id,name', 'reportedUser:id,name']))));

        return response()->json(['message' => 'Signalement envoyé. Merci, notre équipe va examiner cette boutique.'], 201);
    }

    /** Signaler un problème dans l'appli (visible dans le backoffice comme ceux du site). */
    public function reportProblem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'subject' => ['nullable', 'string', 'max:150'],
        ]);

        $report = ProblemReport::create([
            'user_id' => $request->user()?->id,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'open',
        ]);

        rescue(fn () => event(new ProblemReportCreated($report)));

        return response()->json(['message' => 'Signalement envoyé avec succès.'], 201);
    }
}
