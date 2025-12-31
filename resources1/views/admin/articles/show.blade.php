@extends('admin.layout')

@section('title', 'Détails article')
@section('page-title', 'Détails article')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="admin-card-title">
                        <i class="fas fa-newspaper"></i>
                        {{ $article->titre }}
                    </h5>
                    <div>
                        @if($article->status === 'pending')
                            <span class="status-badge pending">En attente</span>
                        @elseif($article->status === 'approved')
                            <span class="status-badge approved">Approuvé</span>
                        @else
                            <span class="status-badge blocked">Bloqué</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations générales</h6>
                        <div class="info-item">
                            <strong>Titre:</strong>
                            <span>{{ $article->titre }}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Prix:</strong>
                            <span class="text-success fw-bold">{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Lieu:</strong>
                            <span>{{ $article->lieu ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Quantité disponible:</strong>
                            <span>{{ $article->quantite_disponible ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>État:</strong>
                            <span>{{ $article->neuf ? 'Neuf' : 'Occasion' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Livraison:</strong>
                            <span>{{ $article->livraison ? 'Disponible' : 'Non disponible' }}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Informations de publication</h6>
                        <div class="info-item">
                            <strong>Auteur:</strong>
                            <span>
                                <a href="{{ route('admin.users.show', $article->user) }}" class="text-decoration-none">
                                    {{ $article->user->name }}
                                </a>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Catégorie:</strong>
                            <span>{{ $article->sousCategorie->nom ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Date de création:</strong>
                            <span>{{ $article->created_at ? $article->created_at->format('d/m/Y à H:i') : 'N/A' }}</span>
                        </div>
                        
                        @if($article->approved_at)
                        <div class="info-item">
                            <strong>Date d'approbation:</strong>
                            <span>{{ $article->approved_at ? $article->approved_at->format('d/m/Y à H:i') : '' }}</span>
                        </div>
                        @endif
                        
                        @if($article->blocked_at)
                        <div class="info-item">
                            <strong>Date de blocage:</strong>
                            <span>{{ $article->blocked_at ? $article->blocked_at->format('d/m/Y à H:i') : '' }}</span>
                        </div>
                        @endif
                        
                        @if($article->block_reason)
                        <div class="info-item">
                            <strong>Raison du blocage:</strong>
                            <span class="text-danger">{{ $article->block_reason }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Description</h6>
                    <div class="description-content">
                        {{ $article->description }}
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Images</h6>
                    <div class="row">
                        @php
                            $photos = [$article->photo, $article->photo1, $article->photo2, $article->photo3, $article->photo4, $article->photo5, $article->photo6];
                            $photos = array_filter($photos);
                            if (empty($photos)) {
                                $photos = [null]; // Afficher au moins le placeholder
                            }
                        @endphp
                        
                        @foreach($photos as $index => $photo)
                            <div class="col-md-3 mb-3">
                                <img src="{{ $photo ? asset($photo) : asset('images/placeholder.png') }}" 
                                     alt="Photo {{ $index === 0 ? 'principale' : $index }}" 
                                     class="img-fluid rounded" 
                                     style="max-height: 200px; object-fit: cover;"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        @if($article->comments->count() > 0)
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-comments"></i>
                    Commentaires ({{ $article->comments->count() }})
                </h5>
            </div>
            <div class="admin-card-body">
                @foreach($article->comments as $comment)
                <div class="comment-item">
                    <div class="d-flex align-items-start">
                        <img src="{{ $comment->user->getProfilPhotoUrl() }}" alt="Photo de profil" 
                             class="rounded-circle me-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted ms-2">{{ $comment->created_at ? $comment->created_at->format('d/m/Y à H:i') : 'N/A' }}</small>
                                </div>
                            </div>
                            <p class="mt-2 mb-0">{{ $comment->contenu }}</p>
                        </div>
                    </div>
                </div>
                @if(!$loop->last)
                <hr>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-cogs"></i>
                    Actions
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="action-buttons d-grid gap-2">
                    @if($article->status === 'pending')
                        <button type="button" class="btn btn-success w-100" 
                                onclick="approveArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                <i class="fas fa-check"></i> Approuver l'article
                            </button>
                    @endif
                    
                    @if($article->status !== 'blocked')
                        <button type="button" class="btn btn-warning w-100" 
                                onclick="blockArticle({{ $article->id }}, '{{ $article->titre }}')">
                            <i class="fas fa-ban"></i> Bloquer l'article
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-danger w-100" 
                            onclick="deleteArticle({{ $article->id }}, '{{ $article->titre }}')">
                        <i class="fas fa-trash"></i> Supprimer l'article
                    </button>
                    
                    <a href="{{ route('article.details', $article) }}" class="btn btn-info w-100" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Voir sur le site
                    </a>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-user"></i>
                    Informations auteur
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="text-center mb-3">
                    <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                         alt="Photo de profil"
                         onerror="this.src='{{ asset('images/user_default.png') }}';" 
                         class="rounded-circle mb-2" width="80" height="80">
                    <h6>{{ $article->user->name }}</h6>
                    @if($article->user->estCertifie())
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> Certifié
                        </span>
                    @endif
                </div>
                
                <div class="user-info">
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span>{{ $article->user->email }}</span>
                    </div>
                    
                    @if($article->user->telephone)
                    <div class="info-item">
                        <strong>Téléphone:</strong>
                        <span>{{ $article->user->telephone }}</span>
                    </div>
                    @endif
                    
                    <div class="info-item">
                        <strong>Statut:</strong>
                        @if($article->user->is_blocked)
                            <span class="status-badge blocked">Bloqué</span>
                        @else
                            <span class="status-badge active">Actif</span>
                        @endif
                    </div>
                    
                    <div class="info-item">
                        <strong>Articles:</strong>
                        <span>{{ $article->user->articles->count() }}</span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('admin.users.show', $article->user) }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-eye"></i> Voir le profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
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
                    <p>Vous êtes sur le point de bloquer l'article <strong id="articleName"></strong>.</p>
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
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item strong {
    color: var(--dark-color);
    min-width: 150px;
}

.description-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid var(--primary-color);
    white-space: pre-wrap;
}

.comment-item {
    padding: 1rem 0;
}

.user-info .info-item {
    min-width: auto;
}

.user-info .info-item strong {
    min-width: 80px;
}
</style>
@endpush

@push('scripts')
<script>
function approveArticle(articleId, articleTitle) {
    if (confirm(`Approuver l'article "${articleTitle}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/articles/${articleId}/approve`;
        
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        } else {
            // Fallback: chercher le token dans un autre formulaire
            const existingForm = document.querySelector('form');
            if (existingForm) {
                const existingToken = existingForm.querySelector('input[name="_token"]');
                if (existingToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = existingToken.value;
                    form.appendChild(csrfInput);
                }
            }
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function blockArticle(articleId, articleTitle) {
    const form = document.getElementById('blockArticleForm');
    const nameElement = document.getElementById('articleName');
    
    form.action = `/admin/articles/${articleId}/block`;
    nameElement.textContent = articleTitle;
    
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
