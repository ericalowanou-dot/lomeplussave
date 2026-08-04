<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\Publicite;
use App\Models\Message;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Afficher le tableau de bord d'administration
     */
    public function dashboard()
    {
        $stats = Cache::remember('admin_dashboard_stats', 60, function () {
            return [
                'total_users' => User::count(),
                'blocked_users' => User::where('is_blocked', true)->count(),
                'total_articles' => Article::count(),
                'pending_articles' => Article::where('status', 'pending')->count(),
                'approved_articles' => Article::where('status', 'approved')->count(),
                'blocked_articles' => Article::where('status', 'blocked')->count(),
                'total_coins' => User::sum('coins'),
            ];
        });

        $recent_pending_articles = Article::with(['user', 'sousCategorie'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Utilisateurs récents
        $recent_users = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Si requête AJAX, retourner JSON
        if (request()->ajax()) {
            return response()->json([
                'stats' => $stats,
                'recent_pending_articles' => $recent_pending_articles->map(function($article) {
                    return [
                        'id' => $article->id,
                        'titre' => $article->titre,
                        'user_name' => $article->user->name ?? 'N/A',
                        'sous_categorie_nom' => $article->sousCategorie->nom ?? 'N/A',
                        'created_at' => $article->created_at ? $article->created_at->format('d/m/Y H:i') : 'N/A',
                        'url' => route('admin.articles.show', $article),
                    ];
                }),
                'recent_users' => $recent_users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_blocked' => $user->is_blocked,
                        'url' => route('admin.users.show', $user),
                    ];
                }),
            ]);
        }

        return view('admin.dashboard', compact('stats', 'recent_pending_articles', 'recent_users'));
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function users(Request $request)
    {
        $query = User::query()
            ->withCount([
                'articles',
                'reportsReceived',
                'reportsReceived as open_reports_received_count' => function ($q) {
                    $q->where('status', 'open');
                },
            ]);

        // Filtrage par statut
        if ($request->has('status')) {
            if ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($request->status === 'active') {
                $query->where('is_blocked', false);
            } elseif ($request->status === 'reported') {
                $query->has('reportsReceived');
            }
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->get('sort') === 'reports') {
            $query->orderByDesc('reports_received_count')->orderByDesc('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Bloquer un utilisateur
     */
    public function blockUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $user->block($request->reason);
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', 'Utilisateur bloqué avec succès.');
    }

    /**
     * Débloquer un utilisateur
     */
    public function unblockUser(User $user)
    {
        $user->unblock();
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', 'Utilisateur débloqué avec succès.');
    }

    /**
     * Ajouter des coins à un utilisateur
     */
    public function addCoins(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $user->addCoins((int) $request->amount);

        return redirect()->back()->with('success', $request->amount . ' coins ajoutés à l\'utilisateur.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(User $user)
    {
        // Supprimer d'abord tous les articles de l'utilisateur
        $user->articles()->delete();
        
        // Puis supprimer l'utilisateur
        $user->delete();

        return redirect()->back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Afficher la liste des articles
     */
    public function articles(Request $request)
    {
        $query = Article::with(['user', 'sousCategorie']);

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $articles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Approuver un article
     */
    public function approveArticle(Article $article)
    {
        $article->approve();
        $this->sendArticleNotification($article, 'approved');
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', 'Article approuvé avec succès.');
    }

    /**
     * Bloquer un article
     */
    public function blockArticle(Request $request, Article $article)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $article->block($request->reason);
        $this->sendArticleNotification($article, 'blocked', $request->reason);
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', 'Article bloqué avec succès.');
    }

    /**
     * Supprimer un article
     */
    public function deleteArticle(Article $article)
    {
        try {
            $article->delete();
            Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('success', 'Article supprimé avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de l\'article: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Impossible de supprimer l\'article pour le moment.')
                ->with('error_solutions', [
                    'Réessayez dans quelques instants',
                    'Vérifiez que l\'article n\'a pas de dépendances actives',
                    'Si le problème persiste, contactez le support technique'
                ]);
        }
    }

    /**
     * Afficher les détails d'un article
     */
    public function showArticle(Article $article)
    {
        $article->load(['user', 'sousCategorie', 'comments.user']);

        return view('admin.articles.show', compact('article'));
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function showUser(User $user)
    {
        $user->load(['articles' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        $user->loadCount([
            'reportsReceived',
            'reportsReceived as open_reports_received_count' => function ($q) {
                $q->where('status', 'open');
            },
        ]);

        $userReports = $user->reportsReceived()
            ->with('reporter:id,name,email,telephone')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        return view('admin.users.show', compact('user', 'userReports'));
    }

    /**
     * Mettre à jour le statut d'un signalement vendeur.
     */
    public function updateUserReportStatus(Request $request, UserReport $report)
    {
        $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        $report->status = $request->status;
        $report->save();

        return redirect()
            ->back()
            ->with('success', 'Statut du signalement mis à jour.');
    }

    /**
     * Approuver plusieurs articles en lot
     */
    public function bulkApproveArticles(Request $request)
    {
        $request->validate([
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:articles,id'
        ]);

        $articles = Article::whereIn('id', $request->article_ids)->get();
        
        foreach ($articles as $article) {
            $article->approve();
            $this->sendArticleNotification($article, 'approved');
        }
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', count($request->article_ids) . ' articles approuvés avec succès.');
    }

    /**
     * Bloquer plusieurs articles en lot
     */
    public function bulkBlockArticles(Request $request)
    {
        $request->validate([
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:articles,id',
            'reason' => 'nullable|string|max:500'
        ]);

        $articles = Article::whereIn('id', $request->article_ids)->get();
        
        foreach ($articles as $article) {
            $article->block($request->reason);
            $this->sendArticleNotification($article, 'blocked', $request->reason);
        }
        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('success', count($request->article_ids) . ' articles bloqués avec succès.');
    }

    /**
     * Envoyer une notification automatique au propriétaire d'un article
     */
    private function sendArticleNotification(Article $article, string $action, string $reason = null)
    {
        $user = $article->user;
        
        if (!$user) {
            return; // Pas d'utilisateur associé
        }

        $subject = '';
        $body = '';

        switch ($action) {
            case 'approved':
                $subject = 'Votre article a été approuvé';
                $body = "Bonjour {$user->name},\n\n";
                $body .= "Votre article \"{$article->titre}\" a été approuvé et est maintenant visible sur la plateforme.\n\n";
                $body .= "Vous pouvez le consulter à tout moment dans vos annonces.\n\n";
                $body .= "Merci d'utiliser notre plateforme !";
                break;
                
            case 'blocked':
                $subject = 'Votre article a été bloqué';
                $body = "Bonjour {$user->name},\n\n";
                $body .= "Votre article \"{$article->titre}\" a été bloqué par notre équipe de modération.\n\n";
                if ($reason) {
                    $body .= "Raison : {$reason}\n\n";
                }
                $body .= "Si vous pensez qu'il s'agit d'une erreur, vous pouvez nous contacter pour plus d'informations.\n\n";
                $body .= "Cordialement,\nL'équipe de modération";
                break;
        }

        // Créer le message
        $message = \App\Models\Message::create([
            'sender_id' => 1, // ID de l'administrateur système
            'subject' => $subject,
            'body' => $body,
        ]);

        // Ajouter le propriétaire comme destinataire
        $message->recipients()->attach($user->id);
    }

    /**
     * Afficher la liste des publicités
     */
    public function publicites(Request $request)
    {
        $query = Publicite::query();

        // Filtrage par position
        if ($request->has('position') && $request->position) {
            $query->where('position', $request->position);
        }

        // Filtrage par statut
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('lien_url', 'like', "%{$search}%");
            });
        }

        $publicites = $query->orderBy('ordre', 'asc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);

        return view('admin.publicites.index', compact('publicites'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function createPublicite()
    {
        return view('admin.publicites.create');
    }

    /**
     * Enregistrer une nouvelle publicité
     */
    public function storePublicite(Request $request)
    {
        try {
            $validated = $request->validate([
                'titre' => 'nullable|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
                'lien_url' => 'nullable|url|max:500',
                'position' => 'required|in:header,sidebar,footer,entre_articles,homepage_top,homepage_bottom',
                'apres_n_articles' => 'nullable|integer|min:1|max:100|required_if:position,entre_articles',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'is_active' => 'boolean',
                'ordre' => 'nullable|integer|min:0',
                'notes' => 'nullable|string|max:1000',
            ], [
                'image.required' => 'Veuillez sélectionner une image.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif ou webp.',
                'image.max' => 'L\'image ne doit pas dépasser 5MB.',
                'position.required' => 'Veuillez sélectionner une position.',
                'apres_n_articles.required_if' => 'Indiquez après combien d\'articles afficher cette publicité.',
                'apres_n_articles.min' => 'Le nombre d\'articles doit être au moins 1.',
                'lien_url.url' => 'L\'URL n\'est pas valide.',
            ]);

            // Gérer l'upload de l'image
            $destinationPath = public_path('advertisements');
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Générer un nom unique
            $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            
            // Déplacer l'image dans public/advertisements
            $request->file('image')->move($destinationPath, $filename);
            
            // Sauvegarder le chemin relatif
            $imagePath = 'advertisements/' . $filename;

            // Vérifier si is_active est coché (checkbox)
            // Les checkboxes en HTML envoient "on" si cochées, rien sinon
            // Donc si la clé existe, c'est que la checkbox est cochée
            $isActive = $request->has('is_active');
            
            $publicite = Publicite::create([
                'titre' => $request->titre ?? null,
                'image' => $imagePath,
                'lien_url' => $request->lien_url ?? null,
                'position' => $request->position,
                'apres_n_articles' => $request->position === 'entre_articles'
                    ? (int) $request->apres_n_articles
                    : null,
                'date_debut' => $request->date_debut ?? null,
                'date_fin' => $request->date_fin ?? null,
                'is_active' => $isActive,
                'ordre' => $request->ordre ?? 0,
                'notes' => $request->notes ?? null,
            ]);

            \Log::info('Publicité créée', [
                'id' => $publicite->id,
                'position' => $publicite->position,
                'is_active' => $publicite->is_active,
                'image' => $publicite->image,
                'request_is_active' => $request->has('is_active'),
                'request_all' => $request->all()
            ]);

            $message = 'Publicité créée avec succès !';
            if ($publicite->is_active) {
                $message .= ' Elle est active et visible sur le site.';
            } else {
                $message .= ' ⚠️ Attention : elle n\'est pas active. Cochez "Publicité active" pour qu\'elle s\'affiche sur le site.';
            }

            return redirect()->route('admin.publicites.index')
                            ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->errors())
                            ->with('error_solutions', [
                                'Vérifiez que l\'image est au format JPG, PNG, GIF ou WEBP',
                                'Assurez-vous que l\'image ne dépasse pas 5 Mo',
                                'Vérifiez que tous les champs obligatoires sont remplis',
                                'Si vous avez renseigné une URL, vérifiez qu\'elle est valide (commence par http:// ou https://)'
                            ])
                            ->withInput();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la publicité: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Erreur lors de la création de la publicité.')
                            ->with('error_solutions', [
                                'Vérifiez que l\'image est valide et ne dépasse pas 5 Mo',
                                'Vérifiez votre connexion Internet',
                                'Assurez-vous que le serveur a les permissions d\'écriture',
                                'Réessayez dans quelques instants'
                            ])
                            ->withInput();
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function editPublicite(Publicite $publicite)
    {
        return view('admin.publicites.edit', compact('publicite'));
    }

    /**
     * Mettre à jour une publicité
     */
    public function updatePublicite(Request $request, Publicite $publicite)
    {
        try {
            $request->validate([
                'titre' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'lien_url' => 'nullable|url|max:500',
                'position' => 'required|in:header,sidebar,footer,entre_articles,homepage_top,homepage_bottom',
                'apres_n_articles' => 'nullable|integer|min:1|max:100|required_if:position,entre_articles',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'is_active' => 'boolean',
                'ordre' => 'nullable|integer|min:0',
                'notes' => 'nullable|string|max:1000',
            ], [
                'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg, gif ou webp.',
                'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
                'lien_url.url' => 'L\'URL n\'est pas valide.',
                'position.required' => 'Veuillez sélectionner une position.',
                'apres_n_articles.required_if' => 'Indiquez après combien d\'articles afficher cette publicité.',
                'apres_n_articles.min' => 'Le nombre d\'articles doit être au moins 1.',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->errors())
                            ->with('error_solutions', [
                                'Vérifiez que l\'image est au format JPG, PNG, GIF ou WEBP',
                                'Assurez-vous que l\'image ne dépasse pas 5 Mo',
                                'Vérifiez que tous les champs obligatoires sont remplis',
                                'Si vous avez renseigné une URL, vérifiez qu\'elle est valide (commence par http:// ou https://)',
                                'Vérifiez que la date de fin est postérieure ou égale à la date de début'
                            ])
                            ->withInput();
        }

        try {
            $data = [
                'titre' => $request->titre,
                'lien_url' => $request->lien_url,
                'position' => $request->position,
                'apres_n_articles' => $request->position === 'entre_articles'
                    ? (int) $request->apres_n_articles
                    : null,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'is_active' => $request->has('is_active') ? true : false,
                'ordre' => $request->ordre ?? 0,
                'notes' => $request->notes,
            ];

            // Si nouvelle image, supprimer l'ancienne et uploader la nouvelle
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($publicite->image && file_exists(public_path($publicite->image))) {
                    unlink(public_path($publicite->image));
                }
                
                // Upload de la nouvelle image
                $destinationPath = public_path('advertisements');
                
                // Créer le dossier s'il n'existe pas
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Générer un nom unique
                $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                
                // Déplacer l'image dans public/advertisements
                $request->file('image')->move($destinationPath, $filename);
                
                // Sauvegarder le chemin relatif
                $data['image'] = 'advertisements/' . $filename;
            }

            $publicite->update($data);

            \Log::info('Publicité mise à jour', [
                'id' => $publicite->id,
                'position' => $publicite->position,
                'is_active' => $publicite->is_active,
                'image' => $publicite->image
            ]);

            $message = 'Publicité mise à jour avec succès !';
            if ($publicite->is_active) {
                $message .= ' Elle est active et visible sur le site.';
            } else {
                $message .= ' ⚠️ Attention : elle n\'est pas active. Cochez "Publicité active" pour qu\'elle s\'affiche sur le site.';
            }

            return redirect()->route('admin.publicites.index')
                            ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la publicité: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Erreur lors de la mise à jour de la publicité.')
                            ->with('error_solutions', [
                                'Vérifiez que l\'image est valide et ne dépasse pas 5 Mo',
                                'Vérifiez votre connexion Internet',
                                'Assurez-vous que le serveur a les permissions d\'écriture',
                                'Réessayez dans quelques instants'
                            ])
                            ->withInput();
        }
    }

    /**
     * Supprimer une publicité
     */
    public function deletePublicite(Publicite $publicite)
    {
        // Supprimer l'image associée
        if ($publicite->image && file_exists(public_path($publicite->image))) {
            unlink(public_path($publicite->image));
        }

        try {
            $publicite->delete();

            return redirect()->route('admin.publicites.index')
                            ->with('success', 'Publicité supprimée avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la publicité: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Impossible de supprimer la publicité pour le moment.')
                ->with('error_solutions', [
                    'Réessayez dans quelques instants',
                    'Vérifiez votre connexion Internet',
                    'Si le problème persiste, contactez le support technique'
                ]);
        }
    }

    /**
     * Toggle le statut actif/inactif
     */
    public function togglePublicite(Publicite $publicite)
    {
        $publicite->is_active = !$publicite->is_active;
        $publicite->save();

        return redirect()->back()->with('success', 
            'Statut de la publicité mis à jour : ' . ($publicite->is_active ? 'Active' : 'Inactive'));
    }

    /**
     * Page de test des publicités
     */
    public function testPublicites()
    {
        return view('admin.publicites.test');
    }

    /**
     * Tracker une vue de publicité
     */
    public function trackPubliciteView(Publicite $publicite)
    {
        $publicite->incrementViews();
        return response()->json(['success' => true]);
    }

    /**
     * Tracker un clic sur une publicité
     */
    public function trackPubliciteClick(Publicite $publicite)
    {
        $publicite->incrementClicks();
        return response()->json(['success' => true]);
    }

    /**
     * Afficher les messages reçus par l'admin
     */
    public function messagesInbox(Request $request)
    {
        $admin = $request->user();
        $filter = $request->get('filter');

        $query = Message::with(['sender', 'recipients'])
            ->whereHas('recipients', function($q) use ($admin, $filter) {
                $q->where('recipient_id', $admin->id);
                if ($filter === 'unread') {
                    $q->whereNull('read_at');
                } elseif ($filter === 'read') {
                    $q->whereNotNull('read_at');
                }
            })
            ->orderBy('created_at', 'desc');

        $messages = $query->paginate(20)->withQueryString();

        $unreadCount = DB::table('message_recipients')
            ->where('recipient_id', $admin->id)
            ->whereNull('read_at')
            ->count();

        $totalCount = Message::whereHas('recipients', function($q) use ($admin) {
            $q->where('recipient_id', $admin->id);
        })->count();

        $stats = [
            'total' => $totalCount,
            'unread' => $unreadCount,
            'read' => $totalCount - $unreadCount,
        ];

        return view('admin.messages.inbox', compact('messages', 'unreadCount', 'stats'));
    }

    /**
     * Afficher un message spécifique reçu par l'admin
     */
    public function showMessage(Request $request, Message $message)
    {
        $admin = $request->user();
        
        // Vérifier que l'admin est destinataire du message
        $isRecipient = $message->recipients()->where('recipient_id', $admin->id)->exists();
        
        if (!$isRecipient) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        // Marquer le message comme lu
        $message->recipients()->updateExistingPivot($admin->id, [
            'read_at' => now()
        ]);

        $message->load(['sender', 'recipients']);

        return view('admin.messages.show', compact('message'));
    }
}