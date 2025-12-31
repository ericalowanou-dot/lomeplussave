<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rules\Password; // Commenté - validation simplifiée à 6 caractères minimum

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            // Validation simplifiée : seulement 6 caractères minimum
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ], [
                'current_password.required' => 'Veuillez saisir votre mot de passe actuel.',
                'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
                'password.required' => 'Veuillez saisir un nouveau mot de passe.',
                'password.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères. Veuillez ajouter des caractères pour atteindre 6 caractères minimum.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors(), 'updatePassword')
                ->with('error_solutions', [
                    'Vérifiez que vous avez bien saisi votre mot de passe actuel',
                    'Le nouveau mot de passe doit contenir au moins 6 caractères',
                    'Assurez-vous que les deux champs de mot de passe correspondent',
                    'Vérifiez que le nouveau mot de passe respecte les règles (majuscule, minuscule, chiffre, caractère spécial)'
                ]);
        }

        try {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return back()->with('status', 'password-updated');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du mot de passe: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour votre mot de passe pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
                ]);
        }
    }
}
