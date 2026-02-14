@extends('admin.layout')

@section('title', 'Message')
@section('page-title', 'Message')

@section('content')
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-card-title">
            <i class="fas fa-envelope"></i>
            Détails du message
        </h5>
        <a href="{{ route('admin.messages.inbox') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
    <div class="admin-card-body">
        @php
            $sender = $message->sender;
            $senderPhoto = $sender->getProfilPhotoUrl();
            $isRead = $message->recipients()
                ->where('recipient_id', auth()->id())
                ->whereNotNull('read_at')
                ->exists();
        @endphp

        <!-- Informations de l'expéditeur -->
        <div class="mb-4 p-3 bg-light rounded">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="{{ $senderPhoto }}" alt="{{ $sender->name }}" 
                         class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                    <div>
                        <h6 class="mb-1">{{ $sender->name }}</h6>
                        <small class="text-muted">{{ $sender->email }}</small>
                        @if($sender->telephone)
                            <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $sender->telephone }}</small>
                        @endif
                    </div>
                </div>
                <div>
                    @if($sender->whatsapp)
                        @php
                            $whatsappMessage = "Bonjour " . $sender->name . ", je vous réponds concernant votre message : \"" . ($message->subject ?: 'votre demande') . "\"";
                            $whatsappMessageEncoded = urlencode($whatsappMessage);
                            $whatsappNumber = preg_replace('/[^0-9]/', '', $sender->whatsapp);
                        @endphp
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessageEncoded }}" 
                           target="_blank" 
                           class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Contacter sur WhatsApp
                        </a>
                    @endif
                    @if($sender->telephone)
                        <a href="tel:{{ $sender->telephone }}" class="btn btn-warning mt-2">
                            <i class="fas fa-phone"></i> Appeler
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Métadonnées du message -->
        <div class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <strong>Sujet :</strong> {{ $message->subject ?: '(Sans sujet)' }}
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> 
                        {{ $message->created_at->format('d/m/Y à H:i') }}
                    </small>
                    @if($isRead)
                        <span class="badge bg-success ms-2">Lu</span>
                    @else
                        <span class="badge bg-warning ms-2">Non lu</span>
                    @endif
                </div>
            </div>
        </div>

        <hr>

        <!-- Contenu du message -->
        <div class="message-content p-3 bg-white border rounded">
            <div style="white-space: pre-wrap; line-height: 1.6;">{{ $message->body }}</div>
        </div>

        <!-- Actions -->
        <div class="mt-4 d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.messages.compose') }}?message={{ $message->id }}" class="btn btn-primary">
                <i class="fas fa-reply"></i> Répondre
            </a>
            <a href="{{ route('admin.messages.inbox') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>

        <!-- Informations supplémentaires -->
        @if($message->is_group_message)
            <div class="mt-3 alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Ce message a été envoyé à plusieurs destinataires.
            </div>
        @endif

        @if($message->parentMessage)
            <div class="mt-3">
                <a href="{{ route('admin.messages.show', $message->parentMessage) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-arrow-up"></i> Voir le message parent
                </a>
            </div>
        @endif

        @if($message->replies->count() > 0)
            <div class="mt-3">
                <h6>Réponses ({{ $message->replies->count() }})</h6>
                @foreach($message->replies as $reply)
                    <div class="border-start border-3 ps-3 mb-2">
                        <small><strong>{{ $reply->sender->name }}</strong> - {{ $reply->created_at->format('d/m/Y H:i') }}</small>
                        <p class="mb-0">{{ Str::limit($reply->body, 100) }}</p>
                        <a href="{{ route('admin.messages.show', $reply) }}" class="btn btn-sm btn-outline-primary mt-1">
                            Voir la réponse
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .message-content {
        min-height: 150px;
    }
    .btn-success {
        background-color: #25d366;
        border-color: #25d366;
    }
    .btn-success:hover {
        background-color: #20ba5a;
        border-color: #20ba5a;
    }
</style>
@endpush
