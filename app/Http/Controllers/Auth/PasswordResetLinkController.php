<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Vérifier si l'utilisateur existe et n'est pas bloqué
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user && $user->isBlocked()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce compte a été bloqué. Contactez l\'administrateur pour plus d\'informations.'])
                ->with('error_solutions', [
                    'Votre compte a été suspendu',
                    'Contactez-nous à : lomeplus80@gmail.com',
                    'Vérifiez vos emails pour connaître la raison du blocage'
                ]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Nous vous avons envoyé par email un lien sécurisé pour réinitialiser votre mot de passe. Vérifiez votre boîte de réception (et les spams).');
        } else {
            $solutions = [];
            if ($status == Password::INVALID_USER) {
                $solutions = [
                    'L\'adresse email n\'existe pas dans notre système',
                    'Vérifiez que vous avez bien saisi votre email',
                    'Créez un compte si vous n\'en avez pas encore'
                ];
            } else {
                $solutions = [
                    'Vérifiez que l\'email est correctement formaté (exemple@domaine.com)',
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous : lomeplus80@gmail.com'
                ];
            }
            
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)])
                ->with('error_solutions', $solutions);
        }
    }
}
