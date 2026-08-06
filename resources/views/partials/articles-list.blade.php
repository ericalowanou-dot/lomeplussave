@if($articles->isEmpty())
    <p class="text-center" style="color: red; font-weight: bold;">Aucun résultat trouvé.</p>
@else
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

                            <!-- Profil / Pro -->
                            <div class="profil-certification">
                                @if($article->isBoosted())
                                    <div class="badge bg-warning text-dark" style="font-size: 0.7rem; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                                @else
                                    @if($article->user)
                                        <div class="user-info">
                                            <a href="{{ $article->user->shop_url }}" style="display: flex; align-items: center; text-decoration: none;">
                                                <img src="{{ $article->user->getProfilPhotoUrl() }}" alt="Profil de {{ $article->user->name }}" class="profile-picture"
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

            {{-- Section pubs après 3 lignes (6 / 9 / 12 selon le nombre de colonnes) --}}
            @if($loop->iteration === 6)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-md-none', 'feedId' => 'ads-feed-mobile'])
            @endif
            @if($loop->iteration === 9)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-md-block d-lg-none', 'feedId' => 'ads-feed-tablet'])
            @endif
            @if($loop->iteration === 12)
                @include('partials.publicites-feed', ['feedVisibilityClass' => 'd-none d-lg-block', 'feedId' => 'ads-feed-desktop'])
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
                    // Gardé pour compatibilité quand la page est chargée "classiquement".
                    // En cas de pagination AJAX, les scripts injectés via innerHTML ne s’exécutent pas :
                    // l’initialisation est donc faite dans resources/js/partialReload.js.
                    document.addEventListener("DOMContentLoaded", function () {
                        const banner = document.querySelector(".banner-ad");
                        if (banner) banner.dataset.loginUrl = "{{ route('login') }}";
                    });
                </script>
                @endguest
            @endif

        @endforeach 
    </div> <!-- fin row -->
@endif
