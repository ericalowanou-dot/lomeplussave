<?php

namespace App\Http\Controllers;

use App\Events\UserReportCreated;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserShopController extends Controller
{
    /**
     * URL SEO : /boutique/{slug-nom}-{id}
     */
    public function show(string $slugId): View|RedirectResponse
    {
        if (! preg_match('/-(\d+)$/', $slugId, $matches)) {
            abort(404);
        }

        $user = User::findOrFail((int) $matches[1]);

        $canonical = $user->shopRouteParameters()['slugId'];
        if ($slugId !== $canonical) {
            return redirect()->route('boutique.show', ['slugId' => $canonical], 301);
        }

        return $this->renderShop($user);
    }

    /**
     * Ancienne URL /boutique/{id} → redirection 301.
     */
    public function showLegacy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        return redirect($user->shopUrl(), 301);
    }

    protected function renderShop(User $user): View
    {
        $articles = $user->articles()
            ->where('status', 'approved')
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id', 'sousCategorie.categorie:id,nom'])
            ->latest()
            ->paginate(24);

        $hasReportedShop = false;
        if (auth()->check() && Schema::hasTable('user_reports')) {
            $hasReportedShop = UserReport::where('reporter_id', auth()->id())
                ->where('reported_user_id', $user->id)
                ->exists();
        }

        $reportReasons = UserReport::REASONS;

        return view('pages.boutique.show', compact('user', 'articles', 'hasReportedShop', 'reportReasons'));
    }

    /**
     * Signaler un vendeur depuis sa page boutique (JSON).
     */
    public function report(Request $request, User $user)
    {
        if (! Schema::hasTable('user_reports')) {
            return response()->json([
                'success' => false,
                'message' => 'Le signalement n\'est pas encore disponible. Réessayez plus tard.',
            ], 503);
        }

        $reporter = $request->user();

        if ($reporter->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas signaler votre propre boutique.',
            ], 422);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette boutique ne peut pas être signalée.',
            ], 422);
        }

        if ($user->isBlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce vendeur est déjà bloqué sur la plateforme.',
            ], 422);
        }

        try {
            $validated = $request->validate([
                'reason' => ['required', 'string', Rule::in(array_keys(UserReport::REASONS))],
                'message' => ['nullable', 'string', 'max:500'],
            ], [
                'reason.required' => 'Veuillez choisir un motif.',
                'reason.in' => 'Le motif sélectionné est invalide.',
                'message.max' => 'Le message ne peut pas dépasser 500 caractères.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Veuillez corriger le formulaire.',
            ], 422);
        }

        $alreadyReported = UserReport::where('reporter_id', $reporter->id)
            ->where('reported_user_id', $user->id)
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà signalé cette boutique.',
            ], 422);
        }

        try {
            $report = UserReport::create([
                'reporter_id' => $reporter->id,
                'reported_user_id' => $user->id,
                'reason' => $validated['reason'],
                'message' => $validated['message'] ?? null,
                'status' => 'open',
            ]);

            $report->load(['reporter:id,name', 'reportedUser:id,name']);

            try {
                event(new UserReportCreated($report));
            } catch (\Throwable $e) {
                \Log::warning('Notification admin non envoyée après signalement vendeur: ' . $e->getMessage(), [
                    'report_id' => $report->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Signalement envoyé. Merci, notre équipe va examiner cette boutique.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur signalement boutique: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'envoyer le signalement pour le moment.',
            ], 500);
        }
    }
}
