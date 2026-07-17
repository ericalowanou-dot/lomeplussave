<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur est administrateur (role === 'admin')
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent accéder à cette section.');
        }

        return $next($request);
    }
}
