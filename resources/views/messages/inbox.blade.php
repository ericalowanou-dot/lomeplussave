@extends('layouts.app2')

@section('title', 'Ma messagerie')

@section('content')
<style>
    /* ========================================
       INBOX - Design moderne et professionnel
       ======================================== */
    
    .inbox-container {
        margin-top: 180px;
        padding: 0 16px;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Header de la messagerie */
    .inbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 0;
        margin-bottom: 16px;
    }
    
    .inbox-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .inbox-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
    }
    
    .inbox-icon-wrapper i {
        font-size: 1.4rem;
        color: #fff;
    }
    
    .inbox-title {
        margin: 0;
        font-weight: 700;
        font-size: 1.4rem;
        color: #1f2937;
    }
    
    .inbox-subtitle {
        margin: 2px 0 0;
        font-size: 0.88rem;
        color: #6b7280;
    }
    
    .btn-new-message {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-new-message:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        color: #fff;
    }
    
    /* Alerts */
    .inbox-alert {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .inbox-alert-success {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #15803d;
    }
    
    /* Liste des messages */
    .messages-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .message-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        border-radius: 16px;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        border: 1px solid rgba(226, 232, 240, 0.5);
    }
    
    /* Messages lus - fond blanc clair */
    .message-item.read {
        background: #ffffff;
    }
    
    .message-item.read:hover {
        background: #fafafa;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }
    
    /* Messages non lus - fond gris plus prononcé */
    .message-item.unread {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        border: 1px solid rgba(59, 130, 246, 0.2);
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    
    .message-item.unread:hover {
        background: linear-gradient(135deg, #dbe4ed 0%, #c4cdd8 100%);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.5);
        transform: translateY(-1px);
    }
    
    /* Avatar du sender */
    .message-avatar {
        position: relative;
        flex-shrink: 0;
    }
    
    .message-avatar-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .message-avatar-admin {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }
    
    .message-avatar-admin i {
        font-size: 0.6rem;
        color: #fff;
    }
    
    /* Contenu du message */
    .message-content {
        flex: 1;
        min-width: 0;
    }
    
    .message-sender-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    
    .message-sender-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2937;
    }
    
    .message-item.unread .message-sender-name {
        font-weight: 700;
        color: #111827;
    }
    
    .message-admin-badge {
        padding: 2px 8px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 600;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .message-subject {
        font-size: 0.88rem;
        color: #4b5563;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .message-item.unread .message-subject {
        color: #374151;
        font-weight: 500;
    }
    
    /* Métadonnées (date + statut) */
    .message-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }
    
    .message-date {
        font-size: 0.78rem;
        color: #9ca3af;
        font-weight: 500;
    }
    
    .message-item.unread .message-date {
        color: #3b82f6;
        font-weight: 600;
    }
    
    /* Indicateurs de statut modernes (style WhatsApp) */
    .message-status {
        display: flex;
        align-items: center;
        gap: 2px;
    }
    
    /* Simple trait = envoyé */
    .status-sent {
        display: flex;
        align-items: center;
    }
    
    .status-sent::before {
        content: '';
        display: block;
        width: 14px;
        height: 2px;
        background: #9ca3af;
        border-radius: 1px;
    }
    
    /* Double trait = lu */
    .status-read {
        display: flex;
        align-items: center;
        gap: 3px;
    }
    
    .status-read::before,
    .status-read::after {
        content: '';
        display: block;
        width: 10px;
        height: 2px;
        background: #22c55e;
        border-radius: 1px;
    }
    
    /* Alternative : Double check style WhatsApp */
    .status-check {
        display: flex;
        align-items: center;
    }
    
    .status-check.sent i {
        color: #9ca3af;
        font-size: 0.9rem;
    }
    
    .status-check.read {
        position: relative;
    }
    
    .status-check.read i {
        color: #22c55e;
        font-size: 0.9rem;
    }
    
    .status-check.read i:last-child {
        margin-left: -6px;
    }
    
    /* Point non lu */
    .unread-dot {
        width: 10px;
        height: 10px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.4);
    }
    
    /* État vide */
    .inbox-empty {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
    }
    
    .inbox-empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .inbox-empty-icon i {
        font-size: 2rem;
        color: #3b82f6;
    }
    
    .inbox-empty-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    
    .inbox-empty-text {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 24px;
    }
    
    /* Pagination */
    .inbox-pagination {
        display: flex;
        justify-content: center;
        padding: 20px 0;
    }
    
    /* Masquer le bouton publier une annonce sur cette page */
    #megaphone-button,
    .gauche,
    .droite {
        display: none !important;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        .inbox-container {
            margin-top: 200px;
            padding: 0 12px;
        }
        
        .inbox-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        
        .btn-new-message {
            width: 100%;
            justify-content: center;
        }
        
        .message-item {
            padding: 14px 16px;
            gap: 12px;
        }
        
        .message-avatar-img {
            width: 44px;
            height: 44px;
        }
        
        .message-sender-name {
            font-size: 0.9rem;
        }
        
        .message-subject {
            font-size: 0.82rem;
        }
        
        .message-date {
            font-size: 0.72rem;
        }
    }
</style>

<div class="inbox-container">
    <!-- Header -->
    <div class="inbox-header">
        <div class="inbox-header-left">
            <div class="inbox-icon-wrapper">
                <i class="bi bi-chat-square-text-fill"></i>
            </div>
            <div>
                <h4 class="inbox-title">Ma messagerie</h4>
                <p class="inbox-subtitle">Gérez vos conversations avec l'administration</p>
            </div>
        </div>
        <a href="{{ route('messages.compose') }}" class="btn-new-message">
            <i class="bi bi-plus-lg"></i>
            <span>Envoyer un nouveau message</span>
        </a>
    </div>

    @if(session('success'))
        <div class="inbox-alert inbox-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error') || session('error_solutions'))
        <x-error-alert 
            type="error" 
            title="Erreur"
            :message="session('error') ?? 'Une erreur s\'est produite.'"
            :solutions="session('error_solutions', [])"
        />
    @endif

    <!-- Liste des messages -->
    <div class="messages-list">
        @if($messages->count() > 0)
            @foreach($messages as $msg)
                @php
                    $isRead = $msg->recipients()
                        ->where('recipient_id', auth()->id())
                        ->whereNotNull('read_at')
                        ->exists();
                    $isAdmin = $msg->sender->isAdmin();
                    $senderPhoto = $msg->sender->photo_profil 
                        ? asset('storage/' . $msg->sender->photo_profil) 
                        : asset('images/default-avatar.png');
                @endphp
                
                <a href="{{ route('messages.show', $msg) }}" class="message-item {{ $isRead ? 'read' : 'unread' }}">
                    <!-- Avatar -->
                    <div class="message-avatar">
                        <img src="{{ $senderPhoto }}" alt="{{ $msg->sender->name }}" class="message-avatar-img">
                        @if($isAdmin)
                            <div class="message-avatar-admin">
                                <i class="bi bi-check-lg"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Contenu -->
                    <div class="message-content">
                        <div class="message-sender-row">
                            <span class="message-sender-name">{{ $msg->sender->name }}</span>
                            @if($isAdmin)
                                <span class="message-admin-badge">Admin</span>
                            @endif
                        </div>
                        <div class="message-subject">
                            {{ $msg->subject ?: Str::limit($msg->body, 50) }}
                        </div>
                    </div>
                    
                    <!-- Métadonnées -->
                    <div class="message-meta">
                        <span class="message-date">
                            @if($msg->created_at->isToday())
                                {{ $msg->created_at->format('H:i') }}
                            @elseif($msg->created_at->isYesterday())
                                Hier
                            @else
                                {{ $msg->created_at->format('d/m') }}
                            @endif
                        </span>
                        
                        <!-- Statut moderne -->
                        <div class="message-status">
                            @if(!$isRead)
                                <div class="unread-dot"></div>
                            @else
                                <div class="status-check read">
                                    <i class="bi bi-check2"></i>
                                    <i class="bi bi-check2"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        @else
            <div class="inbox-empty">
                <div class="inbox-empty-icon">
                    <i class="bi bi-chat-square-text"></i>
                </div>
                <h5 class="inbox-empty-title">Aucun message</h5>
                <p class="inbox-empty-text">Vous n'avez pas encore reçu de messages.<br>Utilisez le bouton ci-dessus pour envoyer un nouveau message.</p>
            </div>
        @endif
    </div>

    @if($messages->count() > 0)
        <div class="inbox-pagination">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection
