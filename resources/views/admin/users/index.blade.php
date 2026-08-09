@extends('admin.layout')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="search-filters">
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="Rechercher par nom...">
            </div>
            
            <div class="filter-group">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqués</option>
                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Signalés</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort" class="form-label">Trier</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="">Plus récents</option>
                    <option value="reports" {{ request('sort') === 'reports' ? 'selected' : '' }}>Plus signalés</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            </div>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">
            <i class="fas fa-users"></i>
            Liste des utilisateurs ({{ $users->total() }})
        </h5>
    </div>
    <div class="admin-card-body">
        @if($users->count() > 0)
            <!-- Desktop Table -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            {{-- <th>Email</th> --}}
                            <th>Contact</th>
                            <th>Téléphone</th>
                            <th>Articles</th>
                            <th>Signalements</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->getProfilPhotoUrl() }}" 
                                         alt="Photo de profil" 
                                         class="rounded-circle me-2" 
                                         width="40" 
                                         height="40"
                                         onerror="this.src='{{ asset('assets/icons/user_default.png') }}';">
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->estCertifie())
                                            <i class="fas fa-check-circle text-success ms-1" title="Utilisateur certifié"></i>
                                        @endif
                                        <small class="text-muted d-block">{{ $user->coins ?? 0 }} coins</small>
                                    </div>
                                </div>
                            </td>
                            {{-- <td>{{ $user->email }}</td> --}}
                            <td>
                                @if($user->getWhatsAppUrl())
                                    <a href="{{ $user->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm d-inline-flex align-items-center justify-content-center p-2 rounded" style="background:#25D366; color:white;" title="Contacter sur WhatsApp">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="22" height="22">
                                    </a>
                                @else
                                    <span class="text-muted" title="Aucun numéro">—</span>
                                @endif
                            </td>
                            <td>{{ $user->telephone ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $user->articles_count ?? $user->articles->count() }}</span>
                            </td>
                            <td>
                                @if(($user->reports_received_count ?? 0) > 0)
                                    <a href="{{ route('admin.users.show', $user) }}#user-reports" class="badge bg-danger text-decoration-none" title="{{ $user->open_reports_received_count ?? 0 }} ouvert(s)">
                                        {{ $user->reports_received_count }}
                                        @if(($user->open_reports_received_count ?? 0) > 0)
                                            <span class="ms-1">({{ $user->open_reports_received_count }} ouverts)</span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_blocked)
                                    <span class="status-badge blocked">Bloqué</span>
                                    @if($user->block_reason)
                                        <br><small class="text-muted">{{ Str::limit($user->block_reason, 30) }}</small>
                                    @endif
                                @else
                                    <span class="status-badge active">Actif</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-action btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.add-coins', $user) }}" style="display:inline-flex; gap:6px; align-items:center;">
                                        @csrf
                                        <input type="number" name="amount" min="1" class="form-control form-control-sm" placeholder="Qté" style="width:90px;">
                                        <button type="submit" class="btn btn-sm btn-success" title="Ajouter des coins" onclick="return confirm('Ajouter des coins à {{ $user->name }} ?')">
                                            <i class="fas fa-coins"></i>
                                        </button>
                                    </form>
                                    
                                    @if($user->is_blocked)
                                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-action btn-sm btn-unblock" 
                                                    onclick="return confirm('Débloquer cet utilisateur ?')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn-action btn-sm btn-block" 
                                                onclick="blockUser({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                    
                                    <button type="button" class="btn-action btn-sm btn-delete" 
                                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards View -->
            <div class="d-md-none">
                @foreach($users as $user)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $user->getProfilPhotoUrl() }}" 
                                 alt="Photo de profil"
                                 onerror="this.src='{{ asset('assets/icons/user_default.png') }}';" 
                                 class="rounded-circle me-3" width="50" height="50">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $user->name }}</h6>
                                @if($user->estCertifie())
                                    <i class="fas fa-check-circle text-success" title="Utilisateur certifié"></i>
                                @endif
                                <small class="text-muted d-block">{{ $user->coins ?? 0 }} coins</small>
                                {{-- <small class="text-muted d-block">{{ $user->email }}</small> --}}
                            </div>
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-action btn-sm btn-primary" title="Voir le profil">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($user->getWhatsAppUrl())
                                    <a href="{{ $user->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer" class="btn-action btn-sm d-inline-flex align-items-center justify-content-center rounded" style="background:#25D366; padding:6px;" title="Contacter sur WhatsApp">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="18" height="18">
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.users.add-coins', $user) }}" style="display:inline-flex; gap:6px; align-items:center;">
                                    @csrf
                                    <input type="number" name="amount" min="1" class="form-control form-control-sm" placeholder="Qté" style="width:90px;">
                                    <button type="submit" class="btn btn-sm btn-success" title="Ajouter des coins" onclick="return confirm('Ajouter des coins à {{ $user->name }} ?')">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                </form>
                                
                                @if($user->is_blocked)
                                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-action btn-sm btn-unblock" 
                                                onclick="return confirm('Débloquer cet utilisateur ?')">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn-action btn-sm btn-block" 
                                            onclick="blockUser({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                                
                                <button type="button" class="btn-action btn-sm btn-delete" 
                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-3">
                                <small class="text-muted d-block">Téléphone</small>
                                <small>{{ $user->telephone ?? 'N/A' }}</small>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Articles</small>
                                <span class="badge bg-primary">{{ $user->articles_count ?? $user->articles->count() }}</span>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Signalements</small>
                                @if(($user->reports_received_count ?? 0) > 0)
                                    <span class="badge bg-danger">{{ $user->reports_received_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Statut</small>
                                @if($user->is_blocked)
                                    <span class="status-badge blocked">Bloqué</span>
                                @else
                                    <span class="status-badge active">Actif</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-2 text-center">
                            <small class="text-muted">Inscrit le {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="pagination-wrapper">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                <p class="text-muted">Aucun utilisateur ne correspond à vos critères de recherche.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal pour bloquer un utilisateur -->
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
                    <div class="form-group">
                        <label for="block_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="block_reason" class="form-control" rows="3" 
                                  placeholder="Expliquez pourquoi cet utilisateur est bloqué..."></textarea>
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

<!-- Modal pour supprimer un utilisateur -->
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
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Vous êtes sur le point de supprimer définitivement l'utilisateur <strong id="deleteUserName"></strong>.</p>
                    <p class="text-muted">Tous ses articles et données associées seront également supprimés.</p>
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

@push('scripts')
<script>
function blockUser(userId, userName) {
    const form = document.getElementById('blockUserForm');
    const nameElement = document.getElementById('userName');
    
    form.action = `/admin/users/${userId}/block`;
    nameElement.textContent = userName;
    
    const modal = new bootstrap.Modal(document.getElementById('blockUserModal'));
    modal.show();
}

function deleteUser(userId, userName) {
    const form = document.getElementById('deleteUserForm');
    const nameElement = document.getElementById('deleteUserName');
    
    form.action = `/admin/users/${userId}`;
    nameElement.textContent = userName;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}
</script>
@endpush
