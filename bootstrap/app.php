<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Avant StartSession : pas de session enregistrée pour les robots
        $middleware->web(prepend: [
            \App\Http\Middleware\NoSessionForBots::class,
        ]);

        // API mobile : texte mal encodé refusé proprement (422) plutôt qu'une erreur 500
        $middleware->api(prepend: [
            \App\Http\Middleware\RejectMalformedUtf8::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.blocked' => \App\Http\Middleware\CheckBlockedUser::class,
            'api.not-blocked' => \App\Http\Middleware\RejectBlockedApiUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // L'API mobile répond toujours en JSON, même en cas d'erreur
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $jsonClientError = function ($request, string $message, array $errors, int $status) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'errors' => $errors,
                ], $status);
            }

            return null;
        };

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) use ($jsonClientError) {
            return $jsonClientError(
                $request,
                'Les photos sont trop volumineuses pour le serveur. Réduisez le nombre ou la taille des images, puis réessayez.',
                ['photos' => ['Fichier trop volumineux pour le serveur.']],
                413
            );
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) use ($jsonClientError) {
            return $jsonClientError(
                $request,
                'Votre session a expiré. Rechargez la page, reconnectez-vous si besoin, puis réessayez.',
                ['general' => ['Session expirée.']],
                419
            );
        });
    })->create();
