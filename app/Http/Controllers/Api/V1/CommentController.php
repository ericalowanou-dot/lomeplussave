<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    private const RULES = ['content' => ['required', 'string', 'min:3', 'max:1000']];

    public function index(Request $request, Article $article)
    {
        Gate::authorize('view', $article);

        $comments = $article->comments()
            ->with('user:id,name,photo_profil,certifie,certifie_from,certifie_until')
            ->paginate(max(1, min(50, (int) $request->input('per_page', 20))));

        return CommentResource::collection($comments);
    }

    public function store(Request $request, Article $article): JsonResponse
    {
        Gate::authorize('view', $article);
        $data = $request->validate(self::RULES);

        $comment = Comment::create([
            'content' => trim($data['content']),
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
        ]);

        return (new CommentResource($comment->load('user')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Comment $comment): CommentResource
    {
        Gate::authorize('update', $comment);
        $data = $request->validate(self::RULES);

        $comment->update(['content' => trim($data['content'])]);

        return new CommentResource($comment->load('user'));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé.']);
    }

    public function report(Request $request, Comment $comment): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        if (! $comment->reportBy($request->user(), $data['reason'] ?? null)) {
            return response()->json(['message' => 'Vous avez déjà signalé ce commentaire.', 'code' => 'already_reported'], 409);
        }

        return response()->json(['message' => 'Commentaire signalé. Merci pour votre vigilance.']);
    }
}
