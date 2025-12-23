@extends('layouts.app2')

@section('title', 'Message')

@section('content')
<style>
    /* ========================================
       SHOW MESSAGE - Design conversation moderne
       ======================================== */
    
    .message-view-container {
        margin-top: 180px;
        padding: 0 16px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        padding-bottom: 40px;
    }
    
    /* Header */
    .message-view-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        margin-bottom: 24px;
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #fff;
        color: #64748b;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .btn-back:hover {
        background: #f8fafc;
        color: #475569;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    
    .btn-reply {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
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
    
    .btn-reply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        color: #fff;
    }
    
    /* Conversation wrapper */
    .conversation-wrapper {
        background: linear-gradient(180deg, #f0f4f8 0%, #e8eef5 100%);
        border-radius: 24px;
        padding: 24px;
        min-height: 300px;
    }
    
    /* Message bubble */
    .message-bubble {
        display: flex;
        gap: 14px;
        margin-bottom: 20px;
    }
    
    .message-bubble.sent {
        flex-direction: row-reverse;
    }
    
    /* Avatar */
    .message-avatar-wrapper {
        position: relative;
        flex-shrink: 0;
    }
    
    .message-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    }
    
    .message-avatar-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
    }
    
    .admin-avatar-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .admin-avatar-badge i {
        font-size: 0.6rem;
        color: #fff;
    }
    
    /* Message content */
    .message-content-wrapper {
        flex: 1;
        max-width: 85%;
    }
    
    .message-sender-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .message-sender-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1f2937;
    }
    
    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 600;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .message-time {
        font-size: 0.78rem;
        color: #9ca3af;
    }
    
    .message-bubble-content {
        background: #fff;
        border-radius: 18px;
        border-top-left-radius: 4px;
        padding: 18px 22px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(226, 232, 240, 0.5);
    }
    
    .message-bubble.sent .message-bubble-content {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-top-left-radius: 18px;
        border-top-right-radius: 4px;
        color: #fff;
        border: none;
    }
    
    .message-body-text {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #374151;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .message-bubble.sent .message-body-text {
        color: #fff;
    }
    
    /* Status bar */
    .message-status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px dashed rgba(148, 163, 184, 0.3);
    }
    
    .message-status-info {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: #64748b;
    }
    
    .message-status-info i {
        color: #9ca3af;
    }
    
    .status-check {
        display: flex;
        align-items: center;
        gap: 2px;
        color: #22c55e;
    }
    
    .status-check i {
        font-size: 0.9rem;
    }
    
    .status-check i:last-child {
        margin-left: -4px;
    }
    
    /* Parent message card */
    .parent-message-card {
        margin-top: 24px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        border: 1px solid rgba(59, 130, 246, 0.12);
        overflow: hidden;
    }
    
    .parent-message-header {
        padding: 14px 20px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.06), rgba(59, 130, 246, 0.02));
        border-bottom: 1px solid rgba(59, 130, 246, 0.08);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .parent-message-header i {
        color: #3b82f6;
    }
    
    .parent-message-header span {
        font-size: 0.85rem;
        color: #3b82f6;
        font-weight: 500;
    }
    
    .parent-message-body {
        padding: 18px 20px;
    }
    
    .parent-message-excerpt {
        font-size: 0.9rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 14px;
    }
    
    .btn-view-original {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(59, 130, 246, 0.08);
        color: #3b82f6;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .btn-view-original:hover {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }
    
    /* Masquer boutons flottants */
    #megaphone-button,
    .gauche,
    .droite {
        display: none !important;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        .message-view-container {
            margin-top: 200px;
            padding: 0 12px;
        }
        
        .message-view-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        
        .btn-back {
            justify-content: center;
        }
        
        .btn-reply {
            width: 100%;
            justify-content: center;
        }
        
        .conversation-wrapper {
            padding: 16px;
            border-radius: 20px;
        }
        
        .message-avatar,
        .message-avatar-placeholder {
            width: 44px;
            height: 44px;
        }
        
        .message-content-wrapper {
            max-width: 90%;
        }
        
        .message-bubble-content {
            padding: 14px 16px;
        }
    }
</style>

    @php
        $isAdminSender = $message->sender->isAdmin();
        $currentUser = auth()->user();
    
    // Photo de profil avec fallback
    $hasPhoto = $message->sender->photo_profil && file_exists(storage_path('app/public/' . $message->sender->photo_profil));
    $senderPhoto = $hasPhoto 
        ? asset('storage/' . $message->sender->photo_profil) 
        : null;
    
    // Initiales pour le placeholder
    $initials = strtoupper(substr($message->sender->name, 0, 2));
    
    // Déterminer si c'est un message envoyé ou reçu
    $isSent = $message->sender_id == $currentUser->id;
    @endphp

<div class="message-view-container">
    <!-- Header -->
    <div class="message-view-header">
        <a href="{{ route('messages.inbox') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            <span>Retour à la messagerie</span>
        </a>
        @if(!$isSent)
            <a href="{{ route('messages.compose.reply', $message) }}" class="btn-reply">
                <i class="bi bi-reply-fill"></i>
                <span>Répondre</span>
            </a>
        @endif
    </div>

    <!-- Conversation -->
    <div class="conversation-wrapper">
        <div class="message-bubble {{ $isSent ? 'sent' : '' }}">
            <!-- Avatar -->
            <div class="message-avatar-wrapper">
                @if($senderPhoto)
                    <img src="{{ $senderPhoto }}" alt="{{ $message->sender->name }}" class="message-avatar">
                @else
                    <div class="message-avatar-placeholder">{{ $initials }}</div>
                @endif
                @if($isAdminSender)
                    <div class="admin-avatar-badge">
                        <i class="bi bi-check-lg"></i>
                    </div>
                @endif
            </div>
            
            <!-- Content -->
            <div class="message-content-wrapper">
                <div class="message-sender-header">
                    <span class="message-sender-name">{{ $message->sender->name }}</span>
                        @if($isAdminSender)
                        <span class="admin-badge">
                            <i class="bi bi-shield-check"></i>
                            Admin
                            </span>
                        @endif
                    <span class="message-time">
                        @if($message->created_at->isToday())
                            Aujourd'hui à {{ $message->created_at->format('H:i') }}
                        @elseif($message->created_at->isYesterday())
                            Hier à {{ $message->created_at->format('H:i') }}
                        @else
                            {{ $message->created_at->format('d/m/Y à H:i') }}
                    @endif
                    </span>
                </div>
                
                <div class="message-bubble-content">
                    <div class="message-body-text">{!! nl2br(e($message->body)) !!}</div>
                </div>
            </div>
        </div>
        
        <!-- Status bar -->
        <div class="message-status-bar">
            <div class="message-status-info">
                @if($message->is_group_message)
                    <i class="bi bi-people-fill"></i>
                    <span>Message de groupe</span>
                    @else
                    <i class="bi bi-person-fill"></i>
                    <span>Message personnel</span>
                    @endif
                </div>
            <div class="status-check" title="Lu">
                <i class="bi bi-check2"></i>
                <i class="bi bi-check2"></i>
            </div>
        </div>
    </div>

    <!-- Historique des réponses si présent -->
    @if($message->parentMessage)
        <div class="parent-message-card">
            <div class="parent-message-header">
                <i class="bi bi-reply"></i>
                <span>En réponse à un message précédent</span>
            </div>
            <div class="parent-message-body">
                <p class="parent-message-excerpt">{{ Str::limit($message->parentMessage->body, 150) }}</p>
                <a href="{{ route('messages.show', $message->parentMessage) }}" class="btn-view-original">
                    <i class="bi bi-eye"></i>
                    Voir le message original
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
