<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Enregistre les vues d'annonces, visites de boutiques et recherches
 * pour la page Statistiques de l'admin.
 *
 * Règles : robots, admins et propriétaires exclus ; un même visiteur
 * n'est compté qu'une fois par heure et par élément. Le suivi ne doit
 * jamais casser une page : toute erreur est seulement journalisée.
 */
class StatTracker
{
    /** Fenêtre de dédoublonnage des visites (secondes). */
    private const VISIT_WINDOW = 3600;

    /** Délai pendant lequel une saisie progressive est fusionnée (secondes). */
    private const SEARCH_MERGE_WINDOW = 60;

    private const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|headless|lighthouse|pingdom|monitor/i';

    public function recordArticleView(Article $article, Request $request): void
    {
        $this->safely(function () use ($article, $request) {
            if (! $this->shouldTrack($request, (int) $article->user_id)) {
                return;
            }

            $this->insertVisit('article', $article->id, (int) $article->user_id, $request);
        });
    }

    public function recordShopVisit(User $vendeur, Request $request): void
    {
        $this->safely(function () use ($vendeur, $request) {
            if (! $this->shouldTrack($request, (int) $vendeur->id)) {
                return;
            }

            $this->insertVisit('boutique', null, (int) $vendeur->id, $request);
        });
    }

    /**
     * Enregistre une recherche. Les frappes successives d'une recherche en direct
     * (« ord », « ordi », « ordinateur ») sont fusionnées dans une seule ligne.
     */
    public function recordSearch(string $terme, int $resultats, Request $request, string $source): void
    {
        $this->safely(function () use ($terme, $resultats, $request, $source) {
            $terme = Str::limit(trim(preg_replace('/\s+/u', ' ', $terme)), 250, '');
            $normalise = self::normalize($terme);

            if (mb_strlen($normalise) < 2 || ! $this->shouldTrack($request, null)) {
                return;
            }

            $normalise = Str::limit($normalise, 191, '');
            $hash = $this->visitorHash($request);
            $now = now();

            // Dernière recherche de ce visiteur, si elle est toute récente
            $last = DB::table('stat_recherches')
                ->where('visiteur_hash', $hash)
                ->where('updated_at', '>=', $now->copy()->subSeconds(self::SEARCH_MERGE_WINDOW))
                ->orderByDesc('updated_at')
                ->first(['id', 'terme_normalise']);

            if ($last) {
                if ($last->terme_normalise === $normalise) {
                    return; // même recherche répétée (pagination, rechargement)
                }

                if (str_starts_with($normalise, $last->terme_normalise) || str_starts_with($last->terme_normalise, $normalise)) {
                    DB::table('stat_recherches')->where('id', $last->id)->update([
                        'terme' => $terme,
                        'terme_normalise' => $normalise,
                        'resultats' => $resultats,
                        'updated_at' => $now,
                    ]);

                    return;
                }
            }

            DB::table('stat_recherches')->insert([
                'terme' => $terme,
                'terme_normalise' => $normalise,
                'resultats' => $resultats,
                'source' => $source,
                'user_id' => $request->user()?->id,
                'visiteur_hash' => $hash,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    /**
     * Forme de regroupement d'un texte : minuscules, sans accents, espaces simples.
     */
    public static function normalize(string $value): string
    {
        $value = Str::lower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim($value);
    }

    private function insertVisit(string $type, ?int $articleId, int $vendeurId, Request $request): void
    {
        $hash = $this->visitorHash($request);

        // Déjà compté dans l'heure ? (requête sur la table elle-même : pas de clés
        // de cache qui s'accumuleraient avec le cache en base de données)
        $alreadyCounted = DB::table('stat_visites')
            ->where('visiteur_hash', $hash)
            ->where('created_at', '>=', now()->subSeconds(self::VISIT_WINDOW))
            ->where('type', $type)
            ->when($articleId !== null,
                fn ($q) => $q->where('article_id', $articleId),
                fn ($q) => $q->where('vendeur_id', $vendeurId))
            ->exists();

        if ($alreadyCounted) {
            return;
        }

        DB::table('stat_visites')->insert([
            'type' => $type,
            'article_id' => $articleId,
            'vendeur_id' => $vendeurId,
            'visiteur_id' => $request->user()?->id,
            'visiteur_hash' => $hash,
            'created_at' => now(),
        ]);
    }

    /**
     * Détection STRICTE des robots, pour les décisions qui peuvent gêner un vrai visiteur
     * (ex. ne pas enregistrer de session). Seuls des robots connus ou déclarés comme tels :
     * « bot » seul ne suffit pas (téléphones Cubot), ni « Telegram » (navigateur intégré).
     */
    private const CRAWLER_PATTERN = '/googlebot|bingbot|yandex(bot|images)|baiduspider|duckduckbot|slurp|applebot|petalbot|ahrefsbot|semrushbot|mj12bot|dotbot|bytespider|gptbot|claudebot|facebookexternalhit|facebookcatalog|meta-externalagent|twitterbot|linkedinbot|pinterestbot|whatsapp\/|telegrambot|discordbot|slackbot|skypeuripreview|uptimerobot|pingdom|headlesschrome|lighthouse|curl\/|wget\/|python-requests|go-http-client|compatible;[^)]*bot|\+https?:\/\//i';

    public static function isCrawler(?string $userAgent): bool
    {
        $userAgent = (string) $userAgent;

        return $userAgent === '' || (bool) preg_match(self::CRAWLER_PATTERN, $userAgent);
    }

    /**
     * Détection LARGE (robot, aperçu de lien, script…) : pour les statistiques seulement,
     * où se tromper sur un vrai visiteur a pour seul effet de ne pas compter sa visite.
     */
    public static function isBot(?string $userAgent): bool
    {
        $userAgent = (string) $userAgent;

        return $userAgent === '' || (bool) preg_match(self::BOT_PATTERN, $userAgent);
    }

    private function shouldTrack(Request $request, ?int $ownerId): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if (self::isBot($request->userAgent())) {
            return false;
        }

        $user = $request->user();
        if ($user && ($user->isAdmin() || ($ownerId !== null && (int) $user->id === $ownerId))) {
            return false;
        }

        return true;
    }

    private function visitorHash(Request $request): string
    {
        if ($user = $request->user()) {
            $identity = 'u:' . $user->id;
        } elseif ($request->hasSession() && $request->session()->getId()) {
            $identity = 's:' . $request->session()->getId();
        } else {
            $identity = 'ip:' . $request->ip() . '|' . $request->userAgent();
        }

        return hash('sha256', $identity . '|' . config('app.key'));
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('StatTracker : ' . $e->getMessage());
        }
    }
}
