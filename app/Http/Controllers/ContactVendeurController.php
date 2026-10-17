<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Services\StatTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Reçoit, en arrière-plan, les clics « Appeler » et « WhatsApp » des pages annonce
 * et boutique. Le lien lui-même s'ouvre directement : ce suivi ne doit jamais
 * retarder ni empêcher le contact.
 */
class ContactVendeurController extends Controller
{
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'canal' => ['required', Rule::in(StatTracker::CONTACT_CHANNELS)],
            'vendeur_id' => ['required', 'integer'],
            'article_id' => ['nullable', 'integer'],
        ]);

        $vendeur = User::find($data['vendeur_id']);
        $article = isset($data['article_id']) ? Article::find($data['article_id']) : null;

        // Annonce inconnue ou d'un autre vendeur : on ignore sans erreur
        if ($vendeur && (! isset($data['article_id']) || ($article && (int) $article->user_id === (int) $vendeur->id))) {
            app(StatTracker::class)->recordContact($vendeur, $article, $data['canal'], 'site', $request);
        }

        return response()->noContent();
    }
}
