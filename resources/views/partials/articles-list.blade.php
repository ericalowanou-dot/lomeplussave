@php
    $articles = $articles ?? $favoris ?? collect();
    $showOwnerControls = !empty($showOwnerControls);
    $showPromoFeed = isset($showPromoFeed) ? (bool) $showPromoFeed : ! $showOwnerControls;
    $feedIdPrefix = $feedIdPrefix ?? 'promo-feed';
@endphp

@if($articles->isEmpty())
    <p class="text-center" style="color: red; font-weight: bold;">Aucun résultat trouvé.</p>
@else
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach($articles as $article)
            <div class="col">
                <div class="card rounded-4 article-hover{{ $showOwnerControls ? ' shadow-sm' : '' }}">

                    @if($showOwnerControls)
                        <div class="position-absolute top-0 start-0 p-1" style="z-index: 10;">
                            @if($article->status === 'pending')
                                <span class="status-badge pending">
                                    <i class="bi bi-clock-history"></i> En attente
                                </span>
                            @elseif($article->status === 'approved')
                                <span class="status-badge approved">
                                    <i class="bi bi-check-circle"></i> Approuvé
                                </span>
                            @else
                                <span class="status-badge blocked">
                                    <i class="bi bi-x-circle"></i> Bloqué
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Heure en haut à droite -->
                    <div class="position-absolute top-0 end-0 px-1 py-0 rounded-bottom-start small d-flex heure">
                        @php
                            $diffHours = $article->created_at->diffInHours();
                        @endphp

                        @if ($diffHours < 24)
                            <p class="mb-0">
                                <strong>Récent</strong>
                            </p>
                        @endif
                    </div>

                    <!-- Image de l'article -->
                    <a href="{{ $article->url }}" style="text-decoration: none; color: inherit; border-bottom: 1px solid;">
                        <img class="card-img-top rounded-top-4 article-img-fixed"
                             src="{{ $article->photo_url }}"
                             width="100%"
                             height="150"
                             alt="Card image cap"
                             style="object-fit: cover;"
                             loading="lazy"
                             onerror="this.src='{{ asset('images/placeholder.png') }}';">
                    </a>

                    <div class="card-body">
                        <div class="card-text">

                            <!-- Prix et Like sur la même ligne -->
                            <div class="article-price-wrapper">
                                <div class="article-price">
                                    <span>{{ number_format($article->prix_ht, 0, '', '.') }} CFA</span>
                                </div>
                                <div class="like-container-inline">
                                    <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                        @csrf
                                        <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                        <button type="button" class="like-button-inline" data-article-id="{{ $article->id }}" aria-label="Ajouter aux favoris">
                                            <i class="bi bi-heart{{ $article->isLikedByCurrentUser($likedIds ?? null) ? '-fill' : '' }} like-icon-inline {{ $article->isLikedByCurrentUser($likedIds ?? null) ? 'liked' : '' }}"></i>
                                        </button>
                                        <div id="count-js-{{ $article->id }}" class="like-count-inline">
                                            <span class="like-number">{{ $article->users_who_liked_count ?? 0 }}</span>
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
                            <hr style="border-top: 2px solid #000; width: 100%; margin: 3px 0;">

                            @if($showOwnerControls)
                                <div class="action-buttons">
                                    <a href="{{ route('articles.edit', $article->id) }}" class="action-btn edit" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('articles.destroy', $article->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-btn delete" onclick="return confirm('Supprimer cet article ?')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                    <button onclick="openShareModal('{{ $article->url }}')" type="button" class="action-btn transfer" title="Partager">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                </div>
                            @else
                                <!-- Profil / Pro -->
                                <div class="profil-certification">
                                    @if($article->isBoosted())
                                        <div class="badge bg-warning text-dark" style="font-size: 0.7rem; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                                    @else
                                        @if($article->user)
                                            <div class="user-info">
                                                <a href="{{ $article->user->shop_url }}" style="display: flex; align-items: center; text-decoration: none;">
                                                    <img src="{{ $article->user->getProfilPhotoUrl() }}" alt="Profil de {{ $article->user->name }}" class="profile-picture"
                                                         onerror="this.src='{{ asset('assets/icons/user_default.svg') }}';">
                                                    <p class="text-muted user-name mb-0{{ $article->user->estCertifie() ? '' : ' not-certified' }}">
                                                        {{ $article->user->name ?? 'nom non spécifiée' }}
                                                    </p>
                                                </a>
                                            </div>
                                        @endif

                                        @if($article->user && $article->user->estCertifie())
                                            <div class="certification">
                                                <img src="{{ asset('images/certifier.png') }}" alt="Certifié" class="certification-logo">
                                                <span class="certification-text">Vérifié</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif

                        </div> <!-- fin card-text -->
                    </div> <!-- fin card-body -->
                </div> <!-- fin card -->
            </div> <!-- fin col -->

            @if($showPromoFeed)
                {{-- Section pubs après 3 lignes (6 / 9 / 12 selon le nombre de colonnes) --}}
                @if($loop->iteration === 6)
                    @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-md-none', 'feedId' => $feedIdPrefix . '-mobile'])
                @endif
                @if($loop->iteration === 9)
                    @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-md-block d-lg-none', 'feedId' => $feedIdPrefix . '-tablet'])
                @endif
                @if($loop->iteration === 12)
                    @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-lg-block', 'feedId' => $feedIdPrefix . '-desktop'])
                @endif

                @if($loop->iteration == 4)
                    @guest
                    <!-- Bannière après la 2e ligne sur mobile (4 articles) - Visible seulement si non connecté -->
                    <div class="col-12" style="z-index: 1;">
                        <div class="banner-ad my-3">
                            <a href="{{ route('login') }}">
                                <img src="{{ asset('images/1.png') }}" alt="Publicité" class="ad-img active" loading="lazy">
                                <img src="{{ asset('images/2.png') }}" alt="Publicité" class="ad-img" loading="lazy">
                                <img src="{{ asset('images/3.png') }}" alt="Publicité" class="ad-img" loading="lazy">
                            </a>
                        </div>
                    </div>

                    <style>
                        .banner-ad {
                            width: 100%;
                            height: 150px;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            margin: 2px 0;
                            box-shadow: 0 4px 16px rgba(0,0,0,0.18), 0 1.5px 4px rgba(0,0,0,0.12);
                            border-radius: 10px;
                            background: #fff;
                            position: relative;
                            overflow: hidden;
                        }

                        .banner-ad .ad-img {
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            border-radius: 10px;
                            top: 0;
                            left: 0;
                            opacity: 0;
                            transition: opacity 1s ease-in-out;
                        }

                        .banner-ad .ad-img.active {
                            opacity: 1;
                            z-index: 2;
                        }

                        .banner-ad .ad-img.exit {
                            opacity: 0;
                            z-index: 1;
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const banner = document.querySelector(".banner-ad");
                            if (banner) banner.dataset.loginUrl = "{{ route('login') }}";
                        });
                    </script>
                    @endguest
                @endif
            @endif

        @endforeach
    </div> <!-- fin row -->
@endif

@if($showOwnerControls)
@once
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
    .status-badge.pending { background-color: #fef3c7; color: #92400e; }
    .status-badge.approved { background-color: #d1fae5; color: #065f46; }
    .status-badge.blocked { background-color: #fee2e2; color: #991b1b; }
</style>
<script>
    if (typeof openShareModal !== 'function') {
        function openShareModal(articleUrl) {
            const shareUrl = document.getElementById('share-url');
            const facebookShare = document.getElementById('facebook-share');
            const whatsappShare = document.getElementById('whatsapp-share');
            const twitterShare = document.getElementById('twitter-share');
            const modal = document.getElementById('share-modal');
            if (shareUrl) shareUrl.value = articleUrl;
            if (facebookShare) facebookShare.href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(articleUrl);
            if (whatsappShare) whatsappShare.href = "https://wa.me/?text=" + encodeURIComponent(articleUrl);
            if (twitterShare) twitterShare.href = "https://twitter.com/intent/tweet?url=" + encodeURIComponent(articleUrl);
            if (modal) modal.style.display = 'block';
        }
    }
    if (typeof closeShareModal !== 'function') {
        function closeShareModal() {
            const modal = document.getElementById('share-modal');
            if (modal) modal.style.display = 'none';
        }
    }
    if (typeof copyToClipboard !== 'function') {
        function copyToClipboard() {
            const urlInput = document.getElementById("share-url");
            if (!urlInput) return;
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(urlInput.value).then(() => {
                alert("Lien copié dans le presse-papiers !");
            });
        }
    }
</script>
@endonce
@endif
