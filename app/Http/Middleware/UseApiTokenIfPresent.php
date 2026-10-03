<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages publiques de l'API : si l'appli envoie un jeton, on reconnaît l'utilisateur
 * (pour « liké par moi », ses annonces en attente…), sans l'exiger.
 */
class UseApiTokenIfPresent
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('sanctum');

        return $next($request);
    }
}
