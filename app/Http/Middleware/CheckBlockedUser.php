<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (Auth::check()) {
            $user = Auth::user();
            
            // Vérifier si l'utilisateur est bloqué
            if ($user->isBlocked()) {
                // Déconnecter l'utilisateur
                Auth::logout();
                
                // Invalider la session
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Rediriger vers la page de connexion avec un message d'erreur
                return redirect()->route('login')
                    ->with('error', 'Votre compte a été bloqué. Raison : ' . ($user->block_reason ?? 'Non spécifiée'));
            }
        }

        return $next($request);
    }
}
