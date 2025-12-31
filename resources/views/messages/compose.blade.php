@extends('layouts.app2')

@section('title', 'Nouveau message')

@section('content')
<style>
    /* ========================================
       COMPOSE - Design moderne et professionnel
       ======================================== */
    
    .compose-container {
        margin-top: 180px;
        padding: 0 16px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        padding-bottom: 40px;
    }
    
    /* Header */
    .compose-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 0;
        margin-bottom: 20px;
    }
    
    .compose-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .compose-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
    }
    
    .compose-icon-wrapper i {
        font-size: 1.3rem;
        color: #fff;
    }
    
    .compose-title {
        margin: 0;
        font-weight: 700;
        font-size: 1.3rem;
        color: #1f2937;
    }
    
    .compose-subtitle {
        margin: 2px 0 0;
        font-size: 0.88rem;
        color: #6b7280;
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .btn-back:hover {
        background: rgba(100, 116, 139, 0.15);
        color: #475569;
    }
    
    /* Alertes */
    .compose-alert {
        padding: 16px 20px;
        border-radius: 14px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    
    .compose-alert-info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.04));
        border: 1px solid rgba(59, 130, 246, 0.15);
    }
    
    .compose-alert-info i {
        color: #3b82f6;
        font-size: 1.2rem;
        margin-top: 2px;
    }
    
    .compose-alert-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.04));
        border: 1px solid rgba(245, 158, 11, 0.15);
    }
    
    .compose-alert-warning i {
        color: #f59e0b;
        font-size: 1.2rem;
        margin-top: 2px;
    }
    
    .compose-alert-content h6 {
        margin: 0 0 6px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2937;
    }
    
    .compose-alert-content p {
        margin: 0;
        font-size: 0.88rem;
        color: #4b5563;
    }
    
    /* Bloc réponse */
    .reply-context {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .reply-context-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .reply-context-header i {
        color: #3b82f6;
    }
    
    .reply-context-header span {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .reply-context-sender {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }
    
    .reply-context-subject {
        font-size: 0.9rem;
        color: #3b82f6;
        margin-bottom: 8px;
    }
    
    .reply-context-body {
        font-size: 0.85rem;
        color: #6b7280;
        line-height: 1.5;
    }
    
    /* Formulaire */
    .compose-form-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        padding: 28px;
    }
    
    .form-group {
        margin-bottom: 22px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #374151;
        margin-bottom: 10px;
    }
    
    .form-group label .optional {
        font-weight: 400;
        color: #9ca3af;
        font-size: 0.82rem;
    }
    
    .form-group label .required {
        color: #ef4444;
    }
    
    .form-input {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid rgba(209, 213, 219, 0.8);
        border-radius: 12px;
        font-size: 0.95rem;
        color: #1f2937;
        background: #fff;
        transition: all 0.2s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .form-input::placeholder {
        color: #9ca3af;
    }
    
    .form-input.is-invalid {
        border-color: #ef4444;
    }
    
    .form-textarea {
        min-height: 200px;
        resize: vertical;
        line-height: 1.6;
    }
    
    .form-hint {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 8px;
    }
    
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 8px;
    }
    
    /* Actions */
    .compose-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .btn-cancel:hover {
        background: rgba(100, 116, 139, 0.15);
        color: #475569;
    }
    
    .btn-send {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
    }
    
    .btn-send:active {
        transform: translateY(0);
    }
    
    /* Masquer boutons flottants */
    #megaphone-button,
    .gauche,
    .droite {
        display: none !important;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        .compose-container {
            margin-top: 200px;
            padding: 0 12px;
        }
        
        .compose-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        
        .compose-form-card {
            padding: 20px 16px;
        }
        
        .compose-actions {
            justify-content: center;
        }
        
        .btn-send {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="compose-container">
    <!-- Header -->
    <div class="compose-header">
        <div class="compose-header-left">
            <div class="compose-icon-wrapper">
                <i class="bi bi-send-fill"></i>
            </div>
            <div>
                <h4 class="compose-title">
                    @if($message ?? null)
                        Répondre au message
                    @else
                        Nouveau message
                    @endif
                </h4>
                <p class="compose-subtitle">Contacter l'administrateur</p>
            </div>
        </div>
        <a href="{{ route('messages.inbox') }}" class="btn-back">
            <i class="bi bi-x-lg"></i>
            <span>Annuler</span>
        </a>
    </div>

    @if(session('success'))
        <div class="compose-alert" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.04)); border: 1px solid rgba(34, 197, 94, 0.15); margin-bottom: 20px;">
            <i class="bi bi-check-circle-fill" style="color: #22c55e; font-size: 1.2rem;"></i>
            <div class="compose-alert-content">
                <p style="color: #16a34a; font-weight: 600;">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="compose-alert" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(239, 68, 68, 0.04)); border: 1px solid rgba(239, 68, 68, 0.15); margin-bottom: 20px;">
            <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444; font-size: 1.2rem;"></i>
            <div class="compose-alert-content">
                <p style="color: #dc2626; font-weight: 600;">{{ session('error') }}</p>
                @if(session('error_solutions'))
                    <ul style="margin-top: 8px; padding-left: 20px; color: #6b7280; font-size: 0.88rem;">
                        @foreach(session('error_solutions') as $solution)
                            <li>{{ $solution }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="compose-alert" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(239, 68, 68, 0.04)); border: 1px solid rgba(239, 68, 68, 0.15); margin-bottom: 20px;">
            <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444; font-size: 1.2rem;"></i>
            <div class="compose-alert-content">
                <p style="color: #dc2626; font-weight: 600;">Erreur de validation</p>
                <ul style="margin-top: 8px; padding-left: 20px; color: #6b7280; font-size: 0.88rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($message ?? null)
        <div class="reply-context">
            <div class="reply-context-header">
                <i class="bi bi-reply-fill"></i>
                <span>Vous répondez à</span>
            </div>
            <div class="reply-context-sender">{{ $message->sender->name }}</div>
            <div class="reply-context-subject">{{ $message->subject ?: 'Sans sujet' }}</div>
            <div class="reply-context-body">{{ Str::limit($message->body, 200) }}</div>
        </div>
    @endif

    <!-- Formulaire -->
    <div class="compose-form-card">
        <form method="POST" action="{{ route('messages.send') }}">
            @csrf
            
            @if($message ?? null)
                <input type="hidden" name="parent_message_id" value="{{ $message->id }}">
            @endif

            <div class="form-group">
                <label for="body">
                    Message <span class="required">*</span>
                </label>
                <textarea name="body" 
                          class="form-input form-textarea @error('body') is-invalid @enderror" 
                          id="body" 
                          required
                          placeholder="Écrivez votre message à l'administrateur...">{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <p class="form-hint">Votre message sera envoyé à l'administrateur</p>
            </div>

            <div class="compose-actions" style="justify-content: flex-end;">
                <button type="submit" class="btn-send">
                    <i class="bi bi-send-fill"></i>
                    <span>Envoyer le message</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
