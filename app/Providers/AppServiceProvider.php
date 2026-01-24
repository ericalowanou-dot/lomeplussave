<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use App\Models\Categorie; 
use App\Models\Article; 
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
    }
}
