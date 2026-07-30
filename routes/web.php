<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategorieController;
use App\Models\User;
use App\Models\Article;
use App\Http\Controllers\SousCategorieController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\DetailArticleController;
use App\Http\Controllers\UserShopController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AboutController;

use Illuminate\Http\Request; //test wireshark


Route::middleware(['auth', 'check.blocked'])->group(function(){
    Route::post('/user/update', [UserController::class, 'updateAjax'])->name('user.update.ajax');
    Route::post('/user/boost/{article}', [UserController::class, 'spendCoinsForBoost'])->name('user.boost');
    Route::post('/user/certify', [UserController::class, 'spendCoinsForCertification'])->name('user.certify');
    Route::get('/user/my-articles', [UserController::class, 'myArticles'])->name('user.my-articles');
    Route::get('/user/info', [UserController::class, 'getUserInfo'])->name('user.info');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::post('/boutique/{user}/signaler', [UserShopController::class, 'report'])
        ->name('boutique.report')
        ->whereNumber('user');
    // messages
    Route::get('/messages', [MessageController::class, 'inbox'])->name('messages.inbox');
    Route::get('/messages/compose', [MessageController::class, 'compose'])->name('messages.compose');
    Route::get('/messages/compose/{message}', [MessageController::class, 'compose'])->name('messages.compose.reply');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
});

// Routes publiques pour le tracking des publicités
Route::post('/publicite/{publicite}/view', [AdminController::class, 'trackPubliciteView'])->name('publicite.view');
Route::post('/publicite/{publicite}/click', [AdminController::class, 'trackPubliciteClick'])->name('publicite.click');


// pour les articles
Route::get('/', [ArticleController::class, 'index'])->name('articles.index'); // Lister tous les articles sur la page d'accueil

// URL SEO professionnelle des annonces
Route::get('/annonce/{categorie}/{sousCategorie}/{slugId}', [DetailArticleController::class, 'show'])
    ->name('article.details')
    ->where([
        'categorie' => '[a-z0-9\-]+',
        'sousCategorie' => '[a-z0-9\-]+',
        'slugId' => '[a-z0-9\-]+\-\d+',
    ]);

// Ancienne URL /article/{id} → redirection 301 vers l'URL SEO
Route::get('/article/{id}', [DetailArticleController::class, 'showLegacy'])
    ->name('article.details.legacy')
    ->whereNumber('id');

Route::get('/search', [ArticleController::class, 'search'])->name('article.search'); // Pour la recherche d'articles


Route::get('/search/articles', [AnnonceController::class, 'searchArticles'])->name('search.articles');



Route::middleware(['auth', 'check.blocked'])->group(function(){
    Route::post('/articles/{article}/like', [ArticleController::class, 'toggleLike'])->name('articles.like'); // Pour aimer ou ne pas aimer un article
    Route::post('/articles/{article}/comments', [CommentController::class, 'store'])->name('comments.store'); // Pour ajouter un commentaire à un article
    Route::get('/articles/{article}/comments/load-more', [CommentController::class, 'loadMore'])->name('comments.loadMore'); // Pour charger plus de commentaires
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update'); // Pour modifier un commentaire
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy'); // Pour supprimer un commentaire
    Route::post('/comments/{comment}/report', [CommentController::class, 'report'])->name('comments.report'); // Pour signaler un commentaire
    Route::get('/mes-favoris', [AnnonceController::class, 'mesFavoris'])->name('mes_favoris'); // Pour voir les articles aimés par l'utilisateur
    Route::get('/search/favoris', [AnnonceController::class, 'searchFavoris'])->name('search.favoris');
    Route::get('/search/mesannonces', [AnnonceController::class, 'searchMesAnnonces'])->name('search.mesannonces');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create'); //pour afficher le formulaire de création d'article
    
    //Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store'); //pour enregistrer un nouvel article dans la bdd
    Route::post('/annonce/sauvegarder', [ArticleController::class, 'store'])->name('articles.store');
    Route::post('/send', [ArticleController::class, 'store'])->name('articles.send'); // Alias de création d'article

    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    Route::get('/articles/{article}/transfer', [ArticleController::class, 'transfer'])->name('articles.transfer');
    Route::post('/articles/{article}/transfer', [ArticleController::class, 'doTransfer'])->name('articles.doTransfer');
    
    Route::get('/mes-annonces', [AnnonceController::class, 'index'])->name('mes_annonces'); // Pour qu'un vendeur voit ses annonces
    Route::get('/annonce/{id}', [AnnonceController::class, 'show'])->name('annonce.show');
});
require __DIR__.'/auth.php'; 


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check.blocked'])->name('dashboard');

Route::middleware(['auth', 'check.blocked'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});





// Gestion des catégories / sous-catégories (admin uniquement — routes legacy)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/categorie/store', [CategorieController::class, 'index'])->name('Categorie.store');
    Route::post('/categories', [CategorieController::class, 'store'])->name('categories.create');
    Route::post('/sous-categories', [SousCategorieController::class, 'store'])->name('sous_categories.store');

    Route::get('/category-list', [CategorieController::class, 'listes_categries'])->name('categories-liste');
    Route::post('/store-categorie', [CategorieController::class, 'store'])->name('categories.store');
    Route::get('/update-categorie/{id}', [CategorieController::class, 'update'])->name('categories.edit');
    Route::delete('/delete-categorie/{id}', [CategorieController::class, 'destroy'])->name('categories.destroy');
});

// routes pour recuperer les categories et sous-categories dans le modal de creation d'article 
Route::get('/categories', [CategorieController::class, 'getCategories']);
Route::get('/sous-categories/{id}', [CategorieController::class, 'getSousCategories']);
  

// pour afficher les articles lorsqu'on scroll sur les categories
Route::get('/categories/{id}/subcategories', [CategorieController::class, 'getSubcategories']);




Route::get('/articlesParCategorie', [CategorieController::class, 'articleParSubcategorie'])->name('articles.sub'); //pour récupérer les articles par sous catégorie

Route::get('/categories-list', [CategorieController::class, 'getCategories'])->name('categories.list');

Route::get('/paginate', [AnnonceController::class, 'maPaginnation']);

// URL SEO boutique : /boutique/{slug-nom}-{id}
Route::get('/boutique/{slugId}', [UserShopController::class, 'show'])
    ->name('boutique.show')
    ->where('slugId', '[a-z0-9\-]+\-\d+');

// Ancienne URL /boutique/{id} → redirection 301
Route::get('/boutique/{id}', [UserShopController::class, 'showLegacy'])
    ->name('boutique.show.legacy')
    ->whereNumber('id');

Route::get('/modal', function(){
    return view('modale');
});



// Page "À propos de nous"
Route::get('/a-propos', [AboutController::class, 'index'])->name('about');



// pour tout ce qui concerne les categories
// Route::resource('categories', CategorieController::class)->parameters([
//     'categories' => 'categorie'
// ]);



// Route::resource('souscategories', SousCategorieController::class);






// Routes pour la gestion des catégories
// Route::get('/categories', [CategorieController::class, 'index'])->name('categories.index');
// Route::post('/categories', [CategorieController::class, 'store'])->name('categories.store');
// Route::put('/categories/{id}', [CategorieController::class, 'update'])->name('categories.update');
// Route::delete('/categories/{id}', [CategorieController::class, 'destroy'])->name('categories.destroy');

// Routes d'administration
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des utilisateurs
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{user}/block', [AdminController::class, 'blockUser'])->name('users.block');
    Route::post('/users/{user}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Gestion des articles
    Route::get('/articles', [AdminController::class, 'articles'])->name('articles.index');
    // Routes POST et DELETE avant GET pour éviter les conflits de routing
    Route::post('/articles/{article}/approve', [AdminController::class, 'approveArticle'])->name('articles.approve');
    Route::post('/articles/{article}/block', [AdminController::class, 'blockArticle'])->name('articles.block');
    Route::delete('/articles/{article}', [AdminController::class, 'deleteArticle'])->name('articles.delete');
    Route::get('/articles/{article}', [AdminController::class, 'showArticle'])->name('articles.show');
    
    // Actions en lot
    Route::post('/articles/bulk-approve', [AdminController::class, 'bulkApproveArticles'])->name('articles.bulk-approve');
    Route::post('/articles/bulk-block', [AdminController::class, 'bulkBlockArticles'])->name('articles.bulk-block');
    Route::post('/users/{user}/coins', [AdminController::class, 'addCoins'])->name('users.add-coins');
    Route::post('/user-reports/{report}/status', [AdminController::class, 'updateUserReportStatus'])->name('user-reports.update-status');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{report}/status', [ReportController::class, 'updateStatus'])->name('reports.update-status');
    // admin messaging
    Route::get('/messages/inbox', [AdminController::class, 'messagesInbox'])->name('messages.inbox');
    Route::get('/messages/compose', [MessageController::class, 'adminCompose'])->name('messages.compose');
    Route::get('/messages/users', [MessageController::class, 'getUsers'])->name('messages.users');
    Route::get('/messages/{message}', [AdminController::class, 'showMessage'])->name('messages.show');
    Route::post('/messages/send', [MessageController::class, 'adminSend'])->name('messages.send');

    // API Notifications
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'unread'])->name('notifications.unread');
        Route::get('/notifications/count', [\App\Http\Controllers\Api\NotificationController::class, 'count'])->name('notifications.count');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });

    // Gestion des catégories (admin)
    Route::get('/categories', [CategorieController::class, 'adminIndex'])->name('categories.index');
    Route::post('/categories', [CategorieController::class, 'store'])->name('categories.store');
    Route::put('/categories/{categorie}', [CategorieController::class, 'adminUpdate'])->name('categories.update');
    Route::delete('/categories/{categorie}', [CategorieController::class, 'destroy'])->name('categories.destroy');

    // Gestion des sous-catégories (admin)
    Route::get('/souscategories', [SousCategorieController::class, 'index'])->name('souscategories.index');
    Route::get('/souscategories/{sousCategorie}/edit', [SousCategorieController::class, 'edit'])->name('souscategories.edit');
    Route::post('/souscategories', [SousCategorieController::class, 'store'])->name('souscategories.store');
    Route::put('/souscategories/{sousCategorie}', [SousCategorieController::class, 'update'])->name('souscategories.update');
    Route::delete('/souscategories/{sousCategorie}', [SousCategorieController::class, 'destroy'])->name('souscategories.destroy');

    // Gestion des publicités (admin)
    Route::get('/publicites', [AdminController::class, 'publicites'])->name('publicites.index');
    Route::get('/publicites/create', [AdminController::class, 'createPublicite'])->name('publicites.create');
    Route::post('/publicites', [AdminController::class, 'storePublicite'])->name('publicites.store');
    Route::get('/publicites/{publicite}/edit', [AdminController::class, 'editPublicite'])->name('publicites.edit');
    Route::put('/publicites/{publicite}', [AdminController::class, 'updatePublicite'])->name('publicites.update');
    Route::delete('/publicites/{publicite}', [AdminController::class, 'deletePublicite'])->name('publicites.delete');
    Route::post('/publicites/{publicite}/toggle', [AdminController::class, 'togglePublicite'])->name('publicites.toggle');
    Route::get('/publicites/test', [AdminController::class, 'testPublicites'])->name('publicites.test');
});

