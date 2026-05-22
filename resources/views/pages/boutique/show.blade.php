@extends('layouts.boutique')

@section('title', 'Boutique de ' . $user->name)

@section('boutique-header')
<div class="boutique-header-content">
    <img src="{{ $user->getProfilPhotoUrl() }}" 
         alt="Photo de profil de {{ $user->name }}"
         class="profile-avatar"
         onerror="this.src='{{ asset('images/user_default.png') }}';">
    
    <div class="profile-info">
        <h1 class="profile-name">{{ $user->name }}</h1>
        <p class="profile-email">
            <i class="bi bi-box-seam me-1"></i>
            {{ $user->articles()->where('status', 'approved')->count() }} article(s) publié(s)
        </p>
        
        <div class="profile-meta">
            <div class="meta-item">
                <i class="bi bi-calendar3"></i>
                <span>Membre depuis {{ $user->created_at->format('d/m/Y') }}</span>
            </div>
            @if($user->telephone)
            <div class="meta-item">
                <i class="bi bi-telephone"></i>
                <span>{{ $user->telephone }}</span>
            </div>
            @endif
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
    }
    
    .call-buttons .btn-success {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: #fff;
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
           class="btn btn-success">
            <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" width="20" height="20" style="filter: brightness(0) invert(1);"> 
            <span>WhatsApp</span>
        </a>
        @endif
        
        <button class="btn btn-outline-secondary" onclick="shareShop()">
            <i class="bi bi-share-fill"></i>
            <span>Partager</span>
        </button>
    </div>
</div>
@endsection

@section('content')

    {{-- ...le code de la liste des articles... --}}
    <h4>Articles en vente</h4>
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3">
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
                    <a href="{{ route('article.details', ['id' => $article->id]) }}" style="text-decoration: none; color: inherit; border-bottom: 1px solid;">
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
                            <hr style="border-top: 3px solid #000000; width: 100%; margin-bottom: 2px; margin-top: 10px;">

                            <!-- Profil et certification -->
                            <div class="profil-certification">
                                @if($article->isBoosted())
                                    <div class="badge bg-warning text-dark" style="font-size: 0.7rem; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                                @else
                                    @if($article->user)
                                        <div class="user-info">
                                            <a href="{{ route('boutique.show', $article->user->id) }}" style="display: flex; align-items: center; text-decoration: none;">
                                                <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                                                     alt="Profil de {{ $article->user->name }}" 
                                                     class="profile-picture"
                                                     loading="lazy"
                                                     onerror="this.src='{{ asset('images/user_default.png') }}';">
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


@endsection