<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.blocked' => \App\Http\Middleware\CheckBlockedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
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
