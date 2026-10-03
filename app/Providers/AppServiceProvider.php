<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use App\Models\Categorie; 
use App\Models\Article;
use App\Events\UserRegistered;
use App\Events\ArticlePending;
use App\Events\ProblemReportCreated;
use App\Events\UserReportCreated;
use App\Listeners\CreateAdminNotification;
use App\Listeners\CreateAdminNotificationForArticle;
use App\Listeners\CreateAdminNotificationForReport;
use App\Listeners\CreateAdminNotificationForUserReport;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Définir la locale de l'application en français
        App::setLocale('fr');
        Carbon::setLocale('fr');
        
        // Catégories disponibles dans toutes les vues (menus, en-têtes), chargées une seule
        // fois par requête et seulement si une vue est rendue (pas pour les réponses JSON).
        // Une valeur passée par le contrôleur reste prioritaire.
        // (L'ancien partage global de Article::all() a été retiré : chaque contrôleur passe
        // déjà ses $articles, et il chargeait toutes les annonces à chaque requête.)
        View::composer('*', function ($view) {
            if (array_key_exists('categories', $view->getData())) {
                return;
            }

            $view->with('categories', once(function () {
                try {
                    return Categorie::all();
                } catch (\Throwable $e) {
                    return collect(); // tables pas encore migrées
                }
            }));
        });

        Paginator::useBootstrap();

        Schema::defaultStringLength(191);

        // IDs des articles likés par l'utilisateur connecté (1 requête) pour cœur + count fiable
        view()->composer([
            'partials.articles-list',
            'partials.annonces-list',
            'partials.favoris-list',
            'products.search',
            'pages.boutique.show',
            'pages.detail_article',
        ], function ($view) {
            $view->with('likedIds', auth()->check()
                ? auth()->user()->favoris()->pluck('articles.id')->toArray()
                : []);
        });

        // API mobile : 120 requêtes/min par compte (ou par adresse IP sans connexion),
        // et 10 tentatives/min pour la connexion et l'inscription (anti force brute).
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(10)->by(strtolower((string) $request->input('email')) . '|' . $request->ip()));

        // Enregistrer les listeners pour les notifications admin
        Event::listen(UserRegistered::class, CreateAdminNotification::class);
        Event::listen(ArticlePending::class, CreateAdminNotificationForArticle::class);
        Event::listen(ProblemReportCreated::class, CreateAdminNotificationForReport::class);
        Event::listen(UserReportCreated::class, CreateAdminNotificationForUserReport::class);
    }
}
