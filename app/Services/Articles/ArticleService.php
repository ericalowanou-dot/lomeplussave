<?php

namespace App\Services\Articles;

use App\Events\ArticlePending;
use App\Models\Article;
use App\Models\User;
use App\Services\AdminMailNotifier;
use App\Services\MediaStorage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Règles métier des annonces, communes au site et à l'API mobile.
 * Les contrôleurs valident la requête et vérifient les droits ; tout le reste est ici.
 */
class ArticleService
{
    /**
     * Publie une annonce, en attente de validation par l'admin.
     *
     * @param  array<string, mixed>  $attributes  Champs validés (voir ArticleRequest::articleAttributes)
     * @param  list<UploadedFile>  $photos
     *
     * @throws PhotoStorageException si une photo ne peut pas être enregistrée
     */
    public function publish(User $seller, array $attributes, array $photos): Article
    {
        $paths = $this->storePhotos($photos);

        try {
            return DB::transaction(function () use ($seller, $attributes, $paths) {
                $article = new Article();
                $article->forceFill($attributes);
                foreach (array_values($paths) as $index => $path) {
                    $article->{self::photoField($index)} = $path;
                }
                $article->user_id = $seller->id;
                $article->status = 'pending';
                $article->save();

                event(new ArticlePending($article));
                AdminMailNotifier::articleCreated($article, $seller);

                return $article;
            });
        } catch (\Throwable $e) {
            $this->discard($paths);
            throw $e;
        }
    }

    /**
     * Modifie une annonce. Une annonce déjà validée ou bloquée repasse en validation.
     * Les nouvelles photos remplacent les premières dans l'ordre ; les anciennes ne sont
     * effacées qu'une fois la modification bien enregistrée.
     *
     * @param  list<UploadedFile>  $photos
     * @return bool Vrai si l'annonce repasse en validation.
     */
    public function update(Article $article, User $seller, array $attributes, array $photos = []): bool
    {
        $paths = $this->storePhotos($photos);
        $replaced = [];

        try {
            $needsReview = DB::transaction(function () use ($article, $seller, $attributes, $paths, &$replaced) {
                $article->forceFill($attributes);

                foreach (array_values($paths) as $index => $path) {
                    $field = self::photoField($index);
                    if ($article->$field) {
                        $replaced[] = $article->$field;
                    }
                    $article->$field = $path;
                }

                // Remodération anti-fraude : approved/blocked → pending, sans toucher created_at
                $needsReview = in_array($article->status, ['approved', 'blocked'], true);
                if ($needsReview) {
                    $article->submitForReview();
                }

                $changedFields = array_keys($article->getDirty());
                $article->save();

                AdminMailNotifier::articleUpdated($article, $seller, $changedFields, $needsReview);

                return $needsReview;
            });
        } catch (\Throwable $e) {
            $this->discard($paths);
            $article->refresh();
            throw $e;
        }

        $article->deleteUnusedPhotoFiles($replaced);

        return $needsReview;
    }

    /** Supprime l'annonce ; ses photos sont effacées après validation de la suppression. */
    public function delete(Article $article): void
    {
        $article->delete();
    }

    public function transfer(Article $article, User $newOwner): void
    {
        $article->user_id = $newOwner->id;
        $article->save();
    }

    /**
     * Ajoute ou retire le like de l'utilisateur.
     *
     * @return array{liked: bool, likeCount: int}
     */
    public function toggleLike(Article $article, User $user): array
    {
        if ($article->usersWhoLiked()->where('user_id', $user->id)->exists()) {
            $article->usersWhoLiked()->detach($user->id);
            $liked = false;
        } else {
            try {
                $article->usersWhoLiked()->attach($user->id);
            } catch (UniqueConstraintViolationException) {
                // Double clic : l'autre requête a déjà enregistré ce like (index unique)
            }
            $liked = true;
        }

        return ['liked' => $liked, 'likeCount' => $article->usersWhoLiked()->count()];
    }

    public static function photoField(int $index): string
    {
        return $index === 0 ? 'photo' : 'photo' . $index;
    }

    /**
     * @param  list<UploadedFile>  $photos
     * @return list<string>
     */
    private function storePhotos(array $photos): array
    {
        $paths = [];

        try {
            foreach (array_slice(array_values($photos), 0, 6) as $photo) {
                $paths[] = MediaStorage::storeImage($photo, 'articles', 'article');
            }
        } catch (\Throwable $e) {
            $this->discard($paths);
            throw new PhotoStorageException($e->getMessage(), 0, $e);
        }

        return $paths;
    }

    /** @param  list<string>  $paths */
    private function discard(array $paths): void
    {
        foreach ($paths as $path) {
            MediaStorage::delete($path);
        }
    }
}
