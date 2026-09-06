<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-tetes de securite sans risque de casse (pas de CSP/COEP ici : le site charge
 * beaucoup de scripts inline et de ressources cross-origin CDN, une politique stricte
 * demande un audit dedie avant d'etre activee sans tout casser).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');

        // Symfony ne fait que retirer l'entete de son propre sac : PHP l'ajoute lui-meme
        // (expose_php) avant meme que ce middleware ne s'execute, il faut l'enlever explicitement.
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
