<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Validation\Rules; // Commenté - validation simplifiée à 6 caractères minimum
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, AccountService $accounts): RedirectResponse
    {
        try {
            $request->validate(AccountService::registrationRules() + [
                'phone_full' => ['required', 'string', 'max:20'],
                'whatsapp_full' => ['required', 'string', 'max:20'],
            ], AccountService::registrationMessages() + [
                'phone_full.required' => 'Le numéro de téléphone est obligatoire.',
                'whatsapp_full.required' => 'Le numéro WhatsApp est obligatoire.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Assurez-vous que l\'email n\'est pas déjà utilisé',
                    'Le mot de passe doit contenir au moins 6 caractères',
                    'Vérifiez que les numéros de téléphone sont valides'
                ])
                ->withInput();
        }

        try {
            $user = $accounts->register($request->name, $request->email, $request->password, $request->phone_full, $request->whatsapp_full);

            Auth::login($user);

            // Vérifier s'il y a un paramètre redirect dans l'URL
            $redirectUrl = $request->input('redirect');
            if ($redirectUrl) {
                $decodedUrl = urldecode($redirectUrl);
                // Vérifier que l'URL est valide et du même domaine pour la sécurité
                if (filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($decodedUrl);
                    $appHost = $request->getHost();
                    if ($parsedUrl && isset($parsedUrl['host']) && $parsedUrl['host'] === $appHost) {
                        return redirect($decodedUrl);
                    }
                }
            }

            return redirect(route('articles.index', absolute: false))->with('success', 'Votre compte a été créé avec succès ! Bienvenue sur Lome+.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible de créer votre compte pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
                ])
                ->withInput();
        }
    }
}
