@extends('admin.layout')

@section('title', 'Détails article')
@section('page-title', 'Détails article')

@section('content')
<div class="admin-article-detail">
    {{-- Lien retour --}}
    <div class="mb-3">
        <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <div class="row g-4">
        {{-- Colonne principale --}}
        <div class="col-lg-8">
            {{-- En-tête : Image principale + Titre + Prix + Statut --}}
            <div class="admin-card mb-4">
                <div class="article-detail-hero">
                    @php
                        $photos = array_filter([$article->photo, $article->photo1, $article->photo2, $article->photo3, $article->photo4, $article->photo5]);
                    @endphp
                    <div class="article-detail-gallery mb-4">
                        @if(!empty($photos))
                            <div class="row g-2">
                                @foreach($photos as $idx => $photo)
                                    <div class="col-6 col-md-4">
                                        <div class="gallery-item rounded overflow-hidden border" style="aspect-ratio:1; background:#f8f9fa;">
                                            <img src="{{ asset($photo) }}" alt="Photo {{ $idx + 1 }}"
                                                 class="img-fluid w-100 h-100 object-fit-cover"
                                                 style="object-fit: cover;"
                                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 bg-light rounded">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Aucune image</p>
                            </div>
                        @endif
                    </div>
                    <div class="article-detail-header d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <h1 class="h4 mb-2 fw-bold">{{ $article->titre }}</h1>
                            <p class="h5 text-success mb-0 fw-bold">{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            @if($article->status === 'pending')
                                <span class="status-badge pending">En attente</span>
                            @elseif($article->status === 'approved')
                                <span class="status-badge approved">Approuvé</span>
                            @else
                                <span class="status-badge blocked">Bloqué</span>
                            @endif
                            @if($article->boosted_until && $article->boosted_until->isFuture())
                                <span class="badge bg-warning text-dark ms-1">Boosté</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations article --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informations de l'article</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-info-row">
                                <span class="detail-label">Lieu</span>
                                <span class="detail-value">{{ $article->lieu ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-info-row">
                                <span class="detail-label">Catégorie</span>
                                <span class="detail-value">{{ $article->sousCategorie->nom ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-info-row">
                                <span class="detail-label">État</span>
                                <span class="detail-value">{{ $article->neuf ? 'Neuf' : 'Occasion' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-info-row">
                                <span class="detail-label">Livraison</span>
                                <span class="detail-value">{{ $article->livraison ? 'Disponible' : 'Non disponible' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-align-left me-2"></i>Description</h5>
                </div>
                <div class="admin-card-body">
                    <div class="article-description">
                        {{ $article->description }}
                    </div>
                </div>
            </div>

            {{-- Commentaires --}}
            @if($article->comments->count() > 0)
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-comments me-2"></i>Commentaires ({{ $article->comments->count() }})</h5>
                </div>
                <div class="admin-card-body">
                    @foreach($article->comments as $comment)
                    <div class="comment-block {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                        <div class="d-flex align-items-start gap-3">
                            <img src="{{ $comment->user->getProfilPhotoUrl() }}" alt="Profil" class="rounded-circle flex-shrink-0" width="40" height="40" onerror="this.src='{{ asset('images/user_default.png') }}';">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->created_at?->format('d/m/Y H:i') ?? '—' }}</small>
                                </div>
                                <p class="mb-0 text-secondary">{{ $comment->contenu }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Actions --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="admin-card-body">
                    <div class="d-grid gap-2">
                        @if($article->status === 'pending')
                            <button type="button" class="btn btn-success" onclick="approveArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                <i class="fas fa-check me-2"></i> Approuver l'article
                            </button>
                        @endif
                        @if($article->status !== 'blocked')
                            <button type="button" class="btn btn-warning" onclick="blockArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                <i class="fas fa-ban me-2"></i> Bloquer l'article
                            </button>
                        @endif
                        <button type="button" class="btn btn-danger" onclick="deleteArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                            <i class="fas fa-trash me-2"></i> Supprimer l'article
                        </button>
                        <a href="{{ $article->url }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i> Voir sur le site
                        </a>
                    </div>
                </div>
            </div>

            {{-- Auteur --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-user me-2"></i>Auteur</h5>
                </div>
                <div class="admin-card-body text-center">
                    <img src="{{ $article->user->getProfilPhotoUrl() }}" alt="Profil" class="rounded-circle mb-3" width="72" height="72" onerror="this.src='{{ asset('images/user_default.png') }}';" style="object-fit: cover;">
                    <h6 class="mb-1">{{ $article->user->name }}</h6>
                    @if($article->user->estCertifie())
                        <span class="badge bg-success mb-2"><i class="fas fa-check-circle me-1"></i> Certifié</span>
                    @endif
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.users.show', $article->user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> Voir le profil
                        </a>
                        @if($article->user->getWhatsAppUrl())
                            <a href="{{ $article->user->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm d-flex align-items-center justify-content-center gap-2" style="background:#25D366; color:white;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="20" height="20">
                                Contacter par WhatsApp
                            </a>
                        @endif
                    </div>
                    <hr class="my-3">
                    <div class="text-start small">
                        <div class="detail-info-row py-2">
                            <span class="detail-label">Articles</span>
                            <span class="detail-value">{{ $article->user->articles->count() }}</span>
                        </div>
                        <div class="detail-info-row py-2">
                            <span class="detail-label">Statut</span>
                            @if($article->user->is_blocked)
                                <span class="status-badge blocked">Bloqué</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dates & historique --}}
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title mb-0"><i class="fas fa-clock me-2"></i>Historique</h5>
                </div>
                <div class="admin-card-body">
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Créé le</span>
                        <span class="detail-value">{{ $article->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    @if($article->approved_at)
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Approuvé le</span>
                        <span class="detail-value text-success">{{ $article->approved_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($article->blocked_at)
                    <div class="detail-info-row py-2">
                        <span class="detail-label">Bloqué le</span>
                        <span class="detail-value text-danger">{{ $article->blocked_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($article->block_reason)
                    <div class="mt-2 p-2 rounded bg-danger bg-opacity-10">
                        <small class="text-danger fw-semibold">Raison du blocage</small>
                        <p class="mb-0 small">{{ $article->block_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
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
                    <div class="mb-3">
                        <label for="block_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="block_reason" class="form-control" rows="3" placeholder="Expliquez pourquoi cet article est bloqué..."></textarea>
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
.admin-article-detail .article-detail-gallery .gallery-item img { transition: transform 0.2s; }
.admin-article-detail .article-detail-gallery .gallery-item:hover img { transform: scale(1.05); }

.detail-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}
.detail-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}
.detail-value { font-weight: 500; }

.article-description {
    background: var(--light-color);
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    border-left: 4px solid var(--primary-color);
    white-space: pre-wrap;
    line-height: 1.6;
}
</style>
@endpush

@push('scripts')
<script>
function approveArticle(articleId, articleTitle) {
    if (confirm('Approuver l\'article "' + articleTitle + '" ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/articles/' + articleId + '/approve';
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrf.getAttribute('content');
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }
}
function blockArticle(articleId, articleTitle) {
    document.getElementById('blockArticleForm').action = '/admin/articles/' + articleId + '/block';
    document.getElementById('articleName').textContent = articleTitle;
    new bootstrap.Modal(document.getElementById('blockArticleModal')).show();
}
function deleteArticle(articleId, articleTitle) {
    document.getElementById('deleteArticleForm').action = '/admin/articles/' + articleId;
    document.getElementById('deleteArticleName').textContent = articleTitle;
    new bootstrap.Modal(document.getElementById('deleteArticleModal')).show();
}
</script>
@endpush
