<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;



class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);

        $comment = new Comment();
        $comment->content = trim($request->content);
        $comment->user_id = auth()->id();
        $comment->article_id = $article->id;
        $comment->save();
        
        $comment->load('user');

        // Retourne une réponse JSON avec le commentaire ajouté
        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'created_at_full' => $comment->created_at->format('d/m/Y à H:i'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->getProfilPhotoUrl(),
                    'is_certified' => $comment->user->estCertifie(),
                ]
            ],
            'message' => 'Commentaire ajouté avec succès'
        ]);
    }
    
    public function destroy(Comment $comment)
    {
        if (Gate::denies('delete', $comment)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }
        
        $comment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Commentaire supprimé avec succès'
        ]);
    }
    
    public function update(Request $request, Comment $comment)
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }
        
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);
        
        $comment->content = trim($request->content);
        $comment->save();
        $comment->load('user');
        
        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'created_at_full' => $comment->created_at->format('d/m/Y à H:i'),
                'updated_at' => $comment->updated_at->diffForHumans(),
            ],
            'message' => 'Commentaire modifié avec succès'
        ]);
    }

    public function report(Request $request, Comment $comment)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Vous devez être connecté pour signaler un commentaire'], 401);
        }

        if (! $comment->reportBy($request->user(), $request->input('reason'))) {
            return response()->json(['success' => false, 'message' => 'Vous avez déjà signalé ce commentaire'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Commentaire signalé avec succès. Merci pour votre vigilance.'
        ]);
    }

    public function loadMore(Request $request, Article $article)
    {
        $offset = $request->input('offset', 10);
        $limit = $request->input('limit', 10);

        $comments = $article->comments()
            ->with('user:id,name,photo_profil,certifie')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $commentsData = $comments->map(function($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'created_at_full' => $comment->created_at->format('d/m/Y à H:i'),
                'updated_at' => $comment->updated_at ? $comment->updated_at->diffForHumans() : null,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->getProfilPhotoUrl(),
                    'is_certified' => $comment->user->estCertifie(),
                ],
                'is_owner' => auth()->check() && auth()->id() === $comment->user_id,
            ];
        });

        return response()->json([
            'success' => true,
            'comments' => $commentsData,
            'has_more' => $article->comments()->count() > ($offset + $limit),
        ]);
    }

    

}

