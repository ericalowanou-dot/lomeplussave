<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de mettre à jour votre profil pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Assurez-vous que l\'email n\'est pas déjà utilisé',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants'
                ])
                ->withInput();
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ], [
                'password.required' => 'Veuillez confirmer votre mot de passe pour supprimer votre compte.',
                'password.current_password' => 'Le mot de passe est incorrect. Veuillez réessayer.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors(), 'userDeletion')
                ->with('error_solutions', [
                    'Vérifiez que vous avez bien saisi votre mot de passe actuel',
                    'Le mot de passe est nécessaire pour confirmer la suppression du compte',
                    'Si vous avez oublié votre mot de passe, utilisez la fonction "Mot de passe oublié"'
                ]);
        }

        try {
            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('success', 'Votre compte a été supprimé avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du compte: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de supprimer votre compte pour le moment.')
                ->with('error_solutions', [
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
                ]);
        }
    }
}
