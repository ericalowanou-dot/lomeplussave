@extends('admin.layout')

@section('title', 'Gestion des articles')
@section('page-title', 'Gestion des articles')

@section('content')
<div class="search-filters">
    <form method="GET" action="{{ route('admin.articles.index') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="Titre, description ou auteur...">
            </div>
            
            <div class="filter-group">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqués</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            </div>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="admin-card-title">
                <i class="fas fa-newspaper"></i>
                Liste des articles ({{ $articles->total() }})
            </h5>
            
            @if($articles->count() > 0)
            <div class="bulk-actions">
                <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="fas fa-check"></i> Approuver sélectionnés
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkBlock()">
                    <i class="fas fa-ban"></i> Bloquer sélectionnés
                </button>
            </div>
            @endif
        </div>
    </div>
    <div class="admin-card-body">
        @if($articles->count() > 0)
            <form id="bulkForm" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Article</th>
                                <th>Auteur</th>
                                <!-- <th>Catégorie</th>
                                <th>Prix</th> -->
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                            <tr>
                                <td>
                                    <input type="checkbox" name="article_ids[]" value="{{ $article->id }}" class="article-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $article->photo_url }}" 
                                             alt="Photo" 
                                             class="rounded me-2" 
                                             width="50" 
                                             height="50" 
                                             style="object-fit: cover;"
                                             onerror="this.src='{{ asset('images/placeholder.png') }}';">
                                        <div>
                                            <strong>{{ Str::limit($article->titre, 20) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($article->description, 20) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                                             alt="Photo de profil" 
                                             class="rounded-circle me-2" 
                                             width="30" 
                                             height="30"
                                             onerror="this.src='{{ asset('images/user_default.png') }}';">
                                        <span>{{ $article->user->name }}</span>
                                    </div>
                                </td>
                                <!-- <td>{{ $article->sousCategorie->nom ?? 'N/A' }}</td> -->
                                <!-- <td>{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</td> -->
                                <td>
                                    @if($article->status === 'pending')
                                        <span class="status-badge pending">En attente</span>
                                    @elseif($article->status === 'approved')
                                        <span class="status-badge approved">Approuvé</span>
                                        @if($article->approved_at)
                                            <br><small class="text-muted">{{ $article->approved_at ? $article->approved_at->format('d/m/Y') : '' }}</small>
                                        @endif
                                    @else
                                        <span class="status-badge blocked">Bloqué</span>
                                        @if($article->blocked_at)
                                            <br><small class="text-muted">{{ $article->blocked_at ? $article->blocked_at->format('d/m/Y') : '' }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $article->created_at ? $article->created_at->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.articles.show', $article) }}" class="btn-action btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($article->status === 'pending')
                                            <button type="button" class="btn-action btn-sm btn-approve" 
                                                    onclick="approveArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                        @endif
                                        
                                        @if($article->status !== 'blocked')
                                            <button type="button" class="btn-action btn-sm btn-block" 
                                                    onclick="blockArticle({{ $article->id }}, '{{ $article->titre }}')">
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
            </form>
            
            <!-- Mobile Cards View -->
            <div class="d-md-none">
                @foreach($articles as $article)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="article_ids[]" value="{{ $article->id }}" class="form-check-input article-checkbox">
                            </div>
                            <div class="action-buttons">
                                <a href="{{ route('admin.articles.show', $article) }}" class="btn-action btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($article->status === 'pending')
                                    <button type="button" class="btn-action btn-sm btn-approve" 
                                            onclick="approveArticle({{ $article->id }}, '{{ addslashes($article->titre) }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                @endif
                                
                                @if($article->status !== 'blocked')
                                    <button type="button" class="btn-action btn-sm btn-block" 
                                            onclick="blockArticle({{ $article->id }}, '{{ $article->titre }}')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                                
                                <button type="button" class="btn-action btn-sm btn-delete" 
                                        onclick="deleteArticle({{ $article->id }}, '{{ $article->titre }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ $article->photo_url }}" 
                                 alt="Photo" 
                                 class="rounded me-2" 
                                 width="60" 
                                 height="60" 
                                 style="object-fit: cover;"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ Str::limit($article->titre, 40) }}</h6>
                                <small class="text-muted">{{ Str::limit($article->description, 60) }}</small>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">Auteur</small>
                                <div class="d-flex align-items-center justify-content-center">
                                    <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                                         alt="Photo de profil" 
                                         class="rounded-circle me-1" 
                                         width="20" 
                                         height="20"
                                         onerror="this.src='{{ asset('images/user_default.png') }}';">
                                    <small>{{ Str::limit($article->user->name, 15) }}</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Prix</small>
                                <strong class="text-success">{{ number_format($article->prix_ht, 0, ',', ' ') }} FCFA</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Statut</small>
                                @if($article->status === 'pending')
                                    <span class="status-badge pending">En attente</span>
                                @elseif($article->status === 'approved')
                                    <span class="status-badge approved">Approuvé</span>
                                @else
                                    <span class="status-badge blocked">Bloqué</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-2 text-center">
                            <small class="text-muted">{{ $article->created_at ? $article->created_at->format('d/m/Y') : 'N/A' }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="pagination-wrapper">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun article trouvé</h5>
                <p class="text-muted">Aucun article ne correspond à vos critères de recherche.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal pour bloquer un article -->
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

<!-- Modal pour supprimer un article -->
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

<!-- Modal pour bloquer plusieurs articles -->
<div class="modal fade" id="bulkBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bloquer plusieurs articles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkBlockForm" method="POST" action="{{ route('admin.articles.bulk-block') }}">
                @csrf
                <div class="modal-body">
                    <p>Vous êtes sur le point de bloquer <span id="bulkBlockCount"></span> article(s).</p>
                    <div class="form-group">
                        <label for="bulk_block_reason" class="form-label">Raison du blocage (optionnel)</label>
                        <textarea name="reason" id="bulk_block_reason" class="form-control" rows="3" 
                                  placeholder="Expliquez pourquoi ces articles sont bloqués..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Bloquer les articles</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.article-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function approveArticle(articleId, articleTitle) {
        if (confirm(`Approuver l'article "${articleTitle}" ?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/articles/${articleId}/approve`;
            
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

    function bulkApprove() {
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Veuillez sélectionner au moins un article.');
            return;
        }
        
        if (confirm(`Approuver ${checkedBoxes.length} article(s) sélectionné(s) ?`)) {
            const form = document.getElementById('bulkForm');
            form.action = '{{ route("admin.articles.bulk-approve") }}';
            form.submit();
        }
    }

    function bulkBlock() {
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Veuillez sélectionner au moins un article.');
            return;
        }
        
        const countElement = document.getElementById('bulkBlockCount');
        countElement.textContent = checkedBoxes.length;
        
        const modal = new bootstrap.Modal(document.getElementById('bulkBlockModal'));
        modal.show();
    }

    // Update bulk form when modal is shown
    document.getElementById('bulkBlockModal').addEventListener('show.bs.modal', function() {
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        const form = document.getElementById('bulkBlockForm');
        
        // Clear previous hidden inputs
        form.querySelectorAll('input[name="article_ids[]"]').forEach(input => input.remove());
        
        // Add selected article IDs
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'article_ids[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
    });
</script>
@endpush
