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
use App\Listeners\CreateAdminNotification;
use App\Listeners\CreateAdminNotificationForArticle;
use App\Listeners\CreateAdminNotificationForReport;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;

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
        
        // Vérifier si les tables existent avant d'y accéder
        try {
            if (Schema::hasTable('categories')) {
                $categories = Categorie::all();
                view()->share('categories', $categories);
            }
        } catch (\Exception $e) {
            // Les tables n'existent pas encore, ignorer silencieusement
        }

        try {
            if (Schema::hasTable('articles')) {
                $articles = Article::all();
                view()->share('articles', $articles);
            }
        } catch (\Exception $e) {
            // Les tables n'existent pas encore, ignorer silencieusement
        }

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

        // Enregistrer les listeners pour les notifications admin
        Event::listen(UserRegistered::class, CreateAdminNotification::class);
        Event::listen(ArticlePending::class, CreateAdminNotificationForArticle::class);
        Event::listen(ProblemReportCreated::class, CreateAdminNotificationForReport::class);
    }
}
