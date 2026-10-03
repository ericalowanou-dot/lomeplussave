<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Services\MessageService;
use App\Services\NoSupportAdminException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Conversation de l'utilisateur avec l'équipe Lome+ (messages envoyés et reçus).
 * L'admin répond depuis le backoffice, comme pour le site.
 */
class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with(['sender', 'recipients' => fn ($q) => $q->where('users.id', $user->id)])
            ->where(fn ($q) => $q
                ->where('sender_id', $user->id)
                ->orWhereHas('recipients', fn ($r) => $r->where('recipient_id', $user->id)))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(max(1, min(50, (int) $request->input('per_page', 20))));

        return MessageResource::collection($messages);
    }

    public function show(Request $request, Message $message, MessageService $messages): MessageResource
    {
        Gate::authorize('view', $message);

        $messages->markRead($message, $request->user());

        return new MessageResource($message->load(['sender', 'recipients' => fn ($q) => $q->where('users.id', $request->user()->id)]));
    }

    public function store(Request $request, MessageService $messages): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'parent_message_id' => ['nullable', 'integer'],
        ], [
            'body.required' => 'Le message est obligatoire.',
            'body.min' => 'Le message doit contenir au moins 2 caractères.',
        ]);

        $parent = null;
        if (! empty($data['parent_message_id'])) {
            $parent = Message::findOrFail($data['parent_message_id']);
            Gate::authorize('reply', $parent);
        }

        try {
            $message = $messages->sendToSupport($request->user(), $data['body'], $parent);
        } catch (NoSupportAdminException) {
            return response()->json(['message' => 'Le support est momentanément indisponible. Écrivez-nous à lomeplus80@gmail.com.'], 503);
        }

        return (new MessageResource($message->load('sender')))->response()->setStatusCode(201);
    }
}
