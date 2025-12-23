@extends('layouts.app2')

@section('title', $article->titre . ' - Lome+')

@section('meta')
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $article->titre }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($article->description), 160) }}">
    @if($article->images && count($article->images) > 0)
        <meta property="og:image" content="{{ asset($article->images[0]) }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @else
        <meta property="og:image" content="{{ asset('images/true-logo.png') }}">
    @endif
    <meta property="og:site_name" content="Lome+">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $article->titre }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($article->description), 160) }}">
    @if($article->images && count($article->images) > 0)
        <meta name="twitter:image" content="{{ asset($article->images[0]) }}">
    @else
        <meta name="twitter:image" content="{{ asset('images/true-logo.png') }}">
    @endif
    
    <!-- Description générale -->
    <meta name="description" content="{{ Str::limit(strip_tags($article->description), 160) }}">
@endsection

@php
    // Message pré-rédigé pour WhatsApp (contact vendeur)
    $whatsappMessage = "Bonjour, je suis intéressé(e) par votre article \"" . $article->titre . "\" à " . number_format($article->prix, 0, ',', ' ') . " FCFA sur Lome+. Est-il toujours disponible ?\n\n" . url()->current();
    $whatsappMessageEncoded = urlencode($whatsappMessage);
    
    // Message pour le partage
    $shareMessage = "Découvrez cet article sur Lome+ : " . $article->titre . " à " . number_format($article->prix, 0, ',', ' ') . " FCFA\n";
    $shareMessageEncoded = urlencode($shareMessage . url()->current());
@endphp

@section('content')


            <div class="detail-container">
                <a href="{{route('articles.index')}}" class="retour-accueil">Accueil</a>
                
                <div class="row g-4 align-items-start detail-grid">
                    <div class="col-md-7 col-lg-7">
                        @php
                            // Définir $images une seule fois au début avec placeholder si nécessaire
                            $images = array_filter([
                                $article->photo, 
                                $article->photo1 ?? null, 
                                $article->photo2 ?? null, 
                                $article->photo3 ?? null,
                                $article->photo4 ?? null, 
                                $article->photo5 ?? null,
                                $article->photo6 ?? null,
                            ]);
                            // Si aucune image, ajouter le placeholder
                            if (empty($images)) {
                                $images = [null]; // null sera remplacé par placeholder dans la vue
                            }
                        @endphp

    <!-- Données structurées JSON-LD pour le SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "{{ addslashes($article->titre) }}",
        "description": "{{ addslashes(Str::limit(strip_tags($article->description), 200)) }}",
        "image": [
            @foreach($images as $index => $image)
                @if($image)
                    "{{ url($image) }}"{{ !$loop->last ? ',' : '' }}
                @endif
            @endforeach
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $article->prix_ht }}",
            "priceCurrency": "XOF",
            "availability": "https://schema.org/{{ $article->status === 'approved' ? 'InStock' : 'OutOfStock' }}",
            "itemCondition": "https://schema.org/{{ $article->neuf ? 'NewCondition' : 'UsedCondition' }}"
        },
        "seller": {
            "@type": "Person",
            "name": "{{ addslashes($article->user->name) }}"
        },
        "category": "{{ addslashes($article->sousCategorie->nom ?? '') }}",
        "brand": {
            "@type": "Brand",
            "name": "Lome Plus"
        }
    }
    </script>

                        <!-- Vignettes mobiles (scrollable) - en haut -->
                        <div class="thumbs-mobile d-lg-none mb-2">
                            <div class="thumbs-scroll">
                                @foreach($images as $idx => $image)
                                    <button class="thumb-mobile {{ $idx === 0 ? 'active' : '' }}" type="button" data-bs-target="#carouselArticle" data-bs-slide-to="{{ $idx }}" aria-label="Voir l'image {{ $idx + 1 }} de {{ $article->titre }}">
                                        <img src="{{ $image ? asset($image) : asset('images/placeholder.png') }}" alt="Vignette {{ $idx + 1 }} : {{ $article->titre }}" loading="lazy" onerror="this.src='{{ asset('images/placeholder.png') }}';" />
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Carrousel d'images -->
                        <div id="carouselArticle" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <div class="image-detail">
                                <div class="carousel-inner">
                                    @forelse($images as $index => $image)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <img src="{{ $image ? asset($image) : asset('images/placeholder.png') }}" class="d-block rounded image-clickable" alt="Image de l'article" data-image-index="{{ $index }}" style="cursor: pointer;" loading="{{ $loop->first ? 'eager' : 'lazy' }}" onerror="this.src='{{ asset('images/placeholder.png') }}';">
                                        </div>
                                    @empty
                                        <div class="carousel-item active">
                                            <img src="{{ asset('images/placeholder.png') }}" class="d-block rounded image-clickable" alt="{{ $article->titre }} - Image par défaut" data-image-index="0" style="cursor: pointer;">
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @if (count($images) > 1)
                                <!-- Flèche de navigation droite uniquement -->
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselArticle" data-bs-slide="next" aria-label="Image suivante">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Vignettes (desktop) - en bas -->
                        <div class="thumbs d-none d-lg-flex gap-2 mt-2">
                            @foreach($images as $idx => $image)
                                <button class="thumb {{ $idx === 0 ? 'active' : '' }}" type="button" data-bs-target="#carouselArticle" data-bs-slide-to="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}">
                                    <img src="{{ $image ? asset($image) : asset('images/placeholder.png') }}" alt="Vignette {{ $idx + 1 }} : {{ $article->titre }}" loading="lazy" onerror="this.src='{{ asset('images/placeholder.png') }}';" />
                                </button>
                            @endforeach
                        </div>

                        <!-- Conteneur mobile organisé (similaire à la sidebar desktop) -->
                        <div class="detail-sidebar-mobile d-lg-none mt-3">
                            <!-- Prix et actions -->
                            <div class="price-actions-mobile">
                                <div class="price-mobile">{{ number_format($article->prix_ht, 0, '', '.') }} CFA</div>
                                <div class="actions-mobile">
                                    <div class="like-container-detail">
                                        <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}-mobile">
                                            @csrf
                                            <input id="post-id-js-{{ $article->id }}-mobile" type="hidden" name="article_id" value="{{ $article->id }}">
                                            <button type="button" class="like-button-detail" data-article-id="{{ $article->id }}">
                                                <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon-detail {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="share-container-detail">
                                        <button onclick="openShareModal()" type="button" class="share-button-detail">
                                            <img src="{{ asset('images/partager.png') }}" alt="Partager" class="share-icon">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA vendeur -->
                            <div class="cta-seller-mobile">
                                <a href="tel:{{ $article->user->telephone }}" class="btn btn-warning">📞 Appeler</a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $article->user->whatsapp) }}?text={{ $whatsappMessageEncoded }}" target="_blank" class="btn btn-success">
                                    <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" width="20"> WhatsApp
                                </a>
                            </div>

                            <!-- Titre -->
                            <div class="mobile-sidebar-header">
                                <h1 class="product-title-mobile">{{ $article->titre }}</h1>
                            </div>

                            <!-- Métadonnées produit -->
                            <div class="product-meta-mobile">
                                <div class="meta-item-mobile">
                                    <span class="meta-label-mobile">📍 Localisation</span>
                                    <span class="meta-value-mobile">{{ $article->lieu ?? 'Non spécifiée' }}</span>
                                </div>
                                <div class="meta-item-mobile">
                                    <span class="meta-label-mobile">🕒 Publié</span>
                                    <span class="meta-value-mobile">{{ $article->created_at->diffForHumans() }}</span>
                                </div>
                                @if($article->isBoosted())
                                <div class="meta-item-mobile">
                                    <span class="meta-label-mobile">⚡ Statut</span>
                                    <span class="meta-value-mobile">Pro</span>
                                </div>
                                @endif
                            </div>

                            <!-- Détails du produit mobile -->
                            <div class="product-details-section-mobile">
                                <h3 class="details-post-titre-mobile-title">Détails du produit</h3>
                                
                                <!-- Informations principales -->
                                <div class="product-specs">
                                    <!-- État du produit -->
                                    <div class="spec-item">
                                        <div class="spec-icon">📦</div>
                                        <div class="spec-content">
                                            <span class="spec-label">État</span>
                                            <span class="spec-value {{ $article->neuf ? 'spec-neuf' : 'spec-occasion' }}">
                                                {{ $article->neuf ? 'Neuf' : 'Occasion' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Livraison -->
                                    <div class="spec-item">
                                        <div class="spec-icon">🚚</div>
                                        <div class="spec-content">
                                            <span class="spec-label">Livraison</span>
                                            <span class="spec-value {{ $article->livraison ? 'spec-available' : 'spec-unavailable' }}">
                                                {{ $article->livraison ? 'Disponible' : 'Non disponible' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Catégorie -->
                                    @if($article->sousCategorie)
                                    <div class="spec-item">
                                        <div class="spec-icon">🏷️</div>
                                        <div class="spec-content">
                                            <span class="spec-label">Catégorie</span>
                                            <span class="spec-value">{{ $article->sousCategorie->nom }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Description -->
                                <div class="product-description-section">
                                    <h4 class="description-title">Description</h4>
                                    <div class="description-content" id="description-desktop">
                                        <div class="description-text">
                                            {!! nl2br(e(trim($article->description))) !!}
                                    </div>
                                        <button class="read-more-btn" id="read-more-desktop" style="display: none;">
                                            <span class="read-more-text">Lire plus</span>
                                            <span class="read-less-text" style="display: none;">Lire moins</span>
                                    </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-5 col-lg-5">
                        <div class="detail-sidebar">
                            <!-- En-tête: Titre + prix (desktop) -->
                            <div class="d-none d-lg-block sidebar-header">
                                <h1 class="product-title">{{ $article->titre }}</h1>
                                <div class="price-actions">
                                    <div class="price">{{ number_format($article->prix_ht, 0, '', '.') }} CFA</div>
                                    <div class="actions">
                                        <div class="like-container-detail">
                                            <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}-desktop">
                                                @csrf
                                                <input id="post-id-js-{{ $article->id }}-desktop" type="hidden" name="article_id" value="{{ $article->id }}">
                                                <button type="button" class="like-button-detail" data-article-id="{{ $article->id }}">
                                                    <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon-detail {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <button onclick="openShareModal()" type="button" class="btn btn-light btn-share" aria-label="Partager cet article">
                                            Partager
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA vendeur (desktop) -->
                            <div class="cta-seller d-none d-lg-flex gap-2">
                                <a href="tel:{{ $article->user->telephone }}" class="btn btn-warning w-50">📞 Appeler</a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $article->user->whatsapp) }}?text={{ $whatsappMessageEncoded }}" target="_blank" class="btn btn-success w-50">
                                    <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" width="20"> WhatsApp
                                </a>
                            </div>

                            <!-- Meta produit -->
                            <div class="product-meta d-none d-lg-grid">
                                <div class="meta-item">
                                    <span class="meta-label">Localisation</span>
                                    <span class="meta-value">{{ $article->lieu ?? 'Non spécifiée' }}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Publié</span>
                                    <span class="meta-value">{{ $article->created_at->diffForHumans() }}</span>
                                </div>
                                @if($article->isBoosted())
                                <div class="meta-item">
                                    <span class="meta-label">Statut</span>
                                    <span class="meta-value">Pro</span>
                                </div>
                                @endif
                            </div>

                            <!-- Détails du produit -->
                            <div class="product-details-section">
                                <h3 class="details-post-titre">Détails du produit</h3>
                                
                                <!-- Informations principales -->
                                <div class="product-specs">
                                    <!-- État du produit -->
                                    <div class="spec-item">
                                        <div class="spec-icon">📦</div>
                                        <div class="spec-content">
                                            <span class="spec-label">État</span>
                                            <span class="spec-value {{ $article->neuf ? 'spec-neuf' : 'spec-occasion' }}">
                                                {{ $article->neuf ? 'Neuf' : 'Occasion' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Livraison -->
                                    <div class="spec-item">
                                        <div class="spec-icon">🚚</div>
                                        <div class="spec-content">
                                            <span class="spec-label">Livraison</span>
                                            <span class="spec-value {{ $article->livraison ? 'spec-available' : 'spec-unavailable' }}">
                                                {{ $article->livraison ? 'Disponible' : 'Non disponible' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Catégorie -->
                                    @if($article->sousCategorie)
                                    <div class="spec-item">
                                        <div class="spec-icon">🏷️</div>
                                        <div class="spec-content">
                                            <span class="spec-label">Catégorie</span>
                                            <span class="spec-value">{{ $article->sousCategorie->nom }}</span>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Localisation -->
                                    @if($article->lieu)
                                    <div class="spec-item">
                                        <div class="spec-icon">📍</div>
                                        <div class="spec-content">
                                            <span class="spec-label">Localisation</span>
                                            <span class="spec-value">{{ $article->lieu }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Description -->
                                <div class="product-description-section">
                                    <h4 class="description-title">Description</h4>
                                    <div class="description-content" id="description-mobile">
                                        <div class="description-text">
                                            {!! nl2br(e(trim($article->description))) !!}
                                        </div>
                                        <button class="read-more-btn" id="read-more-mobile" style="display: none;">
                                            <span class="read-more-text">Lire plus</span>
                                            <span class="read-less-text" style="display: none;">Lire moins</span>
                                        </button>
                                    </div>
                                </div>
                            </div> 
                        <!-- Section Avis Professionnelle -->
                        <div class="ecrire-voir-commentaire">
                            <div class="comments-section-header">
                                <h6 class="comments-title">
                                    <i class="bi bi-chat-square-text-fill"></i>
                                    Avis
                                </h6>
                                <span class="comments-count">{{ $article->comments->count() }}</span>
                            </div>

                            <!-- Liste des commentaires -->
                            <div class="comments-container" id="commentsContainer">
                                @forelse($article->comments->take(10) as $comment)
                                    <div class="comment-item" data-comment-id="{{ $comment->id }}">
                                        <!-- Avatar -->
                                        <img src="{{ $comment->user->getProfilPhotoUrl() }}" 
                                             alt="{{ $comment->user->name }}" 
                                             class="comment-avatar"
                                             loading="lazy"
                                             onerror="this.src='{{ asset('images/user_default.png') }}';">
                                        
                                        <!-- Contenu du commentaire -->
                                        <div class="comment-body">
                                            <div class="comment-meta">
                                                <span class="comment-author">{{ $comment->user->name }}</span>
                                                @if($comment->user->estCertifie())
                                                    <span class="comment-badge-certified" title="Certifié">
                                                        <i class="bi bi-patch-check-fill"></i>
                                                    </span>
                                                @endif
                                                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                                                @if($comment->created_at != $comment->updated_at)
                                                    <span class="comment-edited">(modifié)</span>
                                                @endif
                                            </div>
                                            <p class="comment-text" id="comment-text-{{ $comment->id }}" data-full-text="{{ $comment->content }}">
                                                @if(strlen($comment->content) > 150)
                                                    <span class="comment-short-text">{{ substr($comment->content, 0, 150) }}</span><span class="comment-ellipsis">...</span>
                                                    <span class="comment-full-text" style="display: none;">{{ substr($comment->content, 150) }}</span>
                                                    <button type="button" class="comment-see-more" data-comment-id="{{ $comment->id }}">Voir plus</button>
                                                @else
                                                    {{ $comment->content }}
                                                @endif
                                            </p>
                                            
                                            <!-- Actions (visible au hover) -->
                                            @auth
                                                <div class="comment-actions">
                                                    @if(auth()->id() == $comment->user_id || auth()->user()->isAdmin())
                                                        <button class="comment-action-btn edit-comment-btn" 
                                                                data-comment-id="{{ $comment->id }}"
                                                                title="Modifier">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="comment-action-btn delete-comment-btn" 
                                                                data-comment-id="{{ $comment->id }}"
                                                                title="Supprimer">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                    @if(auth()->id() != $comment->user_id)
                                                        <button class="comment-action-btn report-comment-btn" 
                                                                data-comment-id="{{ $comment->id }}"
                                                                title="Signaler">
                                                            <i class="bi bi-flag"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endauth
                                            <!-- Formulaire d'édition (caché par défaut) -->
                                            <div class="comment-edit-form" id="edit-form-{{ $comment->id }}" style="display: none;">
                                                <textarea class="comment-edit-textarea" 
                                                          id="edit-textarea-{{ $comment->id }}"
                                                          maxlength="1000">{{ $comment->content }}</textarea>
                                                <div class="comment-edit-actions">
                                                    <button class="btn-save-edit" data-comment-id="{{ $comment->id }}">
                                                        <i class="bi bi-check"></i> Enregistrer
                                                    </button>
                                                    <button class="btn-cancel-edit" data-comment-id="{{ $comment->id }}">
                                                        <i class="bi bi-x"></i> Annuler
                                                    </button>
                                                </div>
                                            </div>
                                        </div><!-- /.comment-body -->
                                    </div><!-- /.comment-item -->
                                @empty
                                    <div class="no-comments">
                                        <i class="bi bi-chat"></i>
                                        <p>Aucun avis pour le moment. Soyez le premier à laisser un avis !</p>
                                    </div>
                                @endforelse
                                
                                @if($article->comments->count() > 10)
                                <div class="load-more-comments">
                                    <button class="btn-load-more" id="loadMoreComments">
                                        Voir tous les avis ({{ $article->comments->count() }})
                                    </button>
                                </div>
                                @endif
                            </div>

                            <!-- Formulaire d'ajout de commentaire -->
                            <form id="commentForm" class="comment-form" action="{{ route('comments.store', $article->id) }}" method="POST">
                                @csrf
                                <div class="comment-form-container">
                                    @auth
                                    <img src="{{ auth()->user()->getProfilPhotoUrl() }}" 
                                         alt="{{ auth()->user()->name }}" 
                                         class="comment-form-avatar"
                                         onerror="this.src='{{ asset('images/user_default.png') }}';">
                                    @else
                                    <img src="{{ asset('images/user_default.png') }}" 
                                         alt="Utilisateur" 
                                         class="comment-form-avatar">
                                    @endauth
                                    <div class="comment-form-input-wrapper">
                                        <textarea name="content" 
                                                  id="content" 
                                                  class="comment-form-textarea" 
                                                  placeholder="Laisser un avis..." 
                                                  required 
                                                  maxlength="1000"
                                                  rows="1"></textarea>
                                        <div class="comment-form-footer">
                                            <button type="submit" class="comment-submit-btn" id="commentSubmitBtn">
                                                <i class="bi bi-send-fill"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> 
               
                        <!-- Modal Connexion/Inscription -->
                        <div id="modal-auth" class="modal" style="display: none;">
                            <div class="modal-content" style="max-width: 350px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; text-align: center; position: relative;">
                                <span class="close-button" id="close-auth-modal" style="position: absolute; top: 8px; right: 16px; font-size: 28px; cursor: pointer;">&times;</span>
                                <h5 style="font-weight: bold;">Vous devez être connecté</h5>
                                <p style="font-size: 14px; color: #6b7280; margin: 10px 0 20px;">Connectez-vous pour laisser votre avis</p>
                                <div style="display: flex; text-align: center; align-items: center; justify-content: center; margin: 20px 0; gap: 10px;">
                                    <a href="{{ route('login', ['redirect' => urlencode(request()->fullUrl())]) }}" class="btn btn-primary w-100" id="loginLink">Connexion</a>
                                    <a href="{{ route('register', ['redirect' => urlencode(request()->fullUrl())]) }}" class="btn btn-outline-primary w-100" id="registerLink">Inscription</a>
                                </div>
                                <button type="button" class="btn btn-link" id="cancelCommentBtn" style="margin-top: 10px; color: #6b7280; text-decoration: none;">Annuler</button>
                            </div>
                        </div>

                        <style>
                            .modal {
                                position: fixed;
                                top: 0; left: 0; right: 0; bottom: 0;
                                width: 100%; height: 100%;
                                max-width: 100%;
                                overflow-x: hidden;
                                background: rgba(0,0,0,0.3);
                                display: none;
                                justify-content: center;
                                align-items: center;
                                z-index: 9999;
                            }
                        </style>


    <!-- inofrmations sur le vendeur de l'article  -->
    <!-- Section Informations du Vendeur -->
    <div class="seller-info mt-4">
        <div class="card shadow-sm p-3">
            <div class="d-flex align-items-center">
                <!-- Photo de profil du vendeur (optionnelle) -->
                <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                    alt="Photo vendeur" 
                    class="rounded-circle me-3" 
                    width="40" 
                    height="40"
                    loading="lazy"
                    onerror="this.src='{{ asset('images/user_default.png') }}';">

                <div>
                    <h6 class="mb-1"><strong>{{ $article->user->name }}</strong></h6>

                    <!-- Date d’inscription -->
                    <p class="mb-0 text-muted" style="font-size: 12px;">
                        </p>

                    <!-- Nombre d’articles publiés -->
                    <p class="mb-0 text-muted" style="font-size: 12px;">
                        📝 Articles publiés : <strong>{{ $article->user->articles()->count() }}</strong>
                    </p>

                    <!-- Likes totaux -->
                    <p class="mb-0 text-muted" style="font-size: 12px;">
                        ❤️ Likes totaux : 
                        <strong>{{ $article->user->articles->sum(fn($a) => $a->usersWhoLiked->count()) }}</strong>
                    </p>

                    <!-- Email -->
                    <!-- <p class="mb-0 text-muted">📧 {{ $article->user->email }}</p> -->
                    <a href="{{ route('boutique.show', $article->user->id) }}" class="btn-boutique">
                        Voir la boutique
                    </a>

                </div>
            </div>
        </div>
    </div>

    <style>
        /* Surcharge des styles du fichier detail_article.css */
        .detail-container {
            /* Header (45px) + Navigation (130px) + marge de sécurité (20px) = 195px */
            margin-top: 195px !important;
            padding-top: 0 !important; /* Supprime le padding-top du CSS externe */
            padding: 20px; /* Garde seulement le padding horizontal */
        }
        @media (max-width: 768px) {
            .detail-container {
                /* Header (45px) + Navigation (130px) + marge mobile (10px) = 185px */
                margin-top: 185px !important;
                padding-top: 0 !important; /* Supprime le padding-top: 200px du CSS externe */
                padding: 20px; /* Garde seulement le padding horizontal */
            }
        }
        @media (max-width: 480px) {
            .detail-container {
                /* Ajustement pour très petits écrans */
                margin-top: 180px !important;
                padding-top: 0 !important;
                padding: 15px;
            }
        }

        .seller-info{
            position: relative;
        }

        .btn-boutique {
            position: absolute;
            right: 10px;
            bottom: 10px;
            display: inline-block;
            background-color: #ff7b00; /* orange */
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-boutique:hover {
            background-color: #e06f00; /* plus foncé au survol */
        }

        .seller-info .card {
            border-radius: 14px;
            background: #fff;
            border: 1px solid #eee;
        }
        .seller-info h6 {
            font-weight: bold;
            color: #333;
        }
        .seller-info img {
            border: 2px solid #ddd;
            padding: 2px;
        }
        .seller-info .btn {
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .seller-info .btn:hover {
            transform: translateY(-2px);
        }
        /* Desktop layout enhancements */
        @media (min-width: 992px) {
            .detail-grid { margin-top: 0; }
            .detail-sidebar {
                position: sticky;
                /* Header (45px) + Navigation (130px) + petite marge (10px) = 185px */
                top: 185px;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            }
            .sidebar-header { margin-bottom: 12px; }
            .product-title {
                font-size: 22px;
                line-height: 1.25;
                margin: 0 0 8px 0;
                font-weight: 700;
            }
            .price-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
            .price { font-size: 22px; font-weight: 800; color: #e11d48; }
            .actions { display: flex; align-items: center; gap: 8px; }
            .btn-share { border: 1px solid #eee; }

            .thumbs { 
                display: flex !important;
                flex-wrap: wrap; 
                justify-content: center;
                margin-top: 12px;
                gap: 10px;
            }
            .thumb {
                border: 1px solid #eee; 
                border-radius: 8px; 
                padding: 2px; 
                background: #fff; 
                cursor: pointer;
                transition: transform .15s ease, box-shadow .15s ease;
                display: block;
                outline: none !important; /* Empêcher le focus visible */
            }
            .thumb:focus,
            .thumb:active {
                outline: none !important;
                border-color: #007bff;
            }
            .thumb img { 
                width: 64px; 
                height: 64px; 
                object-fit: cover; 
                border-radius: 6px; 
                display: block; 
            }
            .thumb:hover { 
                transform: translateY(-2px); 
                box-shadow: 0 4px 10px rgba(0,0,0,0.08); 
            }

            .cta-seller { margin: 12px 0 16px 0; }
            .product-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
            .meta-item { background: #fafafa; border: 1px solid #f0f0f0; border-radius: 8px; padding: 8px 10px; }
            .meta-label { display: block; font-size: 11px; color: #6b7280; }
            .meta-value { font-size: 13px; font-weight: 600; color: #111827; }

            .image-detail img { 
                width: 100%; 
                height: 500px; 
                object-fit: cover; 
                border-radius: 12px;
            }
            
            .thumb.active {
                border: 3px solid #007bff !important;
                transform: scale(1.1);
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            }

            /* Section détails produit professionnelle */
            .product-details-section {
                margin-top: 24px;
                padding-top: 24px;
                border-top: 1px solid #eee;
                display: block;
            }
            /* Cacher la section mobile sur desktop */
            .product-details-section-mobile {
                display: none;
            }
            .product-specs {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
                margin: 20px 0;
            }
            .spec-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                background: #f8f9fa;
                border-radius: 10px;
                border: 1px solid #e9ecef;
                transition: all 0.2s ease;
            }
            .spec-item:hover {
                background: #f0f0f0;
                transform: translateY(-2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
            .spec-icon {
                font-size: 24px;
                flex-shrink: 0;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .spec-content {
                display: flex;
                flex-direction: column;
                gap: 4px;
                flex: 1;
            }
            .spec-label {
                font-size: 12px;
                color: #6b7280;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .spec-value {
                font-size: 15px;
                font-weight: 600;
                color: #111827;
            }
            .spec-neuf {
                color: #059669;
            }
            .spec-occasion {
                color: #dc2626;
            }
            .spec-available {
                color: #059669;
            }
            .spec-unavailable {
                color: #6b7280;
            }
            .product-description-section {
                margin-top: 24px;
                padding-top: 24px;
                border-top: 1px solid #eee;
            }
            .description-title {
                font-size: 18px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 12px;
            }
            .description-content {
                font-size: 15px;
                line-height: 1.7;
                color: #374151;
                word-wrap: break-word;
            }
            .description-text {
                margin: 0;
                padding: 0;
            }
            .description-text a {
                color: #2563eb;
                text-decoration: underline;
                word-break: break-all;
            }
            .description-text a:hover {
                color: #1d4ed8;
            }
            .read-more-btn {
                margin-top: 12px;
                padding: 8px 16px;
                background: transparent;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                color: #374151;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }
            .read-more-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
            }
            .description-text.collapsed {
                max-height: 150px;
                overflow: hidden;
                position: relative;
            }
            .description-text.collapsed::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 60px;
                background: linear-gradient(to bottom, transparent, #fff);
                pointer-events: none;
            }
        }

        /* Styles pour les vignettes mobiles */
        @media (max-width: 991px) {
            /* Image principale mobile */
            .image-detail img {
                width: 100%;
                height: 350px;
                object-fit: cover;
                border-radius: 12px;
            }

            .thumbs-mobile {
                margin-bottom: 12px;
                display: flex;
                justify-content: center;
                width: 100%;
                overflow: hidden;
            }
            .thumbs-scroll {
                display: flex;
                gap: 10px;
                overflow-x: auto;
                overflow-y: hidden;
                padding: 8px 0;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                /* Montrer 3 images complètes + un peu de la 4ème : 3.3 × 65px + 2.3 × 10px (gap) ≈ 243px */
                max-width: calc(3.3 * 55px + 2.3 * 10px);
                margin: 0 auto;
                justify-content: center;
                /* Scroll snap pour aligner automatiquement les images */
                scroll-snap-type: x mandatory;
                scroll-padding: 0 calc((100% - 55px) / 2);
            }
            .thumbs-scroll::-webkit-scrollbar {
                display: none; /* Masquer la scrollbar sur Chrome, Safari, Opera */
            }
            .thumbs-scroll {
                scrollbar-width: none; /* Masquer la scrollbar sur Firefox */
                -ms-overflow-style: none; /* Masquer la scrollbar sur IE et Edge */
            }
            .thumb-mobile {
                flex: 0 0 auto;
                width: 50px;
                height: 50px;
                border: 2px solid #ddd;
                border-radius: 10px;
                padding: 3px;
                background: #fff;
                cursor: pointer;
                transition: all 0.2s ease;
                outline: none !important; /* Empêcher le focus visible */
                -webkit-tap-highlight-color: transparent; /* Supprimer le highlight au tap sur mobile */
                /* Scroll snap pour chaque image */
                scroll-snap-align: center;
                scroll-snap-stop: always;
            }
            .thumb-mobile:focus,
            .thumb-mobile:active {
                outline: none !important;
                border-color: #007bff;
            }
            .thumb-mobile img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 8px;
                display: block;
            }
            .thumb-mobile.active {
                border: 3px solid #007bff !important;
                transform: scale(1.05);
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            }
            .thumb-mobile:active {
                transform: scale(0.98);
            }

            /* Sidebar mobile organisée */
            .detail-sidebar-mobile {
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                margin-bottom: 20px;
            }
            .mobile-sidebar-header {
                margin-bottom: 16px;
            }
            .product-title-mobile {
                font-size: 20px;
                line-height: 1.3;
                margin: 0 0 12px 0;
                font-weight: 700;
                color: #1a1a1a;
            }
            .price-actions-mobile {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding-bottom: 16px;
                border-bottom: 1px solid #eee;
            }
            .price-mobile {
                font-size: 22px;
                font-weight: 800;
                color: #e11d48;
            }
            .actions-mobile {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            /* CTA vendeur mobile */
            .cta-seller-mobile {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin: 16px 0;
            }
            .cta-seller-mobile .btn {
                font-weight: 600;
                padding: 12px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                text-decoration: none;
            }

            /* Métadonnées produit mobile */
            .product-meta-mobile {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin: 16px 0;
                padding-top: 16px;
                border-top: 1px solid #eee;
            }
            .meta-item-mobile {
                background: #fafafa;
                border: 1px solid #f0f0f0;
                border-radius: 8px;
                padding: 10px 12px;
            }
            .meta-label-mobile {
                display: block;
                font-size: 12px;
                color: #6b7280;
                margin-bottom: 4px;
            }
            .meta-value-mobile {
                font-size: 14px;
                font-weight: 600;
                color: #111827;
            }

            /* Section détails produit mobile */
            .product-details-section-mobile {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }
            .details-post-titre-mobile-title {
                font-size: 18px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 16px;
                text-align: center;
            }
            /* Cacher la section desktop sur mobile */
            .product-details-section {
                display: none;
            }
            .product-specs {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
                margin: 16px 0;
            }
            .spec-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px;
                background: #f8f9fa;
                border-radius: 10px;
                border: 1px solid #e9ecef;
            }
            .spec-icon {
                font-size: 20px;
                width: 36px;
                height: 36px;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .spec-label {
                font-size: 11px;
                color: #6b7280;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .spec-value {
                font-size: 14px;
                font-weight: 600;
                color: #111827;
            }
            .spec-neuf {
                color: #059669;
            }
            .spec-occasion {
                color: #dc2626;
            }
            .spec-available {
                color: #059669;
            }
            .spec-unavailable {
                color: #6b7280;
            }
            .product-description-section {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }
            .description-title {
                font-size: 16px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 10px;
            }
            .description-content {
                font-size: 14px;
                line-height: 1.6;
                color: #374151;
                word-wrap: break-word;
            }
            .description-text {
                margin: 0;
                padding: 0;
            }
            .description-text a {
                color: #2563eb;
                text-decoration: underline;
                word-break: break-all;
            }
            .description-text a:hover {
                color: #1d4ed8;
            }
            .read-more-btn {
                margin-top: 10px;
                padding: 6px 14px;
                background: transparent;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                color: #374151;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }
            .read-more-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
            }
            .description-text.collapsed {
                max-height: 120px;
                overflow: hidden;
                position: relative;
            }
            .description-text.collapsed::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 50px;
                background: linear-gradient(to bottom, transparent, #fff);
                pointer-events: none;
            }

        }

        /* Styles pour le modal lightbox */
        .lightbox-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            min-height: 100vh;
            background-color: rgba(0, 0, 0, 0.95);
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        .lightbox-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* S'assurer que le body et html prennent toute la hauteur quand le lightbox est ouvert */
        body.lightbox-open {
            overflow: hidden !important;
            height: 100vh !important;
        }
        .lightbox-content {
            position: relative;
            max-width: 95%;
            max-height: 95vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lightbox-image {
            max-width: 100%;
            max-height: 95vh;
            object-fit: contain;
            border-radius: 8px;
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .lightbox-close:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: scale(1.1);
        }
        .lightbox-prev,
        .lightbox-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            padding: 15px 20px;
            border-radius: 50%;
            user-select: none;
            transition: all 0.3s ease;
            z-index: 10000;
        }
        .lightbox-prev.hidden,
        .lightbox-next.hidden {
            display: none;
        }
        .lightbox-prev:hover,
        .lightbox-next:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        .lightbox-prev {
            left: 20px;
        }
        .lightbox-next {
            right: 20px;
        }
        @media (max-width: 768px) {
            .lightbox-close {
                top: 10px;
                right: 15px;
                font-size: 30px;
                width: 40px;
                height: 40px;
            }
            .lightbox-prev,
            .lightbox-next {
                font-size: 24px;
                padding: 12px 16px;
            }
            .lightbox-prev {
                left: 10px;
            }
            .lightbox-next {
                right: 10px;
            }
        }
    </style>

    <!-- Modal Lightbox pour afficher les images en plein écran -->
    <div id="lightboxModal" class="lightbox-modal">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        @if(count($images) > 1)
        <span class="lightbox-prev" onclick="changeLightboxImage(-1)">&#10094;</span>
        <span class="lightbox-next" onclick="changeLightboxImage(1)">&#10095;</span>
        @endif
        <div class="lightbox-content">
            <img id="lightboxImage" class="lightbox-image" src="" alt="{{ $article->titre }} - Vue en plein écran">
        </div>
    </div>

    <!-- informations sur le vendeur  --> 

</div>
                    <!-- script pour l'ouverture du modal de connexion -->
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const modal = document.getElementById("modal-auth");
                            const closeBtn = document.getElementById("close-auth-modal");

                            // Fermer le modal
                            if (closeBtn) {
                            closeBtn.addEventListener("click", function () {
                                modal.style.display = "none";
                            });
                            }
                            window.addEventListener("click", function (event) {
                                if (event.target === modal) {
                                    modal.style.display = "none";
                                }
                            });
                        });
                    </script>

                    <!-- Script pour le système de commentaires professionnel -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const articleId = {{ $article->id }};
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                            
                            // ===== AJOUT DE COMMENTAIRE =====
                            const commentForm = document.getElementById('commentForm');
                            const commentTextarea = document.getElementById('content');
                            const charCount = document.getElementById('charCount');
                            const submitBtn = document.getElementById('commentSubmitBtn');
                            
                            // Compteur de caractères
                            if (commentTextarea && charCount) {
                                commentTextarea.addEventListener('input', function() {
                                    const length = this.value.length;
                                    charCount.textContent = length;
                                    
                                    if (length > 900) {
                                        charCount.style.color = '#dc3545';
                                    } else if (length > 800) {
                                        charCount.style.color = '#ffc107';
                                    } else {
                                        charCount.style.color = '#495057';
                                    }
                                });
                            }
                            
                            // Fonction pour envoyer le commentaire
                            function submitComment(content) {
                                if (!content || content.trim().length < 3) {
                                    if (typeof showToast !== 'undefined') {
                                        showToast('L\'avis doit contenir au moins 3 caractères.', 3000);
                                    } else {
                                        alert('L\'avis doit contenir au moins 3 caractères.');
                                    }
                                        return;
                                    }
                                    
                                    if (content.length > 1000) {
                                    if (typeof showToast !== 'undefined') {
                                        showToast('L\'avis ne peut pas dépasser 1000 caractères.', 3000);
                                    } else {
                                        alert('L\'avis ne peut pas dépasser 1000 caractères.');
                                    }
                                        return;
                                    }
                                    
                                    // Désactiver le bouton pendant l'envoi avec spinner
                                if (submitBtn) {
                                    submitBtn.disabled = true;
                                    submitBtn.innerHTML = '<span class="comment-spinner"></span>';
                                }
                                    
                                    fetch(`/articles/${articleId}/comments`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                            'Accept': 'application/json'
                                        },
                                    body: JSON.stringify({ content: content.trim() })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Créer le nouveau commentaire
                                            const newComment = createCommentHTML(data.comment, true);
                                            
                                            // Retirer le message "aucun commentaire" s'il existe
                                            const noComments = document.querySelector('.no-comments');
                                            if (noComments) {
                                                noComments.remove();
                                            }
                                            
                                            // Ajouter le commentaire en haut
                                            const container = document.getElementById('commentsContainer');
                                            container.insertAdjacentHTML('afterbegin', newComment);
                                            
                                            // Réinitialiser le formulaire
                                        if (commentTextarea) {
                                            commentTextarea.value = '';
                                        }
                                        if (charCount) {
                                            charCount.textContent = '0';
                                        }
                                        
                                        // Supprimer le commentaire en attente du sessionStorage
                                        sessionStorage.removeItem('pendingComment_' + articleId);
                                            
                                            // Mettre à jour le compteur
                                            updateCommentsCount();
                                            
                                            // Réinitialiser les événements pour les nouveaux boutons
                                            initCommentActions();
                                            initSeeMoreButtons();
                                            
                                            // Scroll vers le nouveau commentaire
                                            const firstComment = container.querySelector('.comment-item');
                                            if (firstComment) {
                                                firstComment.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                            }
                                        
                                        if (typeof showToast !== 'undefined') {
                                            showToast('Avis publié avec succès !', 3000);
                                        }
                                    } else {
                                        if (typeof showToast !== 'undefined') {
                                            showToast(data.message || 'Erreur lors de l\'ajout de l\'avis.', 3000);
                                        } else {
                                            alert(data.message || 'Erreur lors de l\'ajout de l\'avis.');
                                        }
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erreur:', error);
                                    if (typeof showToast !== 'undefined') {
                                        showToast('Erreur lors de l\'ajout de l\'avis. Veuillez réessayer.', 3000);
                                    } else {
                                        alert('Erreur lors de l\'ajout de l\'avis. Veuillez réessayer.');
                                    }
                                    })
                                    .finally(() => {
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
                                    }
                                });
                            }
                            
                            if (commentForm) {
                                commentForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    const content = commentTextarea ? commentTextarea.value.trim() : '';
                                    const isAuthenticated = @json(auth()->check());
                                    
                                    // Vérifier si l'utilisateur est connecté
                                    if (!isAuthenticated) {
                                        // Sauvegarder le commentaire dans sessionStorage
                                        sessionStorage.setItem('pendingComment_' + articleId, content);
                                        
                                        // Afficher la modal de connexion
                                        const modal = document.getElementById('modal-auth');
                                        if (modal) {
                                            modal.style.display = 'flex';
                                        }
                                        return;
                                    }
                                    
                                    // Si connecté, envoyer directement
                                    submitComment(content);
                                });
                            }
                            
                            // Vérifier s'il y a un commentaire en attente après connexion
                            const pendingComment = sessionStorage.getItem('pendingComment_' + articleId);
                            const isAuthenticated = @json(auth()->check());
                            
                            if (pendingComment && isAuthenticated) {
                                // Attendre un peu pour s'assurer que la page est complètement chargée
                                setTimeout(() => {
                                    submitComment(pendingComment);
                                }, 500);
                            }
                            
                            // Gérer le bouton "Annuler" dans la modal
                            const cancelBtn = document.getElementById('cancelCommentBtn');
                            if (cancelBtn) {
                                cancelBtn.addEventListener('click', function() {
                                    // Supprimer le commentaire en attente
                                    sessionStorage.removeItem('pendingComment_' + articleId);
                                    
                                    // Vider le textarea
                                    if (commentTextarea) {
                                        commentTextarea.value = '';
                                    }
                                    if (charCount) {
                                        charCount.textContent = '0';
                                    }
                                    
                                    // Fermer la modal
                                    const modal = document.getElementById('modal-auth');
                                    if (modal) {
                                        modal.style.display = 'none';
                                    }
                                });
                            }
                            
                            // ===== GESTION "VOIR PLUS / VOIR MOINS" POUR LES COMMENTAIRES =====
                            function initSeeMoreButtons() {
                                // Gérer les boutons "Voir plus"
                                document.querySelectorAll('.comment-see-more').forEach(btn => {
                                    btn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const commentId = this.dataset.commentId;
                                        const commentText = document.getElementById(`comment-text-${commentId}`);
                                        
                                        if (commentText) {
                                            // Récupérer le texte complet depuis l'attribut data-full-text
                                            const fullText = commentText.getAttribute('data-full-text');
                                            
                                            if (fullText) {
                                                // Sauvegarder le texte tronqué pour pouvoir le restaurer
                                                const shortText = fullText.substring(0, 150);
                                                commentText.setAttribute('data-short-text', shortText);
                                                
                                                // Afficher le texte complet
                                                commentText.textContent = '';
                                                commentText.textContent = fullText;
                                                commentText.classList.add('expanded');
                                                
                                                // Masquer le bouton "Voir plus"
                                                this.style.display = 'none';
                                                
                                                // Créer et afficher le bouton "Voir moins"
                                                const seeLessBtn = document.createElement('button');
                                                seeLessBtn.type = 'button';
                                                seeLessBtn.className = 'comment-see-less';
                                                seeLessBtn.setAttribute('data-comment-id', commentId);
                                                seeLessBtn.textContent = 'Voir moins';
                                                commentText.appendChild(seeLessBtn);
                                                
                                                // Ajouter l'événement au bouton "Voir moins"
                                                seeLessBtn.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    handleSeeLess(commentId);
                                                });
                                            } else {
                                                // Fallback : afficher le span caché
                                                const shortText = commentText.querySelector('.comment-short-text');
                                                const ellipsis = commentText.querySelector('.comment-ellipsis');
                                                const fullTextSpan = commentText.querySelector('.comment-full-text');
                                                
                                                if (shortText && fullTextSpan) {
                                                    // Sauvegarder le texte tronqué
                                                    const shortContent = shortText.textContent;
                                                    commentText.setAttribute('data-short-text', shortContent);
                                                    
                                                    // Afficher le texte complet
                                                    const fullContent = fullTextSpan.textContent;
                                                    commentText.textContent = '';
                                                    commentText.textContent = shortContent + fullContent;
                                                    
                                                    // Masquer les éléments
                                                    shortText.style.display = 'none';
                                                    ellipsis.style.display = 'none';
                                                    fullTextSpan.style.display = 'none';
                                                    
                                                    // Créer et afficher le bouton "Voir moins"
                                                    const seeLessBtn = document.createElement('button');
                                                    seeLessBtn.type = 'button';
                                                    seeLessBtn.className = 'comment-see-less';
                                                    seeLessBtn.setAttribute('data-comment-id', commentId);
                                                    seeLessBtn.textContent = 'Voir moins';
                                                    commentText.appendChild(seeLessBtn);
                                                    
                                                    // Ajouter l'événement au bouton "Voir moins"
                                                    seeLessBtn.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        handleSeeLess(commentId);
                                                    });
                                                } else if (ellipsis && fullTextSpan) {
                                                    ellipsis.style.display = 'none';
                                                    fullTextSpan.style.display = 'inline';
                                                }
                                                
                                                // Masquer le bouton "Voir plus"
                                                this.style.display = 'none';
                                                commentText.classList.add('expanded');
                                            }
                                        }
                                    });
                                });
                            }
                            
                            // Fonction pour gérer "Voir moins"
                            function handleSeeLess(commentId) {
                                const commentText = document.getElementById(`comment-text-${commentId}`);
                                
                                if (commentText) {
                                    const fullText = commentText.getAttribute('data-full-text');
                                    const shortText = fullText ? fullText.substring(0, 150) : '';
                                    
                                    if (shortText && fullText) {
                                        // Restaurer le texte tronqué
                                        commentText.textContent = '';
                                        
                                        // Recréer la structure HTML avec le texte tronqué
                                        const shortTextSpan = document.createElement('span');
                                        shortTextSpan.className = 'comment-short-text';
                                        shortTextSpan.textContent = shortText;
                                        
                                        const ellipsisSpan = document.createElement('span');
                                        ellipsisSpan.className = 'comment-ellipsis';
                                        ellipsisSpan.textContent = '...';
                                        
                                        const fullTextSpan = document.createElement('span');
                                        fullTextSpan.className = 'comment-full-text';
                                        fullTextSpan.style.display = 'none';
                                        fullTextSpan.textContent = fullText.substring(150);
                                        
                                        const seeMoreBtn = document.createElement('button');
                                        seeMoreBtn.type = 'button';
                                        seeMoreBtn.className = 'comment-see-more';
                                        seeMoreBtn.setAttribute('data-comment-id', commentId);
                                        seeMoreBtn.textContent = 'Voir plus';
                                        
                                        commentText.appendChild(shortTextSpan);
                                        commentText.appendChild(ellipsisSpan);
                                        commentText.appendChild(fullTextSpan);
                                        commentText.appendChild(seeMoreBtn);
                                        
                                        // Réinitialiser les événements
                                        commentText.classList.remove('expanded');
                                        initSeeMoreButtons();
                                    }
                                }
                            }
                            
                            // Initialiser les boutons "Voir plus" au chargement
                            initSeeMoreButtons();
                            
                            // ===== MODIFICATION DE COMMENTAIRE =====
                            function initCommentActions() {
                                // Boutons d'édition
                                document.querySelectorAll('.edit-comment-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const commentId = this.dataset.commentId;
                                        const editForm = document.getElementById(`edit-form-${commentId}`);
                                        const commentText = document.getElementById(`comment-text-${commentId}`);
                                        
                                        if (editForm.style.display === 'none') {
                                            editForm.style.display = 'block';
                                            commentText.style.display = 'none';
                                        } else {
                                            editForm.style.display = 'none';
                                            commentText.style.display = 'block';
                                        }
                                    });
                                });
                                
                                // Annuler l'édition
                                document.querySelectorAll('.btn-cancel-edit').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const commentId = this.dataset.commentId;
                                        const editForm = document.getElementById(`edit-form-${commentId}`);
                                        const commentText = document.getElementById(`comment-text-${commentId}`);
                                        const textarea = document.getElementById(`edit-textarea-${commentId}`);
                                        const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
                                        
                                        // Restaurer le texte original
                                        textarea.value = commentItem.querySelector('.comment-text').textContent;
                                        editForm.style.display = 'none';
                                        commentText.style.display = 'block';
                                    });
                                });
                                
                                // Enregistrer l'édition
                                document.querySelectorAll('.btn-save-edit').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const commentId = this.dataset.commentId;
                                        const textarea = document.getElementById(`edit-textarea-${commentId}`);
                                        const content = textarea.value.trim();
                                        
                                        if (content.length < 3) {
                                            alert('L\'avis doit contenir au moins 3 caractères.');
                                            return;
                                        }
                                        
                                        if (content.length > 1000) {
                                            alert('L\'avis ne peut pas dépasser 1000 caractères.');
                                            return;
                                        }
                                        
                                        this.disabled = true;
                                        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Enregistrement...';
                                        
                                        fetch(`/comments/${commentId}`, {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ content: content })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                const commentText = document.getElementById(`comment-text-${commentId}`);
                                                const editForm = document.getElementById(`edit-form-${commentId}`);
                                                
                                                commentText.textContent = data.comment.content;
                                                editForm.style.display = 'none';
                                                commentText.style.display = 'block';
                                                
                                                // Ajouter l'indicateur "modifié"
                                                const commentDate = commentText.closest('.comment-item').querySelector('.comment-date');
                                                if (!commentDate.querySelector('.comment-edited')) {
                                                    commentDate.innerHTML += '<span class="comment-edited">(modifié)</span>';
                                                }
                                            } else {
                                                showToast(data.message || 'Erreur lors de la modification.', 3000);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Erreur:', error);
                                            showToast('Erreur lors de la modification. Veuillez réessayer.', 3000);
                                        })
                                        .finally(() => {
                                            this.disabled = false;
                                            this.innerHTML = '<i class="bi bi-check"></i> Enregistrer';
                                        });
                                    });
                                });
                                
                                // Suppression de commentaire
                                document.querySelectorAll('.delete-comment-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) {
                                            return;
                                        }
                                        
                                        const commentId = this.dataset.commentId;
                                        const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
                                        
                                        fetch(`/comments/${commentId}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                commentItem.style.transition = 'opacity 0.3s ease';
                                                commentItem.style.opacity = '0';
                                                setTimeout(() => {
                                                    commentItem.remove();
                                                    updateCommentsCount();
                                                    
                                                    // Afficher message si plus de commentaires
                                                    const container = document.getElementById('commentsContainer');
                                                    if (container.querySelectorAll('.comment-item').length === 0) {
                                                        container.innerHTML = `
                                                            <div class="no-comments">
                                                                <i class="bi bi-chat"></i>
                                                                <p>Aucun avis pour le moment. Soyez le premier à laisser un avis !</p>
                                                            </div>
                                                        `;
                                                    }
                                                }, 300);
                                            } else {
                                                showToast(data.message || 'Erreur lors de la suppression.', 3000);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Erreur:', error);
                                            showToast('Erreur lors de la suppression. Veuillez réessayer.', 3000);
                                        });
                                    });
                                });
                                
                                // Signalement de commentaire
                                document.querySelectorAll('.report-comment-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const commentId = this.dataset.commentId;
                                        
                                        if (!confirm('Voulez-vous signaler cet avis ?')) {
                                            return;
                                        }
                                        
                                        this.disabled = true;
                                        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Signalement...';
                                        
                                        fetch(`/comments/${commentId}/report`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ reason: 'Contenu inapproprié' })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                showToast(data.message || 'Avis signalé avec succès. Merci pour votre vigilance.', 3000);
                                                this.style.display = 'none';
                                            } else {
                                                showToast(data.message || 'Erreur lors du signalement.', 3000);
                                                this.disabled = false;
                                                this.innerHTML = '<i class="bi bi-flag"></i> Signaler';
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Erreur:', error);
                                            showToast('Erreur lors du signalement. Veuillez réessayer.', 3000);
                                            this.disabled = false;
                                            this.innerHTML = '<i class="bi bi-flag"></i> Signaler';
                                        });
                                    });
                                });
                            }
                            
                            // Fonction pour créer le HTML d'un commentaire
                            function createCommentHTML(comment, isOwner = false) {
                                const isCertified = comment.user.is_certified ? 
                                    '<span class="comment-badge-certified" title="Certifié"><i class="bi bi-patch-check-fill"></i></span>' : '';
                                
                                const currentUserId = @json(auth()->id());
                                const isAdmin = @json(auth()->check() && auth()->user()->isAdmin() ?? false);
                                const isCommentOwner = currentUserId === comment.user.id;
                                const canEditDelete = isCommentOwner || isAdmin;
                                
                                // Générer les actions
                                let actionsHtml = '';
                                if (currentUserId) {
                                    actionsHtml = '<div class="comment-actions">';
                                    if (canEditDelete) {
                                        actionsHtml += `
                                            <button class="comment-action-btn edit-comment-btn" data-comment-id="${comment.id}" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="comment-action-btn delete-comment-btn" data-comment-id="${comment.id}" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        `;
                                    } else {
                                        actionsHtml += `
                                            <button class="comment-action-btn report-comment-btn" data-comment-id="${comment.id}" title="Signaler">
                                                <i class="bi bi-flag"></i>
                                            </button>
                                        `;
                                    }
                                    actionsHtml += '</div>';
                                }
                                
                                return `
                                    <div class="comment-item" data-comment-id="${comment.id}">
                                        <img src="${comment.user.avatar}" 
                                             alt="${comment.user.name}" 
                                             class="comment-avatar"
                                             onerror="this.src='{{ asset('images/user_default.png') }}';">
                                        
                                        <div class="comment-body">
                                            <div class="comment-meta">
                                                <span class="comment-author">${escapeHtml(comment.user.name)}</span>
                                                ${isCertified}
                                                <span class="comment-time">${comment.created_at}</span>
                                            </div>
                                            <p class="comment-text" id="comment-text-${comment.id}" data-full-text="${escapeHtml(comment.content)}">
                                                ${comment.content.length > 150 
                                                    ? '<span class="comment-short-text">' + escapeHtml(comment.content.substring(0, 150)) + '</span><span class="comment-ellipsis">...</span><span class="comment-full-text" style="display: none;">' + escapeHtml(comment.content.substring(150)) + '</span><button type="button" class="comment-see-more" data-comment-id="' + comment.id + '">Voir plus</button>'
                                                    : escapeHtml(comment.content)
                                                }
                                            </p>
                                            ${actionsHtml}
                                            <div class="comment-edit-form" id="edit-form-${comment.id}" style="display: none;">
                                                <textarea class="comment-edit-textarea" id="edit-textarea-${comment.id}" maxlength="1000">${escapeHtml(comment.content)}</textarea>
                                                <div class="comment-edit-actions">
                                                    <button class="btn-save-edit" data-comment-id="${comment.id}">
                                                        <i class="bi bi-check"></i> Enregistrer
                                                    </button>
                                                    <button class="btn-cancel-edit" data-comment-id="${comment.id}">
                                                        <i class="bi bi-x"></i> Annuler
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            // Fonction pour échapper le HTML
                            function escapeHtml(text) {
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            }
                            
                            // Fonction pour mettre à jour le compteur de commentaires
                            function updateCommentsCount() {
                                const count = document.querySelectorAll('.comment-item').length;
                                const countElement = document.querySelector('.comments-count');
                                if (countElement) {
                                    countElement.textContent = count;
                                }
                            }
                            
                            // Charger plus de commentaires
                            const loadMoreBtn = document.getElementById('loadMoreComments');
                            let currentOffset = 10;
                            
                            if (loadMoreBtn) {
                                loadMoreBtn.addEventListener('click', function() {
                                    this.disabled = true;
                                    this.innerHTML = '<i class="bi bi-hourglass-split"></i> Chargement...';
                                    
                                    fetch(`/articles/{{ $article->id }}/comments/load-more?offset=${currentOffset}&limit=10`, {
                                        method: 'GET',
                                        headers: {
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.comments.length > 0) {
                                            const container = document.getElementById('commentsContainer');
                                            const loadMoreDiv = container.querySelector('.load-more-comments');
                                            
                                            data.comments.forEach(comment => {
                                                const commentHTML = createCommentHTML(comment, comment.is_owner);
                                                if (loadMoreDiv) {
                                                    loadMoreDiv.insertAdjacentHTML('beforebegin', commentHTML);
                                                } else {
                                                    container.insertAdjacentHTML('beforeend', commentHTML);
                                                }
                                            });
                                            
                                            currentOffset += data.comments.length;
                                            
                                            // Réinitialiser les événements pour les nouveaux commentaires
                                            initCommentActions();
                                            initSeeMoreButtons();
                                            
                                            // Mettre à jour le compteur
                                            updateCommentsCount();
                                            
                                            // Masquer le bouton si plus de commentaires
                                            if (!data.has_more) {
                                                if (loadMoreDiv) {
                                                    loadMoreDiv.remove();
                                                } else {
                                                    this.style.display = 'none';
                                                }
                                            } else {
                                                this.disabled = false;
                                                this.innerHTML = `Voir plus d'avis ({{ $article->comments->count() }} - ${currentOffset} affichés)`;
                                            }
                                            
                                            showToast(`${data.comments.length} avis chargé(s)`, 2000);
                                        } else {
                                            if (loadMoreDiv) {
                                                loadMoreDiv.remove();
                                            } else {
                                                this.style.display = 'none';
                                            }
                                            showToast('Tous les avis ont été chargés.', 2000);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erreur:', error);
                                        showToast('Erreur lors du chargement des commentaires. Veuillez réessayer.', 3000);
                                        this.disabled = false;
                                        this.innerHTML = 'Voir tous les commentaires';
                                    });
                                });
                            }
                            
                            // Initialiser les actions au chargement
                            initCommentActions();
                        });
                    </script>

                    <!-- JavaScript pour l'ouverture du modal des commentaires-->
                    <script>
                        function openModal() {
                            document.getElementById('commentsModal').style.display = 'block';
                        }
                        function closeModal() {
                            document.getElementById('commentsModal').style.display = 'none';
                        }
                    </script>

                    <!-- Script pour mettre à jour les vignettes actives -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const carousel = document.querySelector('#carouselArticle');
                            const thumbs = document.querySelectorAll('.thumb');
                            const thumbsMobile = document.querySelectorAll('.thumb-mobile');
                            
                            // Empêcher le scroll automatique lors du clic sur les vignettes desktop
                            thumbs.forEach(thumb => {
                                // Désactiver le focus automatique qui cause le scroll
                                thumb.style.outline = 'none';
                                thumb.setAttribute('tabindex', '-1'); // Empêcher le focus au clavier
                                
                                // Gérer le clic
                                function handleThumbClick(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    // Empêcher le focus
                                    thumb.blur();
                                    // Déclencher le changement de slide manuellement
                                    const slideTo = thumb.getAttribute('data-bs-slide-to');
                                    if (slideTo !== null && carousel) {
                                        const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(carousel);
                                        bsCarousel.to(parseInt(slideTo));
                                    }
                                    return false;
                                }
                                
                                thumb.addEventListener('click', handleThumbClick);
                                thumb.addEventListener('mousedown', handleThumbClick);
                            });

                            // Empêcher le scroll automatique lors du clic sur les vignettes mobile
                            thumbsMobile.forEach(thumb => {
                                // Désactiver le focus automatique qui cause le scroll
                                thumb.style.outline = 'none';
                                thumb.setAttribute('tabindex', '-1'); // Empêcher le focus au clavier
                                
                                // Gérer le clic/tap
                                function handleThumbClick(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    // Empêcher le focus
                                    thumb.blur();
                                    
                                    // Centrer la vignette cliquée
                                    const thumbsScroll = document.getElementById('thumbsScroll');
                                    if (thumbsScroll) {
                                        const containerWidth = thumbsScroll.clientWidth;
                                        const thumbLeft = thumb.offsetLeft;
                                        const thumbWidth = thumb.offsetWidth;
                                        
                                        // Calculer la position pour centrer l'image
                                        const scrollPosition = thumbLeft - (containerWidth / 2) + (thumbWidth / 2);
                                        
                                        thumbsScroll.scrollTo({
                                            left: scrollPosition,
                                            behavior: 'smooth'
                                        });
                                    }
                                    
                                    // Déclencher le changement de slide manuellement
                                    const slideTo = thumb.getAttribute('data-bs-slide-to');
                                    if (slideTo !== null && carousel) {
                                        const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(carousel);
                                        bsCarousel.to(parseInt(slideTo));
                                    }
                                    return false;
                                }
                                
                                thumb.addEventListener('click', handleThumbClick);
                                thumb.addEventListener('mousedown', handleThumbClick);
                                thumb.addEventListener('touchstart', handleThumbClick, { passive: false });
                            });
                            
                            function updateActiveThumbs(activeIndex) {
                                // Mettre à jour les vignettes desktop
                                thumbs.forEach(thumb => thumb.classList.remove('active'));
                                if (thumbs[activeIndex]) {
                                    thumbs[activeIndex].classList.add('active');
                                }

                                // Mettre à jour les vignettes mobiles et centrer l'image active
                                thumbsMobile.forEach(thumb => thumb.classList.remove('active'));
                                if (thumbsMobile[activeIndex]) {
                                    thumbsMobile[activeIndex].classList.add('active');
                                    
                                    // Centrer l'image active dans le scroll
                                    const thumbsScroll = document.getElementById('thumbsScroll');
                                    if (thumbsScroll) {
                                        const thumbElement = thumbsMobile[activeIndex];
                                        const scrollContainer = thumbsScroll;
                                        const containerWidth = scrollContainer.clientWidth;
                                        const thumbLeft = thumbElement.offsetLeft;
                                        const thumbWidth = thumbElement.offsetWidth;
                                        
                                        // Calculer la position pour centrer l'image
                                        const scrollPosition = thumbLeft - (containerWidth / 2) + (thumbWidth / 2);
                                        
                                        scrollContainer.scrollTo({
                                            left: scrollPosition,
                                            behavior: 'smooth'
                                        });
                                    }
                                }
                            }
                            
                            if (carousel) {
                                carousel.addEventListener('slid.bs.carousel', function (e) {
                                    updateActiveThumbs(e.to);
                                });

                                // Initialiser l'état actif au chargement
                                const initialActive = carousel.querySelector('.carousel-item.active');
                                if (initialActive) {
                                    const carouselItems = Array.from(carousel.querySelectorAll('.carousel-item'));
                                    const initialIndex = carouselItems.indexOf(initialActive);
                                    updateActiveThumbs(initialIndex);
                                }
                            }
                        });
                    </script>

                    <!-- Script pour le lightbox -->
                    <script>
                        // Stocker les images dans un tableau global avec placeholder si nécessaire
                        @php
                            $lightboxImages = array_filter([
                                $article->photo, 
                                $article->photo1 ?? null, 
                                $article->photo2 ?? null, 
                                $article->photo3 ?? null,
                                $article->photo4 ?? null, 
                                $article->photo5 ?? null,
                                $article->photo6 ?? null,
                            ]);
                            // Si aucune image, utiliser le placeholder
                            if (empty($lightboxImages)) {
                                $lightboxImages = ['images/placeholder.png'];
                            }
                            // Convertir tous les chemins en URLs complètes
                            $lightboxImages = array_map(function($img) {
                                return $img ? asset($img) : asset('images/placeholder.png');
                            }, $lightboxImages);
                        @endphp
                        const lightboxImages = @json($lightboxImages);
                        let currentLightboxIndex = 0;

                        // Ouvrir le lightbox avec l'image cliquée
                        document.addEventListener('DOMContentLoaded', function() {
                            const clickableImages = document.querySelectorAll('.image-clickable');
                            
                            clickableImages.forEach((img) => {
                                img.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const imageIndex = parseInt(this.getAttribute('data-image-index')) || 0;
                                    openLightbox(imageIndex);
                                });
                            });
                        });

                        function openLightbox(index) {
                            if (!lightboxImages || lightboxImages.length === 0) return;
                            
                            currentLightboxIndex = index;
                            const modal = document.getElementById('lightboxModal');
                            const lightboxImg = document.getElementById('lightboxImage');
                            const prevBtn = document.querySelector('.lightbox-prev');
                            const nextBtn = document.querySelector('.lightbox-next');
                            
                            // Afficher/masquer les boutons de navigation selon le nombre d'images
                            if (lightboxImages.length <= 1) {
                                if (prevBtn) prevBtn.style.display = 'none';
                                if (nextBtn) nextBtn.style.display = 'none';
                            } else {
                                if (prevBtn) prevBtn.style.display = 'flex';
                                if (nextBtn) nextBtn.style.display = 'flex';
                            }
                            
                            if (lightboxImages[currentLightboxIndex]) {
                                lightboxImg.src = lightboxImages[currentLightboxIndex];
                                lightboxImg.onerror = function() {
                                    this.src = '{{ asset("images/placeholder.png") }}';
                                };
                                modal.classList.add('active');
                                document.body.classList.add('lightbox-open');
                                document.body.style.overflow = 'hidden'; // Empêcher le scroll de la page
                                document.documentElement.style.overflow = 'hidden'; // Empêcher le scroll sur html aussi
                            }
                        }

                        function closeLightbox() {
                            const modal = document.getElementById('lightboxModal');
                            modal.classList.remove('active');
                            document.body.classList.remove('lightbox-open');
                            document.body.style.overflow = ''; // Restaurer le scroll
                            document.documentElement.style.overflow = ''; // Restaurer le scroll sur html aussi
                        }

                        function changeLightboxImage(direction) {
                            if (!lightboxImages || lightboxImages.length <= 1) return;
                            
                            currentLightboxIndex += direction;
                            
                            // Gérer le dépassement des limites (boucle)
                            if (currentLightboxIndex < 0) {
                                currentLightboxIndex = lightboxImages.length - 1;
                            } else if (currentLightboxIndex >= lightboxImages.length) {
                                currentLightboxIndex = 0;
                            }
                            
                            const lightboxImg = document.getElementById('lightboxImage');
                            if (lightboxImages[currentLightboxIndex]) {
                                lightboxImg.src = lightboxImages[currentLightboxIndex];
                                lightboxImg.onerror = function() {
                                    this.src = '{{ asset("images/placeholder.png") }}';
                                };
                            }
                        }

                        // Fermer le lightbox en cliquant sur le fond
                        document.addEventListener('DOMContentLoaded', function() {
                            const modal = document.getElementById('lightboxModal');
                            modal.addEventListener('click', function(e) {
                                if (e.target === modal || e.target.classList.contains('lightbox-content')) {
                                    closeLightbox();
                                }
                            });
                        });

                        // Navigation au clavier
                        document.addEventListener('keydown', function(e) {
                            const modal = document.getElementById('lightboxModal');
                            if (modal && modal.classList.contains('active')) {
                                if (e.key === 'Escape') {
                                    closeLightbox();
                                } else if (e.key === 'ArrowLeft' && lightboxImages && lightboxImages.length > 1) {
                                    changeLightboxImage(-1);
                                } else if (e.key === 'ArrowRight' && lightboxImages && lightboxImages.length > 1) {
                                    changeLightboxImage(1);
                                }
                            }
                        });
                    </script>



                </div>
            </div>
        </div>




















































        <section id="articles-similaires">
        <h3 style="text-align: center; margin-top: 50px;">Articles similaires</h3>
 

    @if($articles->isEmpty())
        <p class="text-center" style="color: red; font-weight: bold;">Aucun article similaire.</p>
    @else
        <div class="container">
            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach($articlesParCategorie as $article)
                    <div class="col">
                        <div class="card rounded-4 article-hover">

                    <!-- Heure en haut à droite -->
                    <div class="position-absolute top-0 end-0 px-1 py-0 rounded-bottom-start small d-flex heure">
                        @php
                            $diffMinutes = $article->created_at->diffInMinutes();
                        @endphp

                        @if ($diffMinutes < 60)
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

                            <!-- Prix et titre -->
                            <div class="article-price">
                                <span>{{ number_format($article->prix_ht, 0, '', '.') }} CFA</span>    
                            </div>
                            <div class="article-title">
                                {{ $article->titre }}
                            </div>

                            <!-- Localisation -->
                            <div class="article-localisation">
                                <img src="{{ asset('images/localisation.png') }}" alt="Localisation" class="localisation-icon">
                                <p class="localisation-text">{{ $article->lieu ?? 'Ville non spécifiée' }}</p>
                            </div>

                            <!-- Like -->
                            <div class="like-container d-flex align-items">                
                                <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                    @csrf
                                    <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                    <button type="button" class="like-button" data-article-id="{{ $article->id }}">
                                        <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                    </button>
                                    <div id="count-js-{{ $article->id }}" class="like-count d-flex align-items-center">
                                        <span class="like-number">{{ $article->usersWhoLiked->count() }}</span>
                                    </div>
                                </form>                                                   
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

        @endforeach 
    </div> <!-- fin row -->
@endif
        </section> <!-- fin articles-similaires -->

    <!-- Modale de partage (globale, accessible depuis mobile et desktop) -->
    <div id="share-modal" class="share-modal" role="dialog" aria-modal="true" aria-labelledby="share-modal-title" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
        <div class="share-modal-content" style="background-color: #fefefe; margin: 0; padding: 30px; border: 1px solid #888; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <span class="close" onclick="closeShareModal()" role="button" aria-label="Fermer la modale" tabindex="0" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 20px;">&times;</span>
            <h3 id="share-modal-title" style="margin-top: 0; color: #333;">Partager ce lien</h3>
            <input type="text" id="share-url" value="{{ Request::url() }}" readonly aria-label="URL à partager" style="width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background-color: #f9f9f9; box-sizing: border-box;">
            <button onclick="copyToClipboard()" class="copy-btn" aria-label="Copier le lien dans le presse-papiers" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #515ffb, #3b4fd8); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 15px; transition: transform 0.2s;">
                <i class="fas fa-copy"></i> Copier le lien
            </button>
            <div class="share-icons" style="display: flex; gap: 12px; justify-content: center;">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(Request::url()) }}" target="_blank" rel="noopener noreferrer" class="facebook" aria-label="Partager sur Facebook" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #1877f2; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://wa.me/?text={{ $shareMessageEncoded }}" target="_blank" rel="noopener noreferrer" class="whatsapp" aria-label="Partager sur WhatsApp" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #25d366; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(Request::url()) }}" target="_blank" rel="noopener noreferrer" class="twitter" aria-label="Partager sur Twitter" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #1da1f2; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                    <i class="fab fa-x-twitter"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast-notification" class="toast-notification" role="alert" aria-live="polite" style="display: none; position: fixed; bottom: 20px; right: 20px; background: linear-gradient(135deg, #515ffb, #3b4fd8); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 40px rgba(81, 95, 251, 0.3); z-index: 10001; max-width: 350px; animation: slideInRight 0.3s ease-out;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
            <span id="toast-message" style="flex: 1; font-weight: 500;"></span>
        </div>
    </div>

    <!-- Styles pour les notifications toast -->
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .toast-notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        .toast-notification.hiding {
            animation: slideOutRight 0.3s ease-out;
        }
        
        .share-modal-content .close:hover {
            color: #000;
        }
        
        .share-modal-content .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);
        }
        
        .share-modal-content .share-icons a:hover {
            transform: scale(1.1);
        }
        
        /* Animation simple pour l'icône (sans déplacer le bouton) */
        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }
        
        /* Correction du décalage - Le bouton reste complètement fixe */
        .like-container-detail {
            position: relative !important;
            transform: none !important;
            transition: none !important;
            overflow: visible !important;
            margin: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: auto !important;
        }
        
        .like-button-detail {
            position: relative !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: none !important;
            transition: none !important;
            transform: none !important;
            top: 0 !important;
            left: 0 !important;
        }
        
        .like-button-detail:active,
        .like-button-detail:focus {
            transform: none !important;
            outline: none !important;
        }
        
        /* Correction de l'icône - centrée dans le conteneur */
        .like-icon-detail {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            position: absolute !important;
            margin: 0 !important;
            padding: 0 !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        .like-icon-detail.liking {
            animation: iconPulse 0.3s ease;
        }
        
        .like-icon-detail.liked {
            animation: iconPulse 0.3s ease;
        }
        
        .like-icon-detail.unliking {
            animation: iconPulse 0.2s ease;
        }
        
        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        /* Animation professionnelle - gouttes d'eau */
        .water-effect-container {
            position: fixed;
            pointer-events: none;
            z-index: 99999;
            overflow: hidden;
        }
        
        .water-ring {
            position: absolute;
            border: 2px solid #ff6b6b;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
        }
        
        .water-ring-1 {
            animation: ring1 0.6s ease-out forwards;
        }
        
        .water-ring-2 {
            animation: ring2 0.8s ease-out forwards;
        }
        
        .water-ring-3 {
            animation: ring3 1s ease-out forwards;
        }
        
        @keyframes ring1 {
            0% { 
                width: 20px; 
                height: 20px; 
                opacity: 0.8; 
                border-width: 2px;
            }
            100% { 
                width: 100px; 
                height: 100px; 
                opacity: 0; 
                border-width: 1px;
            }
        }
        
        @keyframes ring2 {
            0% { 
                width: 20px; 
                height: 20px; 
                opacity: 0.6; 
                border-width: 2px;
            }
            100% { 
                width: 140px; 
                height: 140px; 
                opacity: 0; 
                border-width: 1px;
            }
        }
        
        @keyframes ring3 {
            0% { 
                width: 20px; 
                height: 20px; 
                opacity: 0.4; 
                border-width: 2px;
            }
            100% { 
                width: 180px; 
                height: 180px; 
                opacity: 0; 
                border-width: 1px;
            }
        }
        
        .water-drop {
            position: absolute;
            width: 10px;
            height: 10px;
            background: radial-gradient(circle, #ff6b6b 0%, #ff8787 70%, transparent 100%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 8px rgba(255, 107, 107, 0.8), 0 0 12px rgba(255, 107, 107, 0.4);
            animation: dropMove 1.2s ease-out forwards;
        }
        
        @keyframes dropMove {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                opacity: 0.9;
                transform: translate(calc(-50% + calc(var(--x) * 0.5)), calc(-50% + calc(var(--y) * 0.5))) scale(1.2);
            }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--x)), calc(-50% + var(--y))) scale(0.2);
            }
        }
        
        @media (max-width: 576px) {
            #toast-notification {
                bottom: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
    </style>

    <!-- Scripts globaux pour les fonctionnalités -->
    <script>
        // Fonction pour afficher une notification toast
        function showToast(message, duration = 3000) {
            const toast = document.getElementById('toast-notification');
            const toastMessage = document.getElementById('toast-message');
            
            if (!toast || !toastMessage) return;
            
            toastMessage.textContent = message;
            toast.style.display = 'block';
            toast.classList.remove('hiding');
            
            // Masquer après la durée spécifiée
            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            }, duration);
        }

        // Fonctions globales pour la modale de partage
        window.openShareModal = function() {
            const modal = document.getElementById('share-modal');
            if (modal) {
                modal.style.display = 'block';
                // Empêcher le scroll du body
                document.body.style.overflow = 'hidden';
                // Focus sur le bouton de fermeture pour l'accessibilité
                const closeBtn = modal.querySelector('.close');
                if (closeBtn) {
                    setTimeout(() => closeBtn.focus(), 100);
                }
            }
        };

        window.closeShareModal = function() {
            const modal = document.getElementById('share-modal');
            if (modal) {
                modal.style.display = 'none';
                // Restaurer le scroll du body
                document.body.style.overflow = '';
            }
        };

        window.copyToClipboard = function() {
            const urlInput = document.getElementById("share-url");
            if (!urlInput) return;
            
            urlInput.select();
            urlInput.setSelectionRange(0, 99999); // Mobile support
            
            const copyText = urlInput.value;
            let copied = false;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(copyText).then(() => {
                    copied = true;
                    showToast("Lien copié dans le presse-papiers !");
                }).catch(() => {
                    // Fallback pour les navigateurs plus anciens
                    try {
                        document.execCommand('copy');
                        copied = true;
                        showToast("Lien copié dans le presse-papiers !");
                    } catch (err) {
                        showToast("Erreur lors de la copie. Veuillez copier manuellement.");
                    }
                });
            } else {
                // Fallback pour les navigateurs plus anciens
                try {
                    document.execCommand('copy');
                    copied = true;
                    showToast("Lien copié dans le presse-papiers !");
                } catch (err) {
                    showToast("Erreur lors de la copie. Veuillez copier manuellement.");
                }
            }
        };

        // Fonction professionnelle pour l'animation de gouttes d'eau (définie en premier)
        window.createLikeParticles = function(button) {
            if (!button) return;
            
            const rect = button.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            // Créer le conteneur avec positionnement fixe
            const container = document.createElement('div');
            container.className = 'water-effect-container';
            container.style.left = centerX + 'px';
            container.style.top = centerY + 'px';
            container.style.width = '0';
            container.style.height = '0';
            document.body.appendChild(container);
            
            // Créer 3 ronds concentriques
            for (let i = 1; i <= 3; i++) {
                const ring = document.createElement('div');
                ring.className = 'water-ring water-ring-' + i;
                ring.style.left = '0';
                ring.style.top = '0';
                ring.style.width = '20px';
                ring.style.height = '20px';
                container.appendChild(ring);
                
                // Nettoyer chaque rond après son animation
                setTimeout(() => {
                    if (ring.parentNode) {
                        ring.remove();
                    }
                }, (600 + (i * 200)));
            }
            
            // Créer 12 gouttes qui se dispersent
            for (let i = 0; i < 12; i++) {
                const drop = document.createElement('div');
                drop.className = 'water-drop';
                drop.style.left = '0';
                drop.style.top = '0';
                
                const angle = (Math.PI * 2 * i) / 12;
                const distance = 50 + Math.random() * 20;
                const x = Math.cos(angle) * distance;
                const y = Math.sin(angle) * distance;
                
                drop.style.setProperty('--x', x + 'px');
                drop.style.setProperty('--y', y + 'px');
                drop.style.animationDelay = (Math.random() * 0.2) + 's';
                
                container.appendChild(drop);
            }
            
            // Nettoyer le conteneur après toutes les animations
            setTimeout(() => {
                if (container.parentNode) {
                    // Nettoyer tous les enfants restants
                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }
                    container.remove();
                }
            }, 1200);
        };

        // Fermer la modale si on clique à l'extérieur
        document.addEventListener('DOMContentLoaded', function() {
            const shareModal = document.getElementById("share-modal");
            if (shareModal) {
                shareModal.addEventListener('click', function(event) {
                    if (event.target === shareModal) {
                        closeShareModal();
                    }
                });
                
                // Fermer avec la touche Escape
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && shareModal.style.display === 'block') {
                        closeShareModal();
                    }
                });
                
                // Gestion du focus pour l'accessibilité
                const closeBtn = shareModal.querySelector('.close');
                if (closeBtn) {
                    closeBtn.addEventListener('keydown', function(event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            closeShareModal();
                        }
                    });
                }
            }

            // Script pour les boutons like-button-detail
            const likeButtonsDetail = document.querySelectorAll(".like-button-detail");
            
            likeButtonsDetail.forEach(function (button) {
                button.addEventListener("click", function () {
                    const articleId = this.getAttribute("data-article-id");
                    const likeIcon = this.querySelector(".like-icon-detail");
                    const form = this.closest('form');
                    const formId = form ? form.id : null;
                    
                    // Vérifier l'état actuel (liked ou non)
                    const isCurrentlyLiked = likeIcon.classList.contains("bi-heart-fill");
                    
                    // Feedback visuel immédiat (optimistic UI)
                    if (isCurrentlyLiked) {
                        // Animation de dislike
                        likeIcon.classList.add("unliking");
                        likeIcon.classList.remove("bi-heart-fill");
                        likeIcon.classList.add("bi-heart");
                        likeIcon.classList.remove("liked");
                    } else {
                        // Animation de like
                        likeIcon.classList.add("liking");
                        likeIcon.classList.remove("bi-heart");
                        likeIcon.classList.add("bi-heart-fill");
                        likeIcon.classList.add("liked");
                        
                        // Créer des particules d'animation (gouttes d'eau)
                        window.createLikeParticles(button);
                    }
                    
                    // Envoie la requête AJAX
                    fetch(`/articles/${articleId}/like`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({ article_id: articleId }),
                    })
                        .then((response) => {
                            // Vérifie si la réponse est du JSON ou du HTML (redirection login)
                            const contentType = response.headers.get("content-type") || "";
                            if (response.status === 401 || contentType.includes("text/html")) {
                                // Annuler l'optimistic UI
                                if (isCurrentlyLiked) {
                                    likeIcon.classList.remove("unliking");
                                    likeIcon.classList.add("bi-heart-fill");
                                    likeIcon.classList.remove("bi-heart");
                                    likeIcon.classList.add("liked");
                                } else {
                                    likeIcon.classList.remove("liking");
                                    likeIcon.classList.add("bi-heart");
                                    likeIcon.classList.remove("bi-heart-fill");
                                    likeIcon.classList.remove("liked");
                                }
                                
                                const modalAuth = document.getElementById("modal-auth");
                                if (modalAuth) {
                                    modalAuth.style.display = "flex";
                                } else {
                                    showToast("Vous devez être connecté pour liker un article.");
                                }
                                return null;
                            }
                            return response.json();
                        })
                        .then((data) => {
                            if (!data) return; // Si data est null (redirection login), on arrête
                            
                            // Retirer les classes d'animation temporaires
                            likeIcon.classList.remove("liking", "unliking");
                            
                            if (data.liked) {
                                likeIcon.classList.add("liked");
                                likeIcon.classList.remove("bi-heart");
                                likeIcon.classList.add("bi-heart-fill");
                                showToast("Article ajouté aux favoris ❤️", 2000);
                            } else {
                                likeIcon.classList.remove("liked");
                                likeIcon.classList.add("bi-heart");
                                likeIcon.classList.remove("bi-heart-fill");
                                showToast("Article retiré des favoris", 2000);
                            }
                        })
                        .catch((error) => {
                            console.error("Erreur lors de la requête AJAX :", error);
                            
                            // Annuler l'optimistic UI en cas d'erreur
                            likeIcon.classList.remove("liking", "unliking");
                            if (isCurrentlyLiked) {
                                likeIcon.classList.add("bi-heart-fill");
                                likeIcon.classList.remove("bi-heart");
                                likeIcon.classList.add("liked");
                            } else {
                                likeIcon.classList.add("bi-heart");
                                likeIcon.classList.remove("bi-heart-fill");
                                likeIcon.classList.remove("liked");
                            }
                            
                            showToast("Erreur lors de l'action. Veuillez réessayer.", 3000);
                        });
                });
            });
            
        });

        // Fonction pour convertir les URLs en liens cliquables
        function convertUrlsToLinks(text) {
            // Expression régulière pour détecter les URLs
            const urlRegex = /(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}[^\s]*)/g;
            return text.replace(urlRegex, function(url) {
                let href = url;
                if (!url.match(/^https?:\/\//)) {
                    href = 'http://' + url;
                }
                return '<a href="' + href + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
            });
        }

        // Fonction pour gérer le "lire plus/lire moins"
        function initReadMore(descriptionId, buttonId) {
            const descriptionContent = document.getElementById(descriptionId);
            const descriptionText = descriptionContent.querySelector('.description-text');
            const readMoreBtn = document.getElementById(buttonId);
            const readMoreText = readMoreBtn.querySelector('.read-more-text');
            const readLessText = readMoreBtn.querySelector('.read-less-text');

            if (!descriptionText || !readMoreBtn) return;

            // Convertir les URLs en liens
            descriptionText.innerHTML = convertUrlsToLinks(descriptionText.innerHTML);

            // Vérifier si le contenu dépasse la hauteur maximale
            const maxHeight = window.innerWidth <= 991 ? 120 : 150;
            const textHeight = descriptionText.scrollHeight;

            if (textHeight > maxHeight) {
                descriptionText.classList.add('collapsed');
                readMoreBtn.style.display = 'block';
            }

            readMoreBtn.addEventListener('click', function() {
                if (descriptionText.classList.contains('collapsed')) {
                    descriptionText.classList.remove('collapsed');
                    readMoreText.style.display = 'none';
                    readLessText.style.display = 'inline';
                } else {
                    descriptionText.classList.add('collapsed');
                    readMoreText.style.display = 'inline';
                    readLessText.style.display = 'none';
                }
            });
        }

        // Initialiser pour desktop et mobile
        document.addEventListener('DOMContentLoaded', function() {
            initReadMore('description-desktop', 'read-more-desktop');
            initReadMore('description-mobile', 'read-more-mobile');
        });
    </script>

       
                </div>
            </div>
        </div>


@endsection