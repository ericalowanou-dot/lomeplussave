<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\MessageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de l'appli mobile — version 1 (préfixe /api/v1)
|--------------------------------------------------------------------------
| Même base de données, mêmes règles et même backoffice que le site.
| Authentification : jeton Sanctum (« Authorization: Bearer <jeton> »).
| Ne jamais modifier le comportement d'une route v1 publiée : les anciennes
| versions de l'appli restent installées sur les téléphones. Créer une v2.
*/

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {

    // ── Connexion ──────────────────────────────────────────────
    Route::middleware('throttle:api-auth')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    });

    // ── Public (le jeton est facultatif : s'il est envoyé, on reconnaît l'utilisateur) ──
    Route::middleware(\App\Http\Middleware\UseApiTokenIfPresent::class)->group(function () {
        Route::get('/categories', [CatalogController::class, 'categories'])->name('categories');
        Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/search', [ArticleController::class, 'search'])->name('articles.search');
        Route::get('/articles/{article}', [ArticleController::class, 'show'])->whereNumber('article')->name('articles.show');
        Route::get('/articles/{article}/comments', [CommentController::class, 'index'])->whereNumber('article')->name('comments.index');
        Route::get('/shops/{user}', [CatalogController::class, 'shop'])->whereNumber('user')->name('shops.show');
        Route::post('/reports', [CatalogController::class, 'reportProblem'])->name('reports.store');
    });

    // ── Utilisateur connecté ───────────────────────────────────
    Route::middleware(['auth:sanctum', 'api.not-blocked'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/auth/logout-all', [AuthController::class, 'logoutEverywhere'])->name('auth.logout-all');

        Route::get('/me', [AccountController::class, 'show'])->name('me.show');
        Route::match(['post', 'patch'], '/me', [AccountController::class, 'update'])->name('me.update');
        Route::delete('/me', [AccountController::class, 'destroy'])->name('me.destroy');
        Route::get('/me/articles', [AccountController::class, 'articles'])->name('me.articles');
        Route::get('/me/favorites', [AccountController::class, 'favorites'])->name('me.favorites');
        Route::post('/me/certification', [AccountController::class, 'certify'])->name('me.certify');

        Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
        Route::match(['post', 'put', 'patch'], '/articles/{article}', [ArticleController::class, 'update'])->whereNumber('article')->name('articles.update');
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->whereNumber('article')->name('articles.destroy');
        Route::post('/articles/{article}/like', [ArticleController::class, 'like'])->whereNumber('article')->name('articles.like');
        Route::post('/articles/{article}/boost', [ArticleController::class, 'boost'])->whereNumber('article')->name('articles.boost');

        Route::post('/articles/{article}/comments', [CommentController::class, 'store'])->whereNumber('article')->name('comments.store');
        Route::match(['put', 'patch'], '/comments/{comment}', [CommentController::class, 'update'])->whereNumber('comment')->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment')->name('comments.destroy');
        Route::post('/comments/{comment}/report', [CommentController::class, 'report'])->whereNumber('comment')->name('comments.report');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [MessageController::class, 'show'])->whereNumber('message')->name('messages.show');
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

        Route::post('/shops/{user}/report', [CatalogController::class, 'reportShop'])->whereNumber('user')->name('shops.report');
    });
});
