@extends('admin.layout')

@section('title', 'Détails utilisateur')
@section('page-title', 'Détails utilisateur')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-user"></i>
                    Informations utilisateur
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="text-center mb-4">
                    <img src="{{ $user->getProfilPhotoUrl() }}" 
                         alt="Photo de profil" 
                         class="rounded-circle mb-3" 
                         width="100" 
                         height="100"
                         onerror="this.src='{{ asset('images/user_default.png') }}';">
                    <h4>{{ $user->name }}</h4>
                    @if($user->estCertifie())
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> Certifié
                        </span>
                    @endif
                </div>
                
                <div class="user-info">
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                    
                    @if($user->telephone)
                    <div class="info-item">
                        <strong>Téléphone:</strong>
                        <span>{{ $user->telephone }}</span>
                    </div>
                    @endif
                    
                    @if($user->whatsapp)
                    <div class="info-item">
                        <strong>WhatsApp:</strong>
                        <span>{{ $user->whatsapp }}</span>
                    </div>
                    @endif
                    
                    <div class="info-item">
                        <strong>Inscription:</strong>
                        <span>{{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <strong>Statut:</strong>
                        @if($user->is_blocked)
                            <span class="status-badge blocked">Bloqué</span>
                        @else
                            <span class="status-badge active">Actif</span>
                        @endif
                    </div>

                    <div class="info-item">
                        <strong>Coins:</strong>
                        <span class="badge bg-info text-dark">{{ $user->coins ?? 0 }}</span>
                    </div>
                    
                    @if($user->is_blocked && $user->block_reason)
                    <div class="info-item">
                        <strong>Raison du blocage:</strong>
                        <span class="text-muted">{{ $user->block_reason }}</span>
                    </div>
                    @endif
                    
                    @if($user->blocked_at)
                    <div class="info-item">
                        <strong>Bloqué le:</strong>
                        <span>{{ $user->blocked_at ? $user->blocked_at->format('d/m/Y à H:i') : '' }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="mt-4">
                    <h6>Actions</h6>
                    <div class="action-buttons">
                        <form method="POST" action="{{ route('admin.users.add-coins', $user) }}" style="display:inline-flex; gap:6px; align-items:center;">
                            @csrf
                            <input type="number" name="amount" min="1" class="form-control" placeholder="Qté" style="max-width:120px;">
                            <button type="submit" class="btn btn-success" title="Ajouter des coins" onclick="return confirm('Ajouter des coins ?')">
                                <i class="fas fa-coins"></i> Ajouter des coins
                            </button>
                        </form>
                        @if($user->is_blocked)
                            <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-unblock" 
                                        onclick="return confirm('Débloquer cet utilisateur ?')">
                                    <i class="fas fa-unlock"></i> Débloquer
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn-action btn-block" 
                                    onclick="blockUser({{ $user->id }}, '{{ $user->name }}')">
                                <i class="fas fa-ban"></i> Bloquer
                            </button>
                        @endif
                        
                        <button type="button" class="btn-action btn-delete" 
                                onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-newspaper"></i>
                    Articles de l'utilisateur ({{ $user->articles->count() }})
                </h5>
            </div>
            <div class="admin-card-body">
                @if($user->articles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->articles as $article)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($article->titre, 40) }}</strong>
                                    </td>
                                    <td>{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @if($article->status === 'pending')
                                            <span class="status-badge pending">En attente</span>
                                        @elseif($article->status === 'approved')
                                            <span class="status-badge approved">Approuvé</span>
                                        @else
                                            <span class="status-badge blocked">Bloqué</span>
                                        @endif
                                    </td>
                                    <td>{{ $article->created_at ? $article->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.articles.show', $article) }}" class="btn-action btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($article->status === 'pending')
                                                <form method="POST" action="{{ route('admin.articles.approve', $article) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-sm btn-approve" 
                                                            onclick="return confirm('Approuver cet article ?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($article->status !== 'blocked')
                                                <button type="button" class="btn-action btn-sm btn-block" 
                                                        onclick="blockArticle({{ $article->id }})">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                            
                                            <button type="button" class="btn-action btn-sm btn-delete" 
                                                    onclick="deleteArticle({{ $article->id }}, '{{ $article->titre }}')">
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
                        <p class="text-muted">Cet utilisateur n'a pas encore publié d'articles.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
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
                    <div class="form-group">
                        <label for="block_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="block_reason" class="form-control" rows="3" 
                                  placeholder="Expliquez pourquoi cet article est bloqué..."></textarea>
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
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
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
.user-info .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.user-info .info-item:last-child {
    border-bottom: none;
}

.user-info .info-item strong {
    color: var(--dark-color);
    min-width: 120px;
}
</style>
@endpush

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

function blockArticle(articleId) {
    const form = document.getElementById('blockArticleForm');
    form.action = `/admin/articles/${articleId}/block`;
    
    const modal = new bootstrap.Modal(document.getElementById('blockArticleModal'));
    modal.show();
}

function deleteArticle(articleId, articleTitle) {
    const form = document.getElementById('deleteArticleForm');
    const nameElement = document.getElementById('deleteArticleName');
    
    form.action = `/admin/articles/${articleId}`;
    nameElement.textContent = articleTitle;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteArticleModal'));
    modal.show();
}
</script>
@endpush
