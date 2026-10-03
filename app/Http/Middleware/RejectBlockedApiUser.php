<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Équivalent API de CheckBlockedUser : un compte bloqué par l'admin perd
 * immédiatement l'accès à l'appli (ses jetons sont révoqués).
 */
class RejectBlockedApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBlocked()) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Votre compte a été bloqué. Raison : ' . ($user->block_reason ?? 'Non spécifiée'),
                'code' => 'account_blocked',
            ], 403);
        }

        return $next($request);
    }
}
