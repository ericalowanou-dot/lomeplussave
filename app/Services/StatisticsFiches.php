<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Fiches statistiques d'une annonce et d'un vendeur (boutique), sur la période choisie.
 * Les filtres Ville / Catégorie ne s'appliquent pas ici : la fiche concerne un seul élément.
 */
class StatisticsFiches
{
    private const PER_PAGE = 20;

    public function __construct(private AdminStatistics $stats, private Request $request)
    {
        $this->stats->detectTrackingTables();
    }

    // ------------------------------------------------------------------
    // Annonce
    // ------------------------------------------------------------------

    public function annonce(Article $article): array
    {
        $article->loadMissing(['user', 'sousCategorie.categorie']);
        $period = $this->stats->period();
        $previous = [$this->stats->previousFrom, $this->stats->previousTo];

        $visits = fn () => DB::table('stat_visites')->where('type', 'article')->where('article_id', $article->id);
        $likes = fn () => DB::table('article_user_like')->where('article_id', $article->id);

        $vues = $vuesPrev = $visiteurs = 0;
        if ($this->stats->hasVisites()) {
            $vues = $visits()->whereBetween('created_at', $period)->count();
            $vuesPrev = $visits()->whereBetween('created_at', $previous)->count();
            $visiteurs = $visits()->whereBetween('created_at', $period)->distinct()->count('visiteur_hash');
        }

        $likesPeriode = $this->stats->likedDuring($likes(), $period, true)->count();
        $likesPrev = $likes()->whereBetween('article_user_like.created_at', $previous)->count();
        $likesTotal = $likes()->count();

        // Comparaison avec les autres annonces de la même sous-catégorie
        $comparaison = $this->compareInSousCategorie($article, $vues);

        $buckets = $this->stats->buckets();
        $series = [
            'labels' => array_values(array_column($buckets, 'label')),
            'vues' => $this->stats->hasVisites()
                ? $this->stats->bucketize($this->stats->dailyCounts($visits(), 'stat_visites.created_at'), $buckets)
                : array_fill(0, count($buckets), 0),
            'likes' => $this->stats->bucketize($this->stats->dailyCounts($likes(), 'article_user_like.created_at'), $buckets),
        ];

        $fans = DB::table('article_user_like')
            ->join('users', 'users.id', '=', 'article_user_like.user_id')
            ->where('article_user_like.article_id', $article->id)
            ->select('users.id', 'article_user_like.created_at as aime_le')
            ->orderByRaw('article_user_like.created_at IS NULL')
            ->orderByDesc('article_user_like.created_at')
            ->paginate(self::PER_PAGE, ['*'], 'fans_page')
            ->withQueryString();
        $fanUsers = User::whereIn('id', collect($fans->items())->pluck('id'))->get()->keyBy('id');

        return [
            'article' => $article,
            'colonnes' => $this->stats->articleColumns($article, $article->id),
            'kpis' => [
                'vues' => $this->stats->metric($vues, $vuesPrev),
                'visiteurs' => $visiteurs,
                'likes' => $this->stats->metric($likesPeriode, $likesPrev),
                'likes_total' => $likesTotal,
                'taux' => $this->stats->percent($likesPeriode, $vues),
            ],
            'comparaison' => $comparaison,
            'series' => $series,
            'fans' => $fans,
            'fan_users' => $fanUsers,
        ];
    }

    private function compareInSousCategorie(Article $article, int $vues): ?array
    {
        if (! $article->sous_categorie_id || ! $this->stats->hasVisites()) {
            return null;
        }

        $ids = DB::table('articles')
            ->where('sous_categorie_id', $article->sous_categorie_id)
            ->where('status', 'approved')
            ->pluck('id');

        if ($ids->count() < 2) {
            return null;
        }

        $perArticle = DB::table('stat_visites')
            ->where('type', 'article')
            ->whereIn('article_id', $ids)
            ->whereBetween('created_at', $this->stats->period())
            ->selectRaw('article_id, COUNT(*) as vues')
            ->groupBy('article_id')
            ->pluck('vues', 'article_id');

        $moyenne = $perArticle->sum() / $ids->count();
        $rang = $perArticle->filter(fn ($v) => $v > $vues)->count() + 1;

        return [
            'sous_categorie' => $article->sousCategorie?->nom,
            'moyenne' => round($moyenne, 1),
            'rang' => $rang,
            'total' => $ids->count(),
            'ecart' => $moyenne > 0 ? round(($vues - $moyenne) / $moyenne * 100) : null,
        ];
    }

    // ------------------------------------------------------------------
    // Vendeur / boutique
    // ------------------------------------------------------------------

    public function vendeur(User $user): array
    {
        $period = $this->stats->period();
        $previous = [$this->stats->previousFrom, $this->stats->previousTo];

        $visits = fn (string $type) => DB::table('stat_visites')->where('type', $type)->where('vendeur_id', $user->id);
        $likes = fn () => DB::table('article_user_like')
            ->join('articles', 'articles.id', '=', 'article_user_like.article_id')
            ->where('articles.user_id', $user->id);
        $articles = fn () => DB::table('articles')->where('user_id', $user->id);

        $kpis = [
            'visites_boutique' => $this->stats->metric(0, 0),
            'vues_annonces' => $this->stats->metric(0, 0),
            'visiteurs' => 0,
        ];

        if ($this->stats->hasVisites()) {
            $kpis['visites_boutique'] = $this->stats->metric(
                $visits('boutique')->whereBetween('created_at', $period)->count(),
                $visits('boutique')->whereBetween('created_at', $previous)->count(),
            );
            $kpis['vues_annonces'] = $this->stats->metric(
                $visits('article')->whereBetween('created_at', $period)->count(),
                $visits('article')->whereBetween('created_at', $previous)->count(),
            );
            $kpis['visiteurs'] = DB::table('stat_visites')->where('vendeur_id', $user->id)
                ->whereBetween('created_at', $period)->distinct()->count('visiteur_hash');
        }

        $kpis += [
            'likes' => $this->stats->metric(
                $this->stats->likedDuring($likes(), $period, true)->count(),
                $likes()->whereBetween('article_user_like.created_at', $previous)->count(),
            ),
            'likes_total' => $likes()->count(),
            'annonces' => $this->stats->metric(
                $articles()->whereBetween('created_at', $period)->count(),
                $articles()->whereBetween('created_at', $previous)->count(),
            ),
            'annonces_total' => $articles()->count(),
            'annonces_en_ligne' => $articles()->where('status', 'approved')->count(),
        ];

        $buckets = $this->stats->buckets();
        $zero = array_fill(0, count($buckets), 0);
        $series = [
            'labels' => array_values(array_column($buckets, 'label')),
            'visites_boutique' => $this->stats->hasVisites()
                ? $this->stats->bucketize($this->stats->dailyCounts($visits('boutique'), 'stat_visites.created_at'), $buckets) : $zero,
            'vues_annonces' => $this->stats->hasVisites()
                ? $this->stats->bucketize($this->stats->dailyCounts($visits('article'), 'stat_visites.created_at'), $buckets) : $zero,
            'likes' => $this->stats->bucketize($this->stats->dailyCounts($likes(), 'article_user_like.created_at'), $buckets),
        ];

        // Ses annonces, avec leur audience sur la période
        $sorts = [
            'vues' => 'vues',
            'likes' => 'likes',
            'publie_le' => 'articles.created_at',
            'prix' => 'articles.prix_ht',
        ];
        $sort = array_key_exists($this->request->query('tri'), $sorts) ? $this->request->query('tri') : 'vues';
        $direction = $this->request->query('sens') === 'asc' ? 'asc' : 'desc';

        $list = DB::table('articles')->where('articles.user_id', $user->id)->select('articles.id');
        if ($this->stats->hasVisites()) {
            $list->selectSub(fn (Builder $q) => $q->from('stat_visites')->selectRaw('COUNT(*)')
                ->whereColumn('stat_visites.article_id', 'articles.id')
                ->where('stat_visites.type', 'article')
                ->whereBetween('stat_visites.created_at', $period), 'vues');
        } else {
            $list->selectRaw('0 as vues');
        }
        $list->selectSub(fn (Builder $q) => $q->from('article_user_like')->selectRaw('COUNT(*)')
            ->whereColumn('article_user_like.article_id', 'articles.id'), 'likes');

        $annonces = $list->orderBy($sorts[$sort], $direction)
            ->orderByDesc('articles.id')
            ->paginate(self::PER_PAGE, ['*'], 'annonces_page')
            ->withQueryString();
        $models = $this->stats->loadArticles(collect($annonces->items())->pluck('id')->all());

        $best = null;
        if ($this->stats->hasVisites()) {
            $bestRow = DB::table('stat_visites')->where('type', 'article')->where('vendeur_id', $user->id)
                ->whereBetween('created_at', $period)->whereNotNull('article_id')
                ->selectRaw('article_id, COUNT(*) as vues')->groupBy('article_id')->orderByDesc('vues')->first();
            if ($bestRow) {
                $bestModel = Article::with(['user:id,name', 'sousCategorie.categorie'])->find($bestRow->article_id);
                $best = $this->stats->articleColumns($bestModel, (int) $bestRow->article_id) + ['vues' => (int) $bestRow->vues];
            }
        }

        return [
            'user' => $user,
            'colonnes' => $this->stats->userColumns($user, $user->id),
            'kpis' => $kpis,
            'series' => $series,
            'annonces' => $annonces,
            'modeles' => $models,
            'tri' => $sort,
            'sens' => $direction,
            'meilleure' => $best,
        ];
    }
}
