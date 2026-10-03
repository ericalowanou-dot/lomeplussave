<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuse proprement (422) un texte qui n'est pas en UTF-8, au lieu d'une erreur 500
 * quand la base ou la réponse JSON tombent sur ces caractères.
 */
class RejectMalformedUtf8
{
    public function handle(Request $request, Closure $next): Response
    {
        $invalid = [];
        $input = $request->except(array_keys($request->allFiles()));
        array_walk_recursive($input, function ($value, $key) use (&$invalid) {
            if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                $invalid[] = mb_convert_encoding((string) $key, 'UTF-8', 'UTF-8');
            }
        });

        if ($invalid !== []) {
            return response()->json([
                'message' => 'Certains textes sont mal encodés. Envoyez les données en UTF-8.',
                'errors' => array_fill_keys($invalid, ['Texte mal encodé (UTF-8 attendu).']),
            ], 422);
        }

        return $next($request);
    }
}
