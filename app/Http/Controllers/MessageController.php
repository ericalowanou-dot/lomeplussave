<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    public function inbox(Request $request)
    {
        $user = $request->user();
        $messages = Message::with('sender')
            ->whereHas('recipients', function($q) use ($user){
                $q->where('recipient_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('messages.inbox', compact('messages'));
    }

    /**
     * Afficher un message spécifique
     */
    public function show(Request $request, Message $message)
    {
        $user = $request->user();
        
        // Vérifier que l'utilisateur est destinataire du message
        $isRecipient = $message->recipients()->where('recipient_id', $user->id)->exists();
        
        if (!$isRecipient && $message->sender_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        // Marquer le message comme lu si l'utilisateur est destinataire
        if ($isRecipient) {
            $message->recipients()->updateExistingPivot($user->id, [
                'read_at' => now()
            ]);
        }

        $message->load(['sender', 'recipients']);

        return view('messages.show', compact('message'));
    }

    public function compose(Request $request, $message = null)
    {
        // Si c'est une réponse à un message, charger le message parent
        $parentMessage = null;
        if ($message) {
            $parentMessage = Message::findOrFail($message);
            
            // Vérifier que l'utilisateur peut répondre à ce message
            $user = $request->user();
            $isRecipient = $parentMessage->recipients()->where('recipient_id', $user->id)->exists();
            if (!$isRecipient && $parentMessage->sender_id !== $user->id && !$user->isAdmin()) {
                abort(403, 'Vous ne pouvez pas répondre à ce message.');
            }
        }
        
        return view('messages.compose', ['message' => $parentMessage]);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        
        // Vérifier si c'est une réponse
        $parentMessage = null;
        if ($request->has('parent_message_id')) {
            $parentMessage = Message::findOrFail($request->parent_message_id);
            
            // Vérifier que l'utilisateur peut répondre à ce message
            $isRecipient = $parentMessage->recipients()->where('recipient_id', $user->id)->exists();
            if (!$isRecipient && $parentMessage->sender_id !== $user->id) {
                abort(403, 'Vous ne pouvez pas répondre à ce message.');
            }
        }

        // Validation du message
        $request->validate([
            'body' => 'required|string|min:2',
        ], [
            'body.required' => 'Le message est obligatoire.',
            'body.min' => 'Le message doit contenir au moins 2 caractères.',
        ]);

        // Trouver l'admin - plusieurs critères
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $admin = User::where('email', 'like', '%admin%')->first();
        }
        
        if (!$admin) {
            $admin = User::where('email', 'lomeplus80@gmail.com')->first();
        }
        
        if (!$admin) {
            $admin = User::find(1);
        }

        if (!$admin) {
            return back()
                ->with('error', 'Impossible de trouver l\'administrateur.')
                ->with('error_solutions', [
                    'Contactez-nous directement par email : lomeplus80@gmail.com',
                    'Essayez de rafraîchir la page et réessayez',
                    'Vérifiez votre connexion Internet'
                ])
                ->withInput();
        }

        try {
            $message = Message::create([
                'sender_id' => $user->id,
                'subject' => $parentMessage ? 'Re: ' . ($parentMessage->subject ?? 'Message') : null,
                'body' => $request->body,
                'parent_message_id' => $parentMessage ? $parentMessage->id : null,
                'is_group_message' => false,
            ]);

            $message->recipients()->sync([$admin->id]);

            return redirect()->route('messages.inbox')->with('success', 'Message envoyé à l\'administrateur.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du message: ' . $e->getMessage());
            return back()
                ->with('error', 'Impossible d\'envoyer le message pour le moment.')
                ->with('error_solutions', [
                    'Vérifiez votre connexion Internet',
                    'Réessayez dans quelques instants',
                    'Si le problème persiste, contactez-nous par email : lomeplus80@gmail.com'
                ])
                ->withInput();
        }
    }

    // Admin bulk send (with filters later)
    public function adminCompose()
    {
        return view('admin.messages.compose');
    }

    public function adminSend(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'body' => 'required|string|min:2',
            'recipient_scope' => 'required|string',
        ]);

        $query = User::query();
        
        // Appliquer les filtres selon le scope
        if ($request->recipient_scope === 'all') {
            // Tous les utilisateurs
            $recipients = $query->pluck('id')->all();
        } elseif ($request->recipient_scope === 'active') {
            // Utilisateurs actifs uniquement
            $recipients = $query->where('is_blocked', false)->pluck('id')->all();
        } elseif ($request->recipient_scope === 'blocked') {
            // Utilisateurs bloqués uniquement
            $recipients = $query->where('is_blocked', true)->pluck('id')->all();
        } elseif ($request->recipient_scope === 'certified') {
            // Utilisateurs certifiés uniquement
            $recipients = $query->where('certifie', true)->pluck('id')->all();
        } elseif ($request->recipient_scope === 'selected') {
            // Sélection manuelle
            if ($request->has('recipients') && is_array($request->recipients) && count($request->recipients) > 0) {
                // Convertir en integers et valider que les utilisateurs existent
                $recipients = $query->whereIn('id', array_map('intval', $request->recipients))->pluck('id')->all();
            } else {
                return back()
                    ->with('error', 'Veuillez sélectionner au moins un destinataire pour la sélection manuelle.')
                    ->with('error_solutions', [
                        'Cochez au moins un utilisateur dans la liste',
                        'Ou choisissez une autre option de destinataires (Tous, Actifs, etc.)'
                    ])
                    ->withInput();
            }
        } else {
            $recipients = [];
        }

        if (empty($recipients)) {
            return back()
                ->with('error', 'Aucun destinataire trouvé avec les critères sélectionnés.')
                ->with('error_solutions', [
                    'Essayez de sélectionner une autre option de destinataires',
                    'Vérifiez que des utilisateurs correspondent aux critères sélectionnés',
                    'Utilisez "Tous les utilisateurs" pour envoyer à tout le monde'
                ])
                ->withInput();
        }

        // Déterminer si c'est un message de groupe (plus d'un destinataire)
        $isGroupMessage = count($recipients) > 1;

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'is_group_message' => $isGroupMessage,
        ]);

        $message->recipients()->sync($recipients);

        return redirect()->route('admin.dashboard')->with('success', 'Message envoyé à ' . count($recipients) . ' utilisateur(s).');
    }

    /**
     * API endpoint pour récupérer les utilisateurs avec filtres
     */
    public function getUsers(Request $request)
    {
        $query = User::query();

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_blocked', false);
            } elseif ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            }
        }

        if ($request->has('certified') && $request->certified !== 'all') {
            $query->where('certifie', $request->certified === 'true');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->select('id', 'name', 'email', 'is_blocked', 'certifie')
                      ->orderBy('name')
                      ->limit(50)
                      ->get();

        return response()->json($users);
    }
}


