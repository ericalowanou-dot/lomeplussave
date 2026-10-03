<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return (int) $user->id === (int) $comment->user_id;
    }

    /** L'auteur, ou l'admin pour la modération. */
    public function delete(User $user, Comment $comment): bool
    {
        return (int) $user->id === (int) $comment->user_id || $user->isAdmin();
    }
}
