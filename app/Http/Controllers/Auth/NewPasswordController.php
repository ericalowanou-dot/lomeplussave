<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Vérifier si l'utilisateur est bloqué avant de réinitialiser
        $user = User::where('email', $request->email)->first();
        
        if ($user && $user->isBlocked()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce compte a été bloqué. Vous ne pouvez pas réinitialiser votre mot de passe.'])
                ->with('error_solutions', [
                    'Votre compte a été suspendu',
                    'Contactez-nous à : lomeplus80@gmail.com',
                    'Vous devez d\'abord débloquer votre compte'
                ]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $resetError = null;
        
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request, &$resetError) {
                // Vérifier à nouveau avant de modifier le mot de passe
                if ($user->isBlocked()) {
                    $resetError = 'blocked';
                    return;
                }
                
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
        
        // Si l'utilisateur était bloqué dans la callback, retourner l'erreur
        if ($resetError === 'blocked') {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce compte a été bloqué. Vous ne pouvez pas réinitialiser votre mot de passe.'])
                ->with('error_solutions', [
                    'Votre compte a été suspendu',
                    'Contactez-nous à : lomeplus80@gmail.com',
                    'Vous devez d\'abord débloquer votre compte'
                ]);
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', trans('passwords.reset'));
        } else {
            $solutions = [];
            if ($status == Password::INVALID_TOKEN) {
                $solutions = [
                    'Le lien de réinitialisation a expiré ou est invalide',
                    'Demandez un nouveau lien de réinitialisation',
                    'Vérifiez que vous utilisez le dernier lien reçu par email'
                ];
            } elseif ($status == Password::INVALID_USER) {
                $solutions = [
                    'L\'adresse email n\'existe pas dans notre système',
                    'Vérifiez que vous avez bien saisi votre email',
                    'Créez un compte si vous n\'en avez pas encore'
                ];
            } else {
                $solutions = [
                    'Vérifiez que tous les champs sont correctement remplis',
                    'Le mot de passe doit contenir au moins 6 caractères',
                    'Assurez-vous que les deux champs de mot de passe correspondent'
                ];
            }
            
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)])
                ->with('error_solutions', $solutions);
        }
    }
}
