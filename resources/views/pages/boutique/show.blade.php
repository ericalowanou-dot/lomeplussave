@extends('layouts.boutique')

@section('title', 'Boutique de ' . $user->name)

@section('boutique-header')
<div class="boutique-header-content">
    <img src="{{ $user->getProfilPhotoUrl() }}" 
         alt="Photo de profil de {{ $user->name }}"
         class="profile-avatar"
         onerror="this.src='{{ asset('images/user_default.svg') }}';">
    
    <div class="profile-info">
        <div class="boutique-header-top">
            <h1 class="profile-name">{{ $user->name }}</h1>
            @unless(auth()->check() && auth()->id() === $user->id)
                @auth
                    @if($hasReportedShop ?? false)
                        <span class="boutique-report-done" title="Vous avez déjà signalé cette boutique">
                            <i class="bi bi-flag-fill"></i> Déjà signalé
                        </span>
                    @else
                        <button type="button" class="boutique-report-btn" id="openShopReportModal" aria-label="Signaler cette boutique">
                            <i class="bi bi-flag"></i> Signaler
                        </button>
                    @endif
                @else
                    <a href="{{ route('login', ['redirect' => urlencode(request()->fullUrl())]) }}" class="boutique-report-btn boutique-report-btn--link">
                        <i class="bi bi-flag"></i> Signaler
                    </a>
                @endauth
            @endunless
        </div>
        <p class="profile-email">
            <i class="bi bi-box-seam me-1"></i>
            {{ $user->articles()->where('status', 'approved')->count() }} article(s) publié(s)
        </p>
        
        <div class="profile-meta">
            <div class="meta-item">
                <i class="bi bi-calendar3"></i>
                <span>Membre depuis {{ $user->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
        
        @if($user->estCertifie())
        <div class="certification-badge">
            <i class="bi bi-patch-check-fill"></i>
            <span>Boutique certifiée</span>
        </div>
        @endif
    </div>
</div>
@endsection

@section('head')
<style>
    .boutique-header-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .boutique-report-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid #dc2626;
        background: #dc2626;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    }

    .boutique-report-btn:hover {
        color: #fff;
        background: #b91c1c;
        border-color: #b91c1c;
        box-shadow: 0 4px 12px rgba(185, 28, 28, 0.45);
        transform: translateY(-1px);
    }

    .boutique-report-btn--link:hover {
        color: #fff;
    }

    .boutique-report-done {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        color: #94a3b8;
        white-space: nowrap;
    }

    .shop-report-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10060;
        background: rgba(15, 23, 42, 0.45);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .shop-report-modal.is-open {
        display: flex;
    }

    .shop-report-modal__dialog {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.2);
        position: relative;
    }

    .shop-report-modal__close {
        position: absolute;
        top: 0.5rem;
        right: 0.75rem;
        border: 0;
        background: transparent;
        font-size: 1.5rem;
        line-height: 1;
        color: #64748b;
        cursor: pointer;
    }

    .shop-report-modal__title {
        margin: 0 0 0.35rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .shop-report-modal__hint {
        margin: 0 0 1rem;
        font-size: 0.85rem;
        color: #64748b;
    }

    .shop-report-modal label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
        color: #334155;
    }

    .shop-report-modal select,
    .shop-report-modal textarea {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem 0.75rem;
        font-size: 0.9rem;
        margin-bottom: 0.9rem;
        box-sizing: border-box;
    }

    .shop-report-modal textarea {
        min-height: 90px;
        resize: vertical;
    }

    .shop-report-modal__actions {
        display: flex;
        gap: 0.6rem;
        justify-content: flex-end;
    }

    .shop-report-modal__actions button {
        border-radius: 10px;
        padding: 0.55rem 0.95rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .shop-report-modal__cancel {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .shop-report-modal__submit {
        background: #dc2626;
        color: #fff;
    }

    .shop-report-modal__submit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .shop-report-modal__error {
        display: none;
        color: #b91c1c;
        font-size: 0.85rem;
        margin: -0.4rem 0 0.8rem;
    }

    .shop-report-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #0f172a;
        color: #fff;
        padding: 0.75rem 1.1rem;
        border-radius: 10px;
        font-size: 0.875rem;
        z-index: 10070;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
        max-width: min(90vw, 360px);
        text-align: center;
    }

    .shop-report-toast.is-visible {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .detail-home {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
        color: #fff;
        padding: 0.75rem 1rem;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(255, 153, 0, 0.35);
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    .detail-home:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 153, 0, 0.45);
        color: #fff;
    }
    
    .detail-home img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .boutique-sidebar {
        position: sticky;
        top: 80px;
    }
    
    .boutique-sidebar h5 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .call-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .call-buttons .btn {
        font-weight: 500;
        font-size: 0.9375rem;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
    }
    
    .call-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .call-buttons .btn-warning {
        background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
        color: #fff;
        box-shadow: 0 3px 10px rgba(255, 153, 0, 0.35);
    }
    
    .call-buttons .btn-whatsapp {
        background: #25D366;
        color: #fff;
        box-shadow: 0 3px 12px rgba(37, 211, 102, 0.45);
    }

    .call-buttons .btn-whatsapp:hover {
        background: #1ebe57;
        color: #fff;
        box-shadow: 0 5px 16px rgba(37, 211, 102, 0.55);
    }

    .call-buttons .btn-whatsapp i {
        font-size: 1.15em;
        line-height: 1;
    }

    .call-buttons .btn-share {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.18);
    }

    .call-buttons .btn-share:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 5px 16px rgba(15, 23, 42, 0.25);
    }
    
    @media (max-width: 768px) {
        .detail-home {
            bottom: 15px;
            left: 15px;
            padding: 0.625rem 0.875rem;
            font-size: 0.8125rem;
        }
        
        .boutique-sidebar {
            position: relative;
            top: 0;
        }

        .call-buttons {
            flex-direction: row;
            align-items: stretch;
            gap: 0.5rem;
        }

        .call-buttons .btn {
            flex: 1 1 0;
            min-width: 0;
            padding: 0.65rem 0.4rem;
            font-size: 0.75rem;
            flex-direction: column;
            gap: 0.25rem;
        }

        .call-buttons .btn img {
            width: 18px;
            height: 18px;
        }
    }
</style>
@endsection

@section('boutique-sidebar')
<a class="detail-home" href="{{ route('articles.index') }}">
    <img src="{{ asset('images/immobilier.png') }}" alt="Accueil">
    <span>Accueil</span>
</a>

<div class="boutique-sidebar">
    <h5><i class="bi bi-info-circle me-2"></i>Actions</h5>
    
    <div class="call-buttons">
        @if($user->telephone)
        <a href="tel:{{ $user->telephone }}" class="btn btn-warning">
            <i class="bi bi-telephone-fill"></i>
            <span>Appeler</span>
        </a>
        @endif
        
        @if($user->whatsapp)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->whatsapp) }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="btn btn-whatsapp">
            <i class="bi bi-whatsapp" aria-hidden="true"></i>
            <span>WhatsApp</span>
        </a>
        @endif
        
        <button type="button" class="btn btn-share" onclick="shareShop()">
            <i class="bi bi-share-fill"></i>
            <span>Partager</span>
        </button>
    </div>
</div>
@endsection

@section('content')

    {{-- ...le code de la liste des articles... --}}
    <h4>Articles en vente</h4>
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach($articles as $article)
            <div class="col">
                <div class="card rounded-4 article-hover">

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
                             loading="lazy"
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

                            <!-- Profil et certification -->
                            <div class="profil-certification">
                                @if($article->isBoosted())
                                    <div class="badge bg-warning text-dark" style="font-size: 0.7rem; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                                @else
                                    @if($article->user)
                                        <div class="user-info">
                                            <a href="{{ $article->user->shop_url }}" style="display: flex; align-items: center; text-decoration: none;">
                                                <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                                                     alt="Profil de {{ $article->user->name }}" 
                                                     class="profile-picture"
                                                     loading="lazy"
                                                     onerror="this.src='{{ asset('images/user_default.svg') }}';">
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

                        </div> <!-- fin card-text -->
                    </div> <!-- fin card-body -->
                </div> <!-- fin card -->
            </div> <!-- fin col -->

            @if($loop->iteration === 6)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-md-none', 'feedId' => 'promo-feed-shop-mobile'])
            @endif
            @if($loop->iteration === 9)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-md-block d-lg-none', 'feedId' => 'promo-feed-shop-tablet'])
            @endif
            @if($loop->iteration === 12)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-lg-block', 'feedId' => 'promo-feed-shop-desktop'])
            @endif

            @if($loop->iteration == 4)
                <!-- Bannière après la 2e ligne sur mobile (4 articles) -->
                <div class="col-12" style="z-index: 1;">
                    <div class="banner-ad my-3">
                        <a id="ad-link" href="https://abonnements.lomeplus.com" target="_blank">
                            <img src="{{ asset('images/annonce.jpg') }}" alt="Publicité" class="ad-img active" loading="lazy">
                            <img src="{{ asset('images/annonce2.png') }}" alt="Publicité" class="ad-img" loading="lazy">
                            <img src="{{ asset('images/annonce3.png') }}" alt="Publicité" class="ad-img" loading="lazy">
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
                        const images = document.querySelectorAll(".banner-ad .ad-img");
                        const adLink = document.getElementById("ad-link");

                        const links = [
                            "https://abonnements.lomeplus.com",
                            "https://lomeplus.com",
                            "https://zhcargo.lomeplus.com"
                        ];

                        let currentIndex = 0;

                        function showNextAd() {
                            const currentImage = images[currentIndex];
                            currentImage.classList.remove("active");
                            currentImage.classList.add("exit");

                            currentIndex = (currentIndex + 1) % images.length;
                            const nextImage = images[currentIndex];

                            adLink.href = links[currentIndex];
                            nextImage.classList.add("active");

                            setTimeout(() => {
                                currentImage.classList.remove("exit");
                            }, 1000);
                        }

                        setInterval(showNextAd, 5000);
                    });
                </script>
            @endif

        @endforeach 
    </div> <!-- fin row -->
    
    
            {{-- Pagination --}}
    <div class="mt-3">
        {{ $articles->links() }}
    </div>

    @auth
        @unless(auth()->id() === $user->id || ($hasReportedShop ?? false))
        <div class="shop-report-modal" id="shopReportModal" role="dialog" aria-modal="true" aria-labelledby="shopReportTitle" hidden>
            <div class="shop-report-modal__dialog">
                <button type="button" class="shop-report-modal__close" id="closeShopReportModal" aria-label="Fermer">&times;</button>
                <h2 class="shop-report-modal__title" id="shopReportTitle">Signaler cette boutique</h2>
                <p class="shop-report-modal__hint">Décrivez le problème. Notre équipe examinera le signalement.</p>
                <form id="shopReportForm" data-no-loader="1">
                    @csrf
                    <label for="shopReportReason">Motif</label>
                    <select name="reason" id="shopReportReason" required>
                        <option value="">Choisir un motif…</option>
                        @foreach(($reportReasons ?? []) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <label for="shopReportMessage">Précisions (optionnel)</label>
                    <textarea name="message" id="shopReportMessage" maxlength="500" placeholder="Ajoutez des détails si besoin…"></textarea>
                    <p class="shop-report-modal__error" id="shopReportError"></p>
                    <div class="shop-report-modal__actions">
                        <button type="button" class="shop-report-modal__cancel" id="cancelShopReport">Annuler</button>
                        <button type="submit" class="shop-report-modal__submit" id="submitShopReport">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="shop-report-toast" id="shopReportToast" role="status"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('shopReportModal');
                const openBtn = document.getElementById('openShopReportModal');
                const form = document.getElementById('shopReportForm');
                const errorEl = document.getElementById('shopReportError');
                const toast = document.getElementById('shopReportToast');
                const submitBtn = document.getElementById('submitShopReport');
                if (!modal || !openBtn || !form) return;

                const closeModal = () => {
                    modal.classList.remove('is-open');
                    modal.hidden = true;
                    errorEl.style.display = 'none';
                    errorEl.textContent = '';
                };

                const openModal = () => {
                    modal.hidden = false;
                    modal.classList.add('is-open');
                };

                const showToast = (message) => {
                    toast.textContent = message;
                    toast.classList.add('is-visible');
                    setTimeout(() => toast.classList.remove('is-visible'), 3200);
                };

                openBtn.addEventListener('click', openModal);
                document.getElementById('closeShopReportModal')?.addEventListener('click', closeModal);
                document.getElementById('cancelShopReport')?.addEventListener('click', closeModal);
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });

                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    errorEl.style.display = 'none';
                    submitBtn.disabled = true;
                    window.hidePageLoader?.();

                    const body = new FormData(form);
                    try {
                        const response = await fetch(@json(route('boutique.report', $user)), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body,
                        });
                        const data = await response.json().catch(() => ({}));
                        window.hidePageLoader?.();

                        if (!response.ok || !data.success) {
                            const firstError = data.errors
                                ? Object.values(data.errors).flat()[0]
                                : null;
                            errorEl.textContent = firstError || data.message || 'Impossible d\'envoyer le signalement.';
                            errorEl.style.display = 'block';
                            submitBtn.disabled = false;
                            return;
                        }

                        closeModal();
                        showToast(data.message || 'Signalement envoyé.');
                        const done = document.createElement('span');
                        done.className = 'boutique-report-done';
                        done.innerHTML = '<i class="bi bi-flag-fill"></i> Déjà signalé';
                        openBtn.replaceWith(done);
                    } catch (err) {
                        window.hidePageLoader?.();
                        errorEl.textContent = 'Erreur réseau. Réessayez.';
                        errorEl.style.display = 'block';
                        submitBtn.disabled = false;
                    }
                });
            });
        </script>
        @endunless
    @endauth

@endsection