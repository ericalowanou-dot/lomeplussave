@extends('admin.layout')

@section('title', 'Signalements')
@section('page-title', 'Signalements')

@section('content')
<div class="admin-reports">
    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Signalements</li>
        </ol>
    </nav>

    {{-- Stats cards améliorées --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="admin-reports-stat-card stat-total">
                <div class="stat-icon"><i class="fas fa-flag"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['total'] }}</span>
                    <span class="stat-label">Total signalements</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="admin-reports-stat-card stat-open {{ $stats['open'] > 0 ? 'has-pending' : '' }}">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['open'] }}</span>
                    <span class="stat-label">Ouverts</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="admin-reports-stat-card stat-closed">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['closed'] }}</span>
                    <span class="stat-label">Fermés</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres par statut (pills) --}}
    <div class="admin-reports-filters mb-4">
        <a href="{{ route('admin.reports.index') }}" class="filter-pill {{ !request('status') ? 'active' : '' }}">
            Tous
        </a>
        <a href="{{ route('admin.reports.index', ['status' => 'open']) }}" class="filter-pill {{ request('status') === 'open' ? 'active' : '' }}">
            <i class="fas fa-clock me-1"></i> Ouverts
            @if($stats['open'] > 0)
                <span class="filter-badge">{{ $stats['open'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.reports.index', ['status' => 'closed']) }}" class="filter-pill {{ request('status') === 'closed' ? 'active' : '' }}">
            <i class="fas fa-check me-1"></i> Fermés
        </a>
    </div>

    {{-- Liste des signalements --}}
    <div class="admin-card">
        <div class="admin-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="admin-card-title mb-0">
                <i class="fas fa-inbox text-primary me-2"></i>Boîte des signalements
                <span class="badge bg-primary ms-2">{{ $reports->total() }}</span>
            </h5>
        </div>
        <div class="admin-card-body p-0">
            @if($reports->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-reports-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:48px;"></th>
                                <th style="width:52px;">#</th>
                                <th style="min-width:200px;">Utilisateur</th>
                                <th style="min-width:160px;">Sujet</th>
                                <th>Message</th>
                                <th style="width:110px;">Date</th>
                                <th style="width:100px;" class="text-center">Statut</th>
                                <th style="width:130px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                @php
                                    $userPhoto = $report->user ? $report->user->getProfilPhotoUrl() : asset('assets/icons/user_default.png');
                                    $createdAt = $report->created_at;
                                    $dateDisplay = $createdAt ? ($createdAt->isToday() ? 'Aujourd\'hui ' . $createdAt->format('H:i')
                                        : ($createdAt->isYesterday() ? 'Hier ' . $createdAt->format('H:i')
                                        : $createdAt->format('d/m/Y H:i'))) : '—';
                                @endphp
                                <tr id="report-{{ $report->id }}" class="report-row {{ $report->status === 'open' ? 'report-row-open' : '' }}">
                                    <td class="py-3">
                                        @if($report->status === 'open')
                                            <span class="badge-dot bg-warning" title="À traiter"></span>
                                        @endif
                                    </td>
                                    <td class="py-3 fw-medium text-muted">{{ $report->id }}</td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $userPhoto }}" alt="{{ $report->user?->name ?? 'Anonyme' }}"
                                                 class="rounded-circle flex-shrink-0 report-user-avatar"
                                                 onerror="this.src='{{ asset('assets/icons/user_default.png') }}'">
                                            <div class="min-w-0">
                                                @if($report->user)
                                                    <a href="{{ route('admin.users.show', $report->user) }}" class="text-decoration-none fw-medium text-dark d-block text-truncate">
                                                        {{ $report->user->name }}
                                                    </a>
                                                    <small class="text-muted text-truncate d-block">{{ $report->user->email ?? '—' }}</small>
                                                @else
                                                    <span class="text-muted fst-italic">Anonyme</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="{{ $report->status === 'open' ? 'fw-semibold' : '' }} report-subject">
                                            {{ $report->subject ?: '(Sans sujet)' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="report-message-cell">
                                            <span class="report-message-preview text-muted" title="{{ $report->message }}">
                                                {{ Str::limit($report->message, 65) }}
                                            </span>
                                            @if(strlen($report->message) > 65)
                                                <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-primary report-view-more"
                                                    data-report-id="{{ $report->id }}"
                                                    data-report-subject="{{ e($report->subject ?: 'Sans sujet') }}"
                                                    data-report-message="{{ e($report->message) }}"
                                                    data-report-date="{{ $dateDisplay }}">
                                                    Voir plus <i class="fas fa-chevron-right small"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 text-nowrap small text-muted">{{ $dateDisplay }}</td>
                                    <td class="py-3 text-center">
                                        @if($report->status === 'open')
                                            <span class="badge rounded-pill bg-warning text-dark"><i class="fas fa-clock me-1"></i>Ouvert</span>
                                        @else
                                            <span class="badge rounded-pill bg-success"><i class="fas fa-check me-1"></i>Fermé</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" action="{{ route('admin.reports.update-status', $report) }}" class="d-inline" onsubmit="return confirm('{{ $report->status === 'open' ? 'Marquer ce signalement comme fermé ?' : 'Réouvrir ce signalement ?' }}');">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $report->status === 'open' ? 'closed' : 'open' }}">
                                                <button type="submit" class="btn {{ $report->status === 'open' ? 'btn-outline-success' : 'btn-outline-warning' }}" title="{{ $report->status === 'open' ? 'Fermer' : 'Réouvrir' }}">
                                                    @if($report->status === 'open')
                                                        <i class="fas fa-check"></i>
                                                    @else
                                                        <i class="fas fa-undo"></i>
                                                    @endif
                                                </button>
                                            </form>
                                            @if($report->user && $report->user->getWhatsAppUrl())
                                                <a href="{{ $report->user->getWhatsAppUrl() }}" target="_blank" rel="noopener" class="btn btn-success" title="Contacter par WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="admin-reports-pagination p-3 border-top">
                        {{ $reports->links() }}
                    </div>
                @endif
            @else
                <div class="admin-reports-empty">
                    <div class="reports-empty-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="text-muted mb-2">Aucun signalement</h5>
                    <p class="text-muted mb-0">
                        @if(request('status'))
                            Aucun signalement {{ request('status') === 'open' ? 'ouvert' : 'fermé' }} pour le moment.
                        @else
                            Aucun signalement n'a été envoyé pour le moment.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal unique pour afficher le détail du message --}}
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-flag text-danger me-2"></i>
                    Signalement #<span id="modalReportId"></span> — <span id="modalReportSubject"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="report-detail-message bg-light rounded p-3" id="modalReportMessage"></div>
                <small class="text-muted d-block mt-2" id="modalReportDate"></small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // Scroll vers le signalement si hash présent
    var hash = window.location.hash;
    if (hash && /^#report-\d+$/.test(hash)) {
        var el = document.querySelector(hash);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('report-row-highlight');
            setTimeout(function() { el.classList.remove('report-row-highlight'); }, 2500);
        }
    }
    // Modal détail du message (données injectées via data-*)
    var modal = document.getElementById('reportDetailModal');
    if (modal) {
        document.querySelectorAll('.report-view-more').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('modalReportId').textContent = this.dataset.reportId;
                document.getElementById('modalReportSubject').textContent = this.dataset.reportSubject;
                document.getElementById('modalReportMessage').textContent = this.dataset.reportMessage;
                document.getElementById('modalReportDate').textContent = this.dataset.reportDate;
                new bootstrap.Modal(modal).show();
            });
        });
    }
})();
</script>
@endpush
@endsection

@push('styles')
<style>
.admin-reports .breadcrumb { background: transparent; padding: 0; }

/* Stats cards */
.admin-reports-stat-card {
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
.admin-reports-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.admin-reports-stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.stat-total .stat-icon { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
.stat-open .stat-icon { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.stat-open.has-pending .stat-icon { background: rgba(245, 158, 11, 0.2); color: #d97706; }
.stat-closed .stat-icon { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.admin-reports-stat-card .stat-value { display: block; font-size: 1.5rem; font-weight: 700; color: #111827; }
.admin-reports-stat-card .stat-label { font-size: 0.875rem; color: #6b7280; }

/* Filtres pills */
.admin-reports-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.admin-reports-filters .filter-pill {
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
.admin-reports-filters .filter-pill:hover {
    background: #f9fafb; color: #374151; border-color: #d1d5db;
}
.admin-reports-filters .filter-pill.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white; border-color: transparent;
}
.admin-reports-filters .filter-badge {
    margin-left: 0.35rem;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.25);
    font-size: 0.75rem; font-weight: 600;
}

/* Table */
.admin-reports-table .report-user-avatar { width: 40px; height: 40px; object-fit: cover; }
.admin-reports-table .report-row-open { background-color: rgba(245, 158, 11, 0.06) !important; }
.admin-reports-table .report-row-highlight { background-color: rgba(245, 158, 11, 0.18) !important; animation: report-pulse 0.5s ease; }
.admin-reports-table .badge-dot {
    display: inline-block; width: 8px; height: 8px; border-radius: 50%;
}
.admin-reports-table .report-message-cell { max-width: 320px; }
.admin-reports-table .report-message-preview { font-size: 0.9rem; }
.admin-reports-pagination { background: #fafafa; }

/* Empty state */
.admin-reports-empty {
    text-align: center; padding: 4rem 2rem;
}
.admin-reports-empty .reports-empty-icon {
    font-size: 4.5rem; color: #e5e7eb;
    margin-bottom: 1rem;
}
.admin-reports-empty .reports-empty-icon i { opacity: 0.6; }

/* WhatsApp button */
.admin-reports .btn-success[title="Contacter par WhatsApp"] { background: #25d366 !important; border-color: #25d366 !important; }
.admin-reports .btn-success[title="Contacter par WhatsApp"]:hover { background: #20ba5a !important; border-color: #20ba5a !important; color: white !important; }

@keyframes report-pulse { 0% { opacity: 1; } 50% { opacity: 0.85; } 100% { opacity: 1; } }
</style>
@endpush
