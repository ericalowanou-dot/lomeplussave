@extends('admin.layout')

@section('title', 'Messages reçus')
@section('page-title', 'Messages reçus')

@section('content')
<div class="admin-messages-inbox">
    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Boîte de réception</li>
        </ol>
    </nav>

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="admin-inbox-stat-card stat-total">
                <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['total'] ?? $messages->total() }}</span>
                    <span class="stat-label">Total messages</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="admin-inbox-stat-card stat-unread {{ ($stats['unread'] ?? $unreadCount) > 0 ? 'has-unread' : '' }}">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['unread'] ?? $unreadCount }}</span>
                    <span class="stat-label">Non lus</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="admin-inbox-stat-card stat-read">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['read'] ?? (($stats['total'] ?? $messages->total()) - ($stats['unread'] ?? $unreadCount)) }}</span>
                    <span class="stat-label">Lus</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres et action --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="admin-inbox-filters">
            <a href="{{ route('admin.messages.inbox') }}" class="filter-pill {{ !request('filter') ? 'active' : '' }}">
                Tous
            </a>
            <a href="{{ route('admin.messages.inbox', ['filter' => 'unread']) }}" class="filter-pill {{ request('filter') === 'unread' ? 'active' : '' }}">
                <i class="fas fa-envelope me-1"></i> Non lus
                @if(($stats['unread'] ?? $unreadCount) > 0)
                    <span class="filter-badge">{{ $stats['unread'] ?? $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.messages.inbox', ['filter' => 'read']) }}" class="filter-pill {{ request('filter') === 'read' ? 'active' : '' }}">
                <i class="fas fa-check me-1"></i> Lus
            </a>
        </div>
        <a href="{{ route('admin.messages.compose') }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i> Nouveau message
        </a>
    </div>

    {{-- Liste des messages --}}
    <div class="admin-card">
        <div class="admin-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="admin-card-title mb-0">
                <i class="fas fa-envelope-open-text text-primary me-2"></i>Boîte de réception
                <span class="badge bg-primary ms-2">{{ $messages->total() }}</span>
            </h5>
        </div>
        <div class="admin-card-body p-0">
            @if($messages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-inbox-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:48px;"></th>
                                <th style="min-width:200px;">Expéditeur</th>
                                <th style="min-width:160px;">Sujet</th>
                                <th>Aperçu</th>
                                <th style="width:110px;">Date</th>
                                <th style="width:100px;" class="text-center">Statut</th>
                                <th style="width:130px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                                @php
                                    $recipient = $message->recipients->firstWhere('id', auth()->id());
                                    $isRead = $recipient ? ($recipient->pivot->read_at !== null) : false;
                                    $senderPhoto = $message->sender->getProfilPhotoUrl();
                                    $msgDate = $message->created_at;
                                    $dateDisplay = $msgDate->isToday() ? 'Aujourd\'hui ' . $msgDate->format('H:i')
                                        : ($msgDate->isYesterday() ? 'Hier ' . $msgDate->format('H:i')
                                        : $msgDate->format('d/m/Y H:i'));
                                @endphp
                                <tr class="message-row {{ $isRead ? '' : 'message-row-unread' }}">
                                    <td class="py-3">
                                        @if(!$isRead)
                                            <span class="badge-dot bg-primary" title="Non lu"></span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $senderPhoto }}" alt="{{ $message->sender->name }}"
                                                 class="rounded-circle flex-shrink-0 message-avatar"
                                                 onerror="this.src='{{ asset('images/user_default.png') }}'">
                                            <div class="min-w-0">
                                                <div class="fw-medium text-truncate">{{ $message->sender->name }}</div>
                                                <small class="text-muted text-truncate d-block">{{ $message->sender->email ?? '—' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="{{ !$isRead ? 'fw-semibold' : '' }}">{{ $message->subject ?: '(Sans sujet)' }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small message-preview">{{ Str::limit(strip_tags($message->body), 60) }}</span>
                                    </td>
                                    <td class="py-3 text-nowrap small text-muted">{{ $dateDisplay }}</td>
                                    <td class="py-3 text-center">
                                        @if($isRead)
                                            <span class="badge rounded-pill bg-secondary"><i class="fas fa-check me-1"></i>Lu</span>
                                        @else
                                            <span class="badge rounded-pill bg-primary"><i class="fas fa-envelope me-1"></i>Non lu</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.messages.compose') }}?message={{ $message->id }}" class="btn btn-outline-success" title="Répondre">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                            @if($message->sender->whatsapp ?? $message->sender->telephone ?? null)
                                                @php
                                                    $phone = $message->sender->whatsapp ?? $message->sender->telephone;
                                                    $whatsappMsg = urlencode("Bonjour " . $message->sender->name . ", je vous réponds concernant : " . ($message->subject ?: 'votre message'));
                                                    $digits = preg_replace('/\D/', '', $phone);
                                                    $digits = (str_starts_with($digits, '0') ? '228' : '') . ltrim($digits, '0');
                                                @endphp
                                                @if(strlen($digits) >= 8)
                                                    <a href="https://wa.me/{{ $digits }}?text={{ $whatsappMsg }}" target="_blank" rel="noopener" class="btn btn-success" title="Contacter par WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($messages->hasPages())
                    <div class="admin-inbox-pagination p-3 border-top">
                        {{ $messages->links() }}
                    </div>
                @endif
            @else
                <div class="admin-inbox-empty">
                    <div class="inbox-empty-icon">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <h5 class="text-muted mb-2">Aucun message</h5>
                    <p class="text-muted mb-4">
                        @if(request('filter') === 'unread')
                            Vous n'avez aucun message non lu.
                        @elseif(request('filter') === 'read')
                            Vous n'avez aucun message lu.
                        @else
                            Vous n'avez pas encore reçu de messages.
                        @endif
                    </p>
                    <a href="{{ route('admin.messages.compose') }}" class="btn btn-primary">
                        <i class="fas fa-pen me-1"></i> Envoyer un message
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-messages-inbox .breadcrumb { background: transparent; padding: 0; }

/* Stats cards */
.admin-inbox-stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}
.admin-inbox-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.admin-inbox-stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.admin-inbox-stat-card.stat-total .stat-icon { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
.admin-inbox-stat-card.stat-unread .stat-icon { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.admin-inbox-stat-card.stat-unread.has-unread .stat-icon { background: rgba(59, 130, 246, 0.2); color: #2563eb; }
.admin-inbox-stat-card.stat-read .stat-icon { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.admin-inbox-stat-card .stat-value { display: block; font-size: 1.5rem; font-weight: 700; color: #111827; }
.admin-inbox-stat-card .stat-label { font-size: 0.875rem; color: #6b7280; }

/* Filtres pills */
.admin-inbox-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.admin-inbox-filters .filter-pill {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    background: white;
    color: #6b7280;
    text-decoration: none;
    font-size: 0.9rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}
.admin-inbox-filters .filter-pill:hover {
    background: #f9fafb; color: #374151; border-color: #d1d5db;
}
.admin-inbox-filters .filter-pill.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white; border-color: transparent;
}
.admin-inbox-filters .filter-badge {
    margin-left: 0.35rem;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.25);
    font-size: 0.75rem; font-weight: 600;
}

/* Table */
.admin-inbox-table .message-avatar { width: 40px; height: 40px; object-fit: cover; }
.admin-inbox-table .message-row-unread { background-color: rgba(59, 130, 246, 0.05) !important; }
.admin-inbox-table .badge-dot {
    display: inline-block; width: 8px; height: 8px; border-radius: 50%;
}
.admin-inbox-table .message-preview { max-width: 300px; display: inline-block; }
.admin-inbox-pagination { background: #fafafa; }

/* Empty state */
.admin-inbox-empty {
    text-align: center; padding: 4rem 2rem;
}
.admin-inbox-empty .inbox-empty-icon {
    font-size: 4.5rem; color: #e5e7eb;
    margin-bottom: 1rem;
}
.admin-inbox-empty .inbox-empty-icon i { opacity: 0.6; }

/* WhatsApp button */
.admin-messages-inbox .btn-success[title="Contacter par WhatsApp"] { background: #25d366 !important; border-color: #25d366 !important; }
.admin-messages-inbox .btn-success[title="Contacter par WhatsApp"]:hover { background: #20ba5a !important; border-color: #20ba5a !important; color: white !important; }
</style>
@endpush
