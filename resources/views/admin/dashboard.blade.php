@extends('admin.layout')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_users'] }}</div>
        <div class="stat-label">Utilisateurs total</div>
        <i class="fas fa-users stat-icon"></i>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-number">{{ $stats['blocked_users'] }}</div>
        <div class="stat-label">Utilisateurs bloqués</div>
        <i class="fas fa-user-slash stat-icon"></i>
    </div>
    
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_articles'] }}</div>
        <div class="stat-label">Articles total</div>
        <i class="fas fa-newspaper stat-icon"></i>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-number">{{ $stats['pending_articles'] }}</div>
        <div class="stat-label">Articles en attente</div>
        <i class="fas fa-clock stat-icon"></i>
    </div>
    
    <div class="stat-card success">
        <div class="stat-number">{{ $stats['approved_articles'] }}</div>
        <div class="stat-label">Articles approuvés</div>
        <i class="fas fa-check-circle stat-icon"></i>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-number">{{ $stats['blocked_articles'] }}</div>
        <div class="stat-label">Articles bloqués</div>
        <i class="fas fa-ban stat-icon"></i>
    </div>
    <div class="stat-card info">
        <div class="stat-number">{{ $stats['total_coins'] }}</div>
        <div class="stat-label">Coins distribués</div>
        <i class="fas fa-coins stat-icon"></i>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-clock text-warning"></i>
                    Articles récents en attente
                </h5>
            </div>
            <div class="admin-card-body">
                @if($recent_pending_articles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_pending_articles as $article)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($article->titre, 30) }}</strong>
                                    </td>
                                    <td>{{ $article->user->name }}</td>
                                    <td>{{ $article->sousCategorie->nom ?? 'N/A' }}</td>
                                    <td>{{ $article->created_at ? $article->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.articles.show', $article) }}" class="btn-action btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.articles.approve', $article) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-action btn-sm btn-approve" onclick="return confirm('Approuver cet article ?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn-action btn-sm btn-block" onclick="blockArticle({{ $article->id }})">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.articles.index', ['status' => 'pending']) }}" class="btn btn-primary">
                            Voir tous les articles en attente
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <p class="text-muted">Aucun article en attente d'approbation</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-users text-primary"></i>
                    Utilisateurs récents
                </h5>
            </div>
            <div class="admin-card-body">
                @if($recent_users->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recent_users as $user)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <div>
                                @if($user->is_blocked)
                                    <span class="status-badge blocked">Bloqué</span>
                                @else
                                    <span class="status-badge active">Actif</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                            Voir tous les utilisateurs
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-plus text-primary fa-3x mb-3"></i>
                        <p class="text-muted">Aucun utilisateur récent</p>
                    </div>
                @endif
            </div>
        </div>
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
                    <div class="form-group">
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
@endsection

@push('scripts')
<script>
function blockArticle(articleId) {
    const form = document.getElementById('blockArticleForm');
    form.action = `/admin/articles/${articleId}/block`;
    
    const modal = new bootstrap.Modal(document.getElementById('blockArticleModal'));
    modal.show();
}

// Rafraîchissement automatique des données du dashboard
(function() {
    const REFRESH_INTERVAL = 30000; // 30 secondes
    let refreshInterval;

    async function refreshDashboard() {
        try {
            const response = await fetch('{{ route("admin.dashboard") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();

            // Mettre à jour les statistiques
            if (data.stats) {
                updateStatCard('total_users', data.stats.total_users);
                updateStatCard('blocked_users', data.stats.blocked_users);
                updateStatCard('total_articles', data.stats.total_articles);
                updateStatCard('pending_articles', data.stats.pending_articles);
                updateStatCard('approved_articles', data.stats.approved_articles);
                updateStatCard('blocked_articles', data.stats.blocked_articles);
                updateStatCard('total_coins', data.stats.total_coins);
            }

            // Mettre à jour les articles en attente
            if (data.recent_pending_articles) {
                updatePendingArticles(data.recent_pending_articles);
            }

            // Mettre à jour les utilisateurs récents
            if (data.recent_users) {
                updateRecentUsers(data.recent_users);
            }
        } catch (error) {
            console.error('Erreur lors du rafraîchissement:', error);
        }
    }

    function updateStatCard(statKey, value) {
        const cards = {
            'total_users': '.stat-card:first-child .stat-number',
            'blocked_users': '.stat-card.danger:first-of-type .stat-number',
            'total_articles': '.stat-card:nth-child(3) .stat-number',
            'pending_articles': '.stat-card.warning .stat-number',
            'approved_articles': '.stat-card.success .stat-number',
            'blocked_articles': '.stat-card.danger:last-of-type .stat-number',
            'total_coins': '.stat-card.info .stat-number',
        };

        const selector = cards[statKey];
        if (selector) {
            const element = document.querySelector(selector);
            if (element && element.textContent !== value.toString()) {
                // Animation de mise à jour
                element.style.transition = 'all 0.3s';
                element.style.transform = 'scale(1.1)';
                element.textContent = value;
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 300);
            }
        }
    }

    function updatePendingArticles(articles) {
        const tbody = document.querySelector('.admin-card table tbody');
        if (!tbody) return;

        if (articles.length === 0) {
            const cardBody = tbody.closest('.admin-card-body');
            if (cardBody) {
                cardBody.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <p class="text-muted">Aucun article en attente d'approbation</p>
                    </div>
                `;
            }
            return;
        }

        tbody.innerHTML = articles.map(article => `
            <tr>
                <td><strong>${article.titre.length > 30 ? article.titre.substring(0, 30) + '...' : article.titre}</strong></td>
                <td>${article.user_name}</td>
                <td>${article.sous_categorie_nom}</td>
                <td>${article.created_at}</td>
                <td>
                    <div class="action-buttons">
                        <a href="${article.url}" class="btn-action btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="/admin/articles/${article.id}/approve" style="display: inline;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn-action btn-sm btn-approve" onclick="return confirm('Approuver cet article ?')">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <button type="button" class="btn-action btn-sm btn-block" onclick="blockArticle(${article.id})">
                            <i class="fas fa-ban"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateRecentUsers(users) {
        const listGroup = document.querySelector('.list-group.list-group-flush');
        if (!listGroup) return;

        if (users.length === 0) {
            const cardBody = listGroup.closest('.admin-card-body');
            if (cardBody) {
                cardBody.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-user-plus text-primary fa-3x mb-3"></i>
                        <p class="text-muted">Aucun utilisateur récent</p>
                    </div>
                `;
            }
            return;
        }

        listGroup.innerHTML = users.map(user => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${user.name}</strong>
                    <br>
                    <small class="text-muted">${user.email}</small>
                </div>
                <div>
                    ${user.is_blocked 
                        ? '<span class="status-badge blocked">Bloqué</span>' 
                        : '<span class="status-badge active">Actif</span>'}
                </div>
            </div>
        `).join('');
    }

    // Démarrer le rafraîchissement automatique
    document.addEventListener('DOMContentLoaded', function() {
        refreshInterval = setInterval(refreshDashboard, REFRESH_INTERVAL);
    });

    // Nettoyer à la sortie
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) clearInterval(refreshInterval);
    });
})();
</script>
@endpush
