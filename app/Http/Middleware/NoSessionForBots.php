<?php

namespace App\Http\Middleware;

use App\Services\StatTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les robots (Google, aperçus WhatsApp / Facebook…) ne gardent pas les cookies :
 * chacune de leurs visites créait une nouvelle session en base, conservée 30 jours.
 * Pour eux, la session reste en mémoire et n'est jamais enregistrée.
 *
 * Doit s'exécuter avant StartSession (ajouté en tête du groupe « web »).
 */
class NoSessionForBots
{
    public function handle(Request $request, Closure $next): Response
    {
        if (StatTracker::isBot($request->userAgent())) {
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }
}
