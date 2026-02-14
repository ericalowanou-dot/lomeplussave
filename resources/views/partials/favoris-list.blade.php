@if(isset($favoris) && $favoris->isEmpty())
    <p class="text-center" style="color: red; font-weight: bold;">Aucun article trouvé.</p>
@elseif(isset($articles) && $articles->isEmpty())
    <p class="text-center" style="color: red; font-weight: bold;">Aucun article trouvé.</p>
@else
    <!-- Affichage des articles favoris -->
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach(isset($favoris) ? $favoris : $articles as $article)
            <div class="col">
                <div class="card rounded-4 article-hover">
                    <!-- Heure en haut à droite -->
                    <div class="position-absolute top-0 end-0 px-1 py-0 rounded-bottom-start small d-flex heure">
                        <i class="bi bi-clock" style="padding-right: 5px;"></i>
                        <p class="mb-0">
                            <strong>{{ intval($article->created_at->diffInDays()) }}</strong> jour{{ intval($article->created_at->diffInDays()) > 1 ? 's' : '' }}
                        </p>
                    </div>
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
                        <!-- Localisation et la ville -->
                        <div class="article-localisation">
                            <img src="{{ asset('images/localisation.png') }}" alt="Localisation" class="localisation-icon">
                            <p class="localisation-text">{{ $article->lieu ?? 'Ville non spécifiée' }}</p>
                        </div>

                        <!-- ligne horizontale qui sépare la photo et les autres elements  -->
                        <hr style="border-top: 3px solid #000000;  width: 100%; margin-bottom: 5px; margin-top: 5px;">

                        <div class="d-flex justify-content-between align-items-center profil-certification">
                            @if($article->isBoosted())
                                <div class="badge bg-warning text-dark" style="font-size: 0.7rem; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                            @else
                                <!-- photo de profil et le nom de l'utilisateur -->
                                @if($article->user)
                                    <div class="user-info">
                                        <a href="{{ route('boutique.show', $article->user->id) }}" style="display: flex; align-items: center; text-decoration: none;">
                                            <img src="{{ $article->user->getProfilPhotoUrl() }}" 
                                                 alt="Profil de {{ $article->user->name }}" 
                                                 class="profile-picture"
                                                 onerror="this.src='{{ asset('images/user_default.png') }}';">
                                            <p class="text-muted user-name mb-0{{ $article->user->estCertifie() ? '' : ' not-certified' }}">
                                                {{ $article->user->name ?? 'nom non spécifiée' }}
                                            </p>
                                        </a>
                                    </div>
                                @endif 
                                <!-- certification de l'utilisateur -->
                                @if($article->user && $article->user->estCertifie())
                                    <div class="certification">
                                        <img src="{{ asset('images/certifier.png') }}" alt="Certifié" class="certification-logo">
                                        <span class="certification-text">Vérifié</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>                                </div> 
                </div>
            </div>
        @endforeach
    </div>
@endif
