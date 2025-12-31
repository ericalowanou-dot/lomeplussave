<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class UserController extends Controller
{
public function updateAjax(Request $request)
{
    $user = auth()->user();

    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif ou webp.',
            'photo.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors(),
            'solutions' => [
                'Vérifiez que le nom est rempli et ne dépasse pas 255 caractères',
                'Assurez-vous que l\'image est au bon format (JPG, PNG, GIF, WEBP)',
                'Vérifiez que l\'image ne dépasse pas 2 Mo'
            ]
        ], 422);
    }

    try {
        $user->name = $request->name;

        // 📌 Vérifie si l'utilisateur a uploadé une nouvelle photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->photo_profil && file_exists(public_path($user->photo_profil))) {
                unlink(public_path($user->photo_profil));
            }

            // Chemin du dossier public/users/profil
            $destinationPath = public_path('users/profil');

            // Créer le dossier s'il n'existe pas
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Générer un nom unique
            $filename = time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();

            // Optimiser et compresser l'image avant de la sauvegarder
            $imageOptimizer = new \App\Services\ImageOptimizer();
            if (!$imageOptimizer->optimizeProfileImage($request->file('photo'), $destinationPath, $filename)) {
                // Si l'optimisation échoue, sauvegarder l'image originale
                $request->file('photo')->move($destinationPath, $filename);
            }

            // Mettre à jour le champ photo_profil dans la base avec le chemin relatif
            $user->photo_profil = 'users/profil/' . $filename;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'name' => $user->name,
            'photo' => $user->getProfilPhotoUrl()
        ]);
    } catch (\Exception $e) {
        \Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Impossible de mettre à jour votre profil pour le moment.',
            'solutions' => [
                'Vérifiez votre connexion Internet',
                'Assurez-vous que l\'image ne dépasse pas 2 Mo',
                'Réessayez dans quelques instants'
            ]
        ], 500);
    }
}

public function spendCoinsForBoost(Request $request, Article $article)
{
    try {
        $request->validate([
            'days' => 'required|integer|min:1'
        ], [
            'days.required' => 'Le nombre de jours est obligatoire.',
            'days.integer' => 'Le nombre de jours doit être un nombre entier.',
            'days.min' => 'Le nombre de jours doit être au moins 1.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Données invalides.',
            'errors' => $e->errors(),
            'solutions' => [
                'Vérifiez que le nombre de jours est un nombre entier positif',
                'Le nombre de jours doit être au moins 1'
            ]
        ], 422);
    }

    try {
        $user = auth()->user();
        if (!$user || $article->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas l\'autorisation de booster cet article.',
                'solutions' => [
                    'Assurez-vous d\'être connecté',
                    'Vérifiez que vous êtes le propriétaire de cet article'
                ]
            ], 403);
        }

        $cost = (int) $request->days; // 1 coin = 1 jour
        if (!$user->hasCoins($cost)) {
            return response()->json([
                'success' => false,
                'message' => 'Solde de coins insuffisant.',
                'solutions' => [
                    'Vous avez besoin de ' . $cost . ' coins pour ' . $cost . ' jour(s)',
                    'Achetez plus de coins pour continuer',
                    'Contactez-nous pour obtenir des coins : lomeplus80@gmail.com'
                ]
            ], 400);
        }

        $user->spendCoins($cost);

        $current = $article->boosted_until ? $article->boosted_until->copy() : now();
        if ($article->boosted_until && $article->boosted_until->isFuture()) {
            $current = $article->boosted_until->copy();
        }
        $article->boosted_until = $current->addDays((int) $request->days);
        $article->save();

        return response()->json(['success' => true, 'boosted_until' => $article->boosted_until->toDateTimeString(), 'coins' => $user->coins]);
    } catch (\Exception $e) {
        \Log::error('Erreur lors du boost de l\'article: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Impossible de booster l\'article pour le moment.',
            'solutions' => [
                'Vérifiez votre connexion Internet',
                'Réessayez dans quelques instants',
                'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
            ]
        ], 500);
    }
}

public function spendCoinsForCertification(Request $request)
{
    try {
        $request->validate([
            'days' => 'required|integer|min:1'
        ], [
            'days.required' => 'Le nombre de jours est obligatoire.',
            'days.integer' => 'Le nombre de jours doit être un nombre entier.',
            'days.min' => 'Le nombre de jours doit être au moins 1.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Données invalides.',
            'errors' => $e->errors(),
            'solutions' => [
                'Vérifiez que le nombre de jours est un nombre entier positif',
                'Le nombre de jours doit être au moins 1'
            ]
        ], 422);
    }

    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour obtenir la certification.',
                'solutions' => [
                    'Connectez-vous à votre compte',
                    'Créez un compte si vous n\'en avez pas encore'
                ]
            ], 403);
        }

        $cost = (int) $request->days; // 1 coin = 1 jour
        if (!$user->hasCoins($cost)) {
            return response()->json([
                'success' => false,
                'message' => 'Solde de coins insuffisant.',
                'solutions' => [
                    'Vous avez besoin de ' . $cost . ' coins pour ' . $cost . ' jour(s)',
                    'Achetez plus de coins pour continuer',
                    'Contactez-nous pour obtenir des coins : lomeplus80@gmail.com'
                ]
            ], 400);
        }

        $user->spendCoins($cost);

        $current = $user->certifie_until ? $user->certifie_until->copy() : now();
        if ($user->certifie_until && $user->certifie_until->isFuture()) {
            $current = $user->certifie_until->copy();
        }
        $user->certifie_until = $current->addDays((int) $request->days);
        $user->certifie = 1;
        $user->save();

        return response()->json(['success' => true, 'certifie_until' => optional($user->certifie_until)->toDateTimeString(), 'coins' => $user->coins]);
    } catch (\Exception $e) {
        \Log::error('Erreur lors de la certification: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Impossible d\'obtenir la certification pour le moment.',
            'solutions' => [
                'Vérifiez votre connexion Internet',
                'Réessayez dans quelques instants',
                'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
            ]
        ], 500);
    }
}

public function myArticles()
{
    $user = auth()->user();
    if (!$user) {
        return response()->json([], 401);
    }
    $articles = Article::where('user_id', $user->id)
        ->where('status', 'approved')
        ->orderBy('created_at', 'desc')
        ->get(['id','titre']);

    return response()->json($articles);
}

public function getUserInfo()
{
    $user = auth()->user();
    if (!$user) {
        return response()->json([], 401);
    }
    
    return response()->json([
        'coins' => $user->coins ?? 0,
        'certifie_until' => $user->certifie_until ? $user->certifie_until->toDateTimeString() : null,
        'certifie' => $user->certifie ?? 0
    ]);
}


}
