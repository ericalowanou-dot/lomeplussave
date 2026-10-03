<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{

    protected $fillable = [
        'content',
        'article_id', 
        'user_id']; // Les champs remplissables

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Signale le commentaire (une seule fois par utilisateur).
     *
     * @return bool Faux si cet utilisateur l'avait déjà signalé.
     */
    public function reportBy(User $user, ?string $reason = null): bool
    {
        try {
            DB::table('comment_reports')->insert([
                'comment_id' => $this->id,
                'user_id' => $user->id,
                'reason' => mb_substr(trim((string) $reason), 0, 255) ?: 'Contenu inapproprié',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return false; // index unique (comment_id, user_id)
        }

        return true;
    }


}
