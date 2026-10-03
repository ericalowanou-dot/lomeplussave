<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Qui peut voir et gérer une annonce. Utilisé par le site et par l'API.
 */
class ArticlePolicy
{
    /** Une annonce non validée n'existe que pour son vendeur et l'admin. */
    public function view(?User $user, Article $article): Response
    {
        if ($article->isApproved() || $this->ownsOrAdmin($user, $article)) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return ! $user->isBlocked();
    }

    public function update(User $user, Article $article): Response
    {
        return $this->owner($user, $article, 'Vous n\'avez pas l\'autorisation de modifier cet article.');
    }

    public function delete(User $user, Article $article): Response
    {
        return $this->owner($user, $article, 'Vous n\'avez pas l\'autorisation de supprimer cet article.');
    }

    public function transfer(User $user, Article $article): Response
    {
        return $this->owner($user, $article, 'Vous n\'avez pas l\'autorisation de transférer cet article.');
    }

    public function boost(User $user, Article $article): Response
    {
        return $this->owner($user, $article, 'Vous n\'avez pas l\'autorisation de booster cet article.');
    }

    private function owner(User $user, Article $article, string $message): Response
    {
        return (int) $user->id === (int) $article->user_id ? Response::allow() : Response::deny($message);
    }

    private function ownsOrAdmin(?User $user, Article $article): bool
    {
        return $user !== null && ((int) $user->id === (int) $article->user_id || $user->isAdmin());
    }
}
