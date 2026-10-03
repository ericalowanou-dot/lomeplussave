<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Dépenses de coins (1 coin = 1 jour), communes au site et à l'API mobile.
 */
class CoinService
{
    /**
     * Met l'annonce en avant pendant $days jours, à la suite d'un boost encore actif.
     *
     * @throws InsufficientCoinsException
     */
    public function boostArticle(User $user, Article $article, int $days): Article
    {
        return DB::transaction(function () use ($user, $article, $days) {
            $this->spend($user, $days);

            $start = $article->boosted_until && $article->boosted_until->isFuture()
                ? $article->boosted_until->copy()
                : now();
            $article->boosted_until = $start->addDays($days);
            $article->save();

            return $article;
        });
    }

    /**
     * Certifie le compte pendant $days jours, à la suite d'une certification encore active.
     *
     * @throws InsufficientCoinsException
     */
    public function certify(User $user, int $days): User
    {
        return DB::transaction(function () use ($user, $days) {
            $this->spend($user, $days);

            $start = $user->certifie_until && $user->certifie_until->isFuture()
                ? $user->certifie_until->copy()
                : now();
            $user->certifie_until = $start->addDays($days);
            $user->certifie = 1;
            $user->save();

            return $user;
        });
    }

    private function spend(User $user, int $amount): void
    {
        if (! $user->spendCoins($amount)) {
            throw new InsufficientCoinsException($amount);
        }
    }
}
