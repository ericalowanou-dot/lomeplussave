@if($articles->isEmpty())
    <div class="mes-annonces-empty-filtered" style="background: linear-gradient(135deg, rgba(81, 95, 251, 0.08), rgba(81, 95, 251, 0)); border: 1px solid rgba(81, 95, 251, 0.15); border-radius: 18px; padding: 64px 32px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 18px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); margin: 32px 0;">
        <div style="width: 72px; height: 72px; border-radius: 18px; display: grid; place-items: center; background: rgba(81, 95, 251, 0.12); color: #515ffb; font-size: 32px;">
            <i class="bi bi-funnel-x"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 700; color: #111827; margin: 0;">Aucune annonce trouvée</h2>
        <p style="color: #475569; font-size: 1rem; max-width: 420px; margin: 0 auto; line-height: 1.6;">
            @if(request('status') || request('boosted') || request('categorie') || request('date_filter'))
                Aucune annonce ne correspond aux filtres sélectionnés. 
                Essayez de modifier vos critères de recherche ou consultez toutes vos annonces.
            @else
                Vous n'avez pas encore d'annonces correspondant à ce critère.
            @endif
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
            <a href="{{ route('mes_annonces') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 999px; background: linear-gradient(135deg, #515ffb, #3b4fd8); color: #fff; font-weight: 600; text-decoration: none; box-shadow: 0 12px 30px rgba(81, 95, 251, 0.25); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <i class="bi bi-arrow-left"></i>
                <span>Voir toutes mes annonces</span>
            </a>
            @if(request('status') || request('boosted') || request('categorie') || request('date_filter'))
                <a href="{{ route('articles.create') }}" style="display: inline-flex; align-items: center; padding: 10px 16px; border-radius: 999px; border: 1px solid rgba(15, 23, 42, 0.12); color: #1f2937; font-weight: 500; text-decoration: none; transition: background 0.2s ease, border-color 0.2s ease;">
                    <i class="bi bi-plus-circle"></i>
                    <span>Créer une annonce</span>
                </a>
            @endif
        </div>
    </div>
@else
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach($articles as $article)
            <div class="col">
                <div class="card shadow-sm rounded-4 article-hover">
                    
                    <!-- Badge de statut en haut à gauche -->
                    <div class="position-absolute top-0 start-0 p-1" style="z-index: 10;">
                        @if($article->status === 'pending')
                            <span class="status-badge pending" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                <i class="bi bi-clock-history"></i> En attente
                            </span>
                        @elseif($article->status === 'approved')
                            <span class="status-badge approved" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                <i class="bi bi-check-circle"></i> Approuvé
                            </span>
                        @else
                            <span class="status-badge blocked" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                <i class="bi bi-x-circle"></i> Bloqué
                            </span>
                        @endif
                    </div>
                    
                    <!-- Heure en haut à droite -->
                    <div class="position-absolute top-0 end-0 p-1 rounded-bottom-start small d-flex heure">
                        <i class="bi bi-clock" style="padding-right: 5px;"></i>
                        <p class="mb-0">
                            <strong>{{ intval($article->created_at->diffInDays()) }}</strong> jour{{ intval($article->created_at->diffInDays()) > 1 ? 's' : '' }}
                        </p>
                    </div>

                    <!-- Image de l'article -->
                    <a href="{{ route('article.details', ['id' => $article->id]) }}" style="text-decoration: none; color: inherit; border-bottom: 1px solid;">
                        <img class="card-img-top rounded-top-4 article-img-fixed" 
                             src="{{ $article->photo_url }}" 
                             width="100%" 
                             height="150" 
                             alt="Card image cap" 
                             style="object-fit: cover;"
                             onerror="this.src='{{ asset('images/placeholder.png') }}';">
                    </a>

                    <div class="card-body">
                        <div class="card-text">

                            <!-- Prix et Like sur la même ligne -->
                            <div class="article-price-wrapper">
                                <div class="article-price">
                                    <span>{{ intval($article->prix_ht) }} FCFA</span>    
                                </div>
                                <div class="like-container-inline">
                                    <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                        @csrf
                                        <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                        <button type="button" class="like-button-inline" data-article-id="{{ $article->id }}" aria-label="Ajouter aux favoris">
                                            <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon-inline {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                        </button>
                                        <div id="count-js-{{ $article->id }}" class="like-count-inline">
                                            <span class="like-number">{{ $article->usersWhoLiked->count() }}</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="article-title">
                                {{ $article->titre }}
                            </div>

                            <!-- Localisation -->
                            <div class="article-localisation">
                                <img src="{{ asset('images/localisation.png') }}" alt="Localisation" class="localisation-icon">
                                <p class="localisation-text">{{ $article->lieu ?? 'Ville non spécifiée' }}</p>
                            </div>

                            <!-- Séparateur -->
                            <hr style="border-top: 3px solid #000000; width: 100%; margin-bottom: 5px; margin-top: 5px;">

                            <!-- Boutons d'action -->
                            <div class="action-buttons">
                                <!-- Modifier -->
                                <a href="{{ route('articles.edit', $article->id) }}" class="action-btn edit" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Supprimer -->
                                <form action="{{ route('articles.destroy', $article->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-btn delete" onclick="return confirm('Supprimer cet article ?')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                <!-- Transférer -->
                                <button onclick="openShareModal('{{ route('article.details', $article->id) }}')" type="button" class="action-btn transfer" title="Partager">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div> <!-- fin action-buttons -->

                        </div> <!-- fin card-text -->
                    </div> <!-- fin card-body -->
                </div> <!-- fin card -->
            </div> <!-- fin col -->
        @endforeach
    </div> <!-- fin row -->
@endif

<!-- Styles des boutons et badges de statut -->
<style>
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 10px;
        background: none;
        border: none;
        border-radius: 5px;
        padding: 5px;
        justify-content: center;
    }
    .action-btn {
        background: #f5f5f5;
        color: #222;
        border: 1px solid #bbb;
        border-radius: 5px;
        font-size: 0.85rem;
        padding: 3px 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: background 0.2s, color 0.2s, border 0.2s;
    }
    .action-btn.edit { background: #ffe082; color: #795548; border-color: #ffd54f; }
    .action-btn.delete { background: #ffcdd2; color: #b71c1c; border-color: #e57373; }
    .action-btn.transfer { background: #b3e5fc; color: #01579b; border-color: #4fc3f7; }
    .action-btn:hover { filter: brightness(0.95); }
    .action-btn i { font-size: 1em; }
    
    /* Styles pour les badges de statut */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .status-badge.pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-badge.approved {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-badge.blocked {
        background-color: #fee2e2;
        color: #991b1b;
    }
</style>

<!-- Scripts modale et suppression -->
<script>
    function openShareModal(articleUrl) {
        document.getElementById('share-url').value = articleUrl;
        document.getElementById('facebook-share').href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(articleUrl);
        document.getElementById('whatsapp-share').href = "https://wa.me/?text=" + encodeURIComponent(articleUrl);
        document.getElementById('twitter-share').href = "https://twitter.com/intent/tweet?url=" + encodeURIComponent(articleUrl);
        document.getElementById('share-modal').style.display = 'block';
    }

    function closeShareModal() {
        document.getElementById('share-modal').style.display = 'none';
    }

    function copyToClipboard() {
        const urlInput = document.getElementById("share-url");
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // Mobile support
        navigator.clipboard.writeText(urlInput.value).then(() => {
            alert("Lien copié dans le presse-papiers !");
        });
    }

    // Fermer la modale si on clique à l’extérieur
    window.onclick = function(event) {
        const modal = document.getElementById("share-modal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // Confirmation suppression
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if(!confirm('Supprimer cet article ?')) {
                e.preventDefault();
            }
        });
    });
</script>
