<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\MeResource;
use App\Models\Article;
use App\Services\Articles\ArticleSearch;
use App\Services\CoinService;
use App\Services\InsufficientCoinsException;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Le compte de l'utilisateur connecté : profil, mes annonces, favoris, certification.
 */
class AccountController extends Controller
{
    public function show(Request $request, MessageService $messages): JsonResponse
    {
        return response()->json([
            'data' => new MeResource($request->user()),
            'messages_non_lus' => $messages->unreadCount($request->user()),
        ]);
    }

    /** Multipart si une photo est envoyée (POST /me avec le champ « photo »). */
    public function update(Request $request): MeResource
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'telephone' => ['sometimes', 'required', 'string', 'max:20'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format JPG, PNG, GIF ou WEBP.',
            'photo.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        $user = $request->user();
        $user->fill(collect($data)->only(['name', 'telephone', 'whatsapp'])->all());
        $user->save();

        if ($request->hasFile('photo')) {
            $user->replaceProfilePhoto($request->file('photo'));
        }

        return new MeResource($user->fresh());
    }

    /**
     * Suppression du compte depuis l'appli (exigée par l'App Store et Google Play).
     * Mêmes effets que sur le site : annonces et photos supprimées.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'current_password:sanctum']], [
            'password.required' => 'Veuillez confirmer votre mot de passe pour supprimer votre compte.',
            'password.current_password' => 'Le mot de passe est incorrect. Veuillez réessayer.',
        ]);

        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Votre compte a été supprimé.']);
    }

    /** Mes annonces, tous statuts confondus, avec les compteurs. */
    public function articles(Request $request)
    {
        $request->validate(['status' => ['nullable', 'in:pending,approved,blocked']]);
        $user = $request->user();

        $articles = Article::where('user_id', $user->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->select(array_merge(ArticleSearch::CARD_COLUMNS, ['block_reason']))
            ->withLikeCounts($user->id)
            ->with(ArticleSearch::CARD_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage($request))
            ->withQueryString();

        $stats = Article::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return ArticleResource::collection(ArticleResource::markLikedBy($articles, $user))->additional([
            'stats' => [
                'total' => (int) $stats->sum(),
                'pending' => (int) ($stats['pending'] ?? 0),
                'approved' => (int) ($stats['approved'] ?? 0),
                'blocked' => (int) ($stats['blocked'] ?? 0),
            ],
        ]);
    }

    public function favorites(Request $request)
    {
        $user = $request->user();

        $favoris = $user->favoris()
            ->where('articles.status', 'approved')
            ->select(array_map(fn ($c) => 'articles.' . $c, ArticleSearch::CARD_COLUMNS))
            ->withLikeCounts($user->id)
            ->with(ArticleSearch::CARD_RELATIONS)
            ->orderBy('article_user_like.created_at', 'desc')
            ->paginate($this->perPage($request));

        $favoris->getCollection()->each(fn ($article) => $article->setAttribute('liked_by_me_count', 1));

        return ArticleResource::collection($favoris);
    }

    public function certify(Request $request, CoinService $coins): JsonResponse
    {
        $data = $request->validate(['days' => ['required', 'integer', 'min:1', 'max:3650']]);

        try {
            $user = $coins->certify($request->user(), (int) $data['days']);
        } catch (InsufficientCoinsException $e) {
            return response()->json([
                'message' => 'Solde de coins insuffisant : il faut ' . $e->needed . ' coins pour ' . $e->needed . ' jour(s).',
                'code' => 'insufficient_coins',
            ], 422);
        }

        return response()->json(['data' => new MeResource($user->fresh())]);
    }

    private function perPage(Request $request): int
    {
        return max(1, min(50, (int) $request->input('per_page', 20)));
    }
}
