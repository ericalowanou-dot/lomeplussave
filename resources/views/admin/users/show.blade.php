@extends('admin.layout')

@section('title', 'Détails utilisateur')
@section('page-title', 'Détails utilisateur')

@section('content')
<div class="admin-user-detail">
    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($user->name, 30) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar gauche : Profil & Actions --}}
        <div class="col-lg-4">
            {{-- Carte profil --}}
            <div class="admin-card mb-4 overflow-hidden">
                <div class="admin-card-body text-center pt-4 pb-4">
                    <div class="user-detail-avatar-wrapper mb-3 mx-auto">
                        <img src="{{ $user->getProfilPhotoUrl() }}" alt="Photo de profil" class="rounded-circle" width="110" height="110" style="object-fit: cover;" onerror="this.src='{{ asset('assets/icons/user_default.png') }}';">
                    </div>
                    <h4 class="mb-2 fw-bold">{{ $user->name }}</h4>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                        @if($user->estCertifie())
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Certifié</span>
                        @endif
                        @if($user->is_blocked)
                            <span class="status-badge blocked">Bloqué</span>
                        @else
                            <span class="status-badge active">Actif</span>
                        @endif
                    </div>
                    <small class="text-muted">#{{ $user->id }}</small>
                </div>
            </div>

            {{-- Contact --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-address-book me-2"></i>Contact</h5>
                </div>
                <div class="admin-card-body">
                    @if($user->email)
                        <div class="detail-info-row py-2 border-bottom">
                            <span class="detail-label"><i class="fas fa-envelope text-muted me-2"></i>Email</span>
                            <span class="detail-value small">
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                            </span>
                        </div>
                    @endif
                    <div class="detail-info-row py-2 {{ $user->email ? 'border-bottom' : '' }}">
                        <span class="detail-label"><i class="fas fa-phone text-muted me-2"></i>Téléphone</span>
                        <span class="detail-value">{{ $user->telephone ?? $user->whatsapp ?? '—' }}</span>
                    </div>
                    @if($user->getWhatsAppUrl())
                        <div class="pt-3">
                            <a href="{{ $user->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer" class="btn w-100 d-flex align-items-center justify-content-center gap-2" style="background:#25D366; color:white;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="22" height="22">
                                Contacter par WhatsApp
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Informations compte --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informations compte</h5>
                </div>
                <div class="admin-card-body">
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Inscription</span>
                        <span class="detail-value">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Coins</span>
                        <span class="badge bg-info text-dark">{{ $user->coins ?? 0 }}</span>
                    </div>
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Certification</span>
                        <span class="detail-value">
                            @if($user->estCertifie())
                                <span class="badge bg-success">Active</span>
                            @elseif((int) $user->certifie === 1 && $user->certifie_from && $user->certifie_from->isFuture())
                                <span class="badge bg-warning text-dark">Programmée</span>
                            @elseif((int) $user->certifie === 1)
                                <span class="badge bg-secondary">Expirée</span>
                            @else
                                <span class="text-muted">Non certifié</span>
                            @endif
                        </span>
                    </div>
                    @if($user->certifie_from || $user->certifie_until)
                        <div class="detail-info-row py-2">
                            <span class="detail-label">Période</span>
                            <span class="detail-value small">
                                {{ $user->certifie_from?->format('d/m/Y') ?? '—' }}
                                →
                                {{ $user->certifie_until?->format('d/m/Y') ?? '—' }}
                            </span>
                        </div>
                    @endif
                    @if($user->is_blocked)
                        @if($user->blocked_at)
                        <div class="detail-info-row py-2">
                            <span class="detail-label">Bloqué le</span>
                            <span class="detail-value text-danger">{{ $user->blocked_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($user->block_reason)
                        <div class="mt-3 p-3 rounded bg-danger bg-opacity-10 border border-danger border-opacity-25">
                            <small class="text-danger fw-semibold d-block mb-1">Raison du blocage</small>
                            <p class="mb-0 small text-dark">{{ $user->block_reason }}</p>
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="admin-card-body">
                    <div class="d-grid gap-3">
                        <div>
                            <label class="form-label small text-muted mb-1">Ajouter des coins</label>
                            <form method="POST" action="{{ route('admin.users.add-coins', $user) }}" class="d-flex gap-2">
                                @csrf
                                <input type="number" name="amount" min="1" class="form-control form-control-sm" placeholder="Quantité" style="max-width:90px;">
                                <button type="submit" class="btn btn-success btn-sm flex-grow-1" onclick="return confirm('Ajouter des coins à {{ addslashes($user->name) }} ?')">
                                    <i class="fas fa-coins me-1"></i> Valider
                                </button>
                            </form>
                        </div>
                        <hr class="my-2">
                        <div>
                            <label class="form-label small text-muted mb-2">
                                <i class="fas fa-check-circle me-1"></i>Certification boutique
                            </label>
                            <form method="POST" action="{{ route('admin.users.certify', $user) }}" class="d-grid gap-2">
                                @csrf
                                <div>
                                    <label class="form-label small mb-0" for="certifie_from">Date de début</label>
                                    <input type="date"
                                           name="certifie_from"
                                           id="certifie_from"
                                           class="form-control form-control-sm @error('certifie_from') is-invalid @enderror"
                                           value="{{ old('certifie_from', $user->certifie_from?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                           required>
                                    @error('certifie_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="form-label small mb-0" for="certifie_until">Date de fin</label>
                                    <input type="date"
                                           name="certifie_until"
                                           id="certifie_until"
                                           class="form-control form-control-sm @error('certifie_until') is-invalid @enderror"
                                           value="{{ old('certifie_until', $user->certifie_until?->format('Y-m-d') ?? now()->addMonth()->format('Y-m-d')) }}"
                                           required>
                                    @error('certifie_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit"
                                        class="btn btn-primary btn-sm"
                                        onclick="return confirm('Certifier {{ addslashes($user->name) }} et lui envoyer un message ?')">
                                    <i class="fas fa-award me-1"></i>
                                    {{ (int) $user->certifie === 1 ? 'Mettre à jour la certification' : 'Certifier' }}
                                </button>
                            </form>
                            @if((int) $user->certifie === 1)
                                <form method="POST" action="{{ route('admin.users.uncertify', $user) }}" class="mt-2">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-outline-secondary btn-sm w-100"
                                            onclick="return confirm('Retirer la certification de {{ addslashes($user->name) }} ?')">
                                        <i class="fas fa-times-circle me-1"></i> Retirer la certification
                                    </button>
                                </form>
                            @endif
                        </div>
                        <hr class="my-2">
                        @if($user->is_blocked)
                            <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('Débloquer cet utilisateur ?')">
                                    <i class="fas fa-unlock me-1"></i> Débloquer l'utilisateur
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-outline-warning w-100" onclick="blockUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                <i class="fas fa-ban me-1"></i> Bloquer l'utilisateur
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-danger w-100" onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                            <i class="fas fa-trash me-1"></i> Supprimer le compte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne principale : Articles --}}
        <div class="col-lg-8">
            {{-- En-tête résumé --}}
            @php
                $articlesCount = $user->articles->count();
                $pendingCount = $user->articles->where('status', 'pending')->count();
                $approvedCount = $user->articles->where('status', 'approved')->count();
                $blockedCount = $user->articles->where('status', 'blocked')->count();
                $reportsTotal = $user->reports_received_count ?? ($userReports->count() ?? 0);
                $reportsOpen = $user->open_reports_received_count ?? ($userReports->where('status', 'open')->count() ?? 0);
            @endphp
            <div class="admin-card mb-4">
                <div class="admin-card-body">
                    <h5 class="mb-3 fw-semibold"><i class="fas fa-chart-bar me-2 text-primary"></i>Activité utilisateur</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card text-center p-3 rounded">
                                <div class="h4 mb-0 text-primary fw-bold">{{ $articlesCount }}</div>
                                <small class="text-muted">Total articles</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card text-center p-3 rounded">
                                <div class="h4 mb-0 text-warning fw-bold">{{ $pendingCount }}</div>
                                <small class="text-muted">En attente</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card text-center p-3 rounded">
                                <div class="h4 mb-0 text-success fw-bold">{{ $approvedCount }}</div>
                                <small class="text-muted">Approuvés</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini-card text-center p-3 rounded">
                                <div class="h4 mb-0 text-danger fw-bold">{{ $reportsTotal }}</div>
                                <small class="text-muted">Signalements @if($reportsOpen > 0)<span class="d-block">({{ $reportsOpen }} ouverts)</span>@endif</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signalements boutique --}}
            <div class="admin-card mb-4" id="user-reports">
                <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="admin-card-title mb-0">
                        <i class="fas fa-flag me-2 text-danger"></i>
                        Signalements boutique
                        <span class="badge bg-danger">{{ $reportsTotal }}</span>
                    </h5>
                </div>
                <div class="admin-card-body">
                    @if(($userReports ?? collect())->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Signaleur</th>
                                        <th>Motif</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userReports as $report)
                                        <tr>
                                            <td>
                                                <strong>{{ $report->reporter?->name ?? '—' }}</strong>
                                                @if($report->reporter?->email)
                                                    <small class="text-muted d-block">{{ $report->reporter->email }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $report->reasonLabel() }}</td>
                                            <td class="small">{{ $report->message ? Str::limit($report->message, 80) : '—' }}</td>
                                            <td class="small">{{ $report->created_at?->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($report->status === 'open')
                                                    <span class="badge bg-warning text-dark">Ouvert</span>
                                                @else
                                                    <span class="badge bg-secondary">Fermé</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.user-reports.update-status', $report) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $report->status === 'open' ? 'closed' : 'open' }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        {{ $report->status === 'open' ? 'Marquer clos' : 'Rouvrir' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            Astuce : utilisez <strong>Bloquer</strong> pour couper l’accès, ou <strong>Supprimer</strong> pour retirer le compte et ses articles.
                        </p>
                    @else
                        <p class="text-muted mb-0">Aucun signalement pour cette boutique.</p>
                    @endif
                </div>
            </div>

            {{-- Liste des articles --}}
            <div class="admin-card">
                <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-newspaper me-2"></i>Articles publiés <span class="badge bg-secondary">{{ $articlesCount }}</span></h5>
                </div>
                <div class="admin-card-body">
                    @if($articlesCount > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">Photo</th>
                                        <th>Titre</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th class="text-end" style="width:140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->articles as $article)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.articles.show', $article) }}" class="d-block rounded overflow-hidden" style="width:44px; height:44px; background:#f0f0f0;">
                                                <img src="{{ $article->photo ? asset($article->photo) : asset('images/placeholder.png') }}" alt="" class="w-100 h-100 object-fit-cover" style="object-fit:cover;" onerror="this.src='{{ asset('images/placeholder.png') }}';">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.articles.show', $article) }}" class="text-decoration-none text-dark fw-medium">
                                                {{ Str::limit($article->titre, 45) }}
                                            </a>
                                        </td>
                                        <td class="text-nowrap">{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            @if($article->status === 'pending')
                                                <span class="status-badge pending">En attente</span>
                                            @elseif($article->status === 'approved')
                                                <span class="status-badge approved">Approuvé</span>
                                            @else
                                                <span class="status-badge blocked">Bloqué</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap small">{{ $article->created_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-outline-primary" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($article->status === 'pending')
                                                    <form method="POST" action="{{ route('admin.articles.approve', $article) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approuver" onclick="return confirm('Approuver cet article ?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($article->status !== 'blocked')
                                                    <button type="button" class="btn btn-outline-warning" title="Bloquer" onclick="blockArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger" title="Supprimer" onclick="deleteArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun article</h5>
                            <p class="text-muted mb-0">Cet utilisateur n'a pas encore publié d'articles.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="blockUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bloquer un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blockUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Vous êtes sur le point de bloquer l'utilisateur <strong id="userName"></strong>.</p>
                    <div class="mb-3">
                        <label for="block_user_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="block_user_reason" class="form-control" rows="3" placeholder="Expliquez pourquoi cet utilisateur est bloqué..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Bloquer l'utilisateur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Vous êtes sur le point de supprimer définitivement l'utilisateur <strong id="deleteUserName"></strong>.</p>
                    <p class="text-muted small mb-0">Tous ses articles et données associées seront également supprimés.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="blockArticleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bloquer un article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blockArticleForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Vous êtes sur le point de bloquer l'article <strong id="blockArticleName"></strong>.</p>
                    <div class="mb-3">
                        <label for="block_article_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="block_article_reason" class="form-control" rows="3" placeholder="Expliquez pourquoi cet article est bloqué..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Bloquer l'article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteArticleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer un article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteArticleForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Vous êtes sur le point de supprimer définitivement l'article <strong id="deleteArticleName"></strong>.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-user-detail .detail-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}
.admin-user-detail .detail-info-row-email {
    flex-wrap: wrap;
}
.admin-user-detail .detail-info-row-email .detail-value {
    max-width: 100%;
    word-break: break-all;
}
.admin-user-detail .detail-label { color: var(--text-secondary); font-size: 0.875rem; }
.admin-user-detail .detail-value { font-weight: 500; }
.admin-user-detail .user-detail-avatar-wrapper {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid var(--bs-primary);
    padding: 2px;
    overflow: hidden;
}
.admin-user-detail .stat-mini-card {
    background: var(--bs-light, #f8f9fa);
}
.admin-user-detail .stat-mini-card:hover {
    background: var(--bs-gray-200, #e9ecef);
}
.admin-user-detail .breadcrumb {
    background: transparent;
    padding: 0;
}
</style>
@endpush

@push('scripts')
<script>
const ROUTES = {
    blockUser: @json(route('admin.users.block', ['user' => '__ID__'])),
    deleteUser: @json(route('admin.users.delete', ['user' => '__ID__'])),
    blockArticle: @json(route('admin.articles.block', ['article' => '__ID__'])),
    deleteArticle: @json(route('admin.articles.delete', ['article' => '__ID__'])),
};
function blockUser(userId, userName) {
    document.getElementById('blockUserForm').action = ROUTES.blockUser.replace('__ID__', userId);
    document.getElementById('userName').textContent = userName;
    new bootstrap.Modal(document.getElementById('blockUserModal')).show();
}
function deleteUser(userId, userName) {
    document.getElementById('deleteUserForm').action = ROUTES.deleteUser.replace('__ID__', userId);
    document.getElementById('deleteUserName').textContent = userName;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
function blockArticle(articleId, articleTitle) {
    document.getElementById('blockArticleForm').action = ROUTES.blockArticle.replace('__ID__', articleId);
    document.getElementById('blockArticleName').textContent = articleTitle;
    new bootstrap.Modal(document.getElementById('blockArticleModal')).show();
}
function deleteArticle(articleId, articleTitle) {
    document.getElementById('deleteArticleForm').action = ROUTES.deleteArticle.replace('__ID__', articleId);
    document.getElementById('deleteArticleName').textContent = articleTitle;
    new bootstrap.Modal(document.getElementById('deleteArticleModal')).show();
}
</script>
@endpush
