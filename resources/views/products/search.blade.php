@extends('layouts.app2')

@section('title', 'Recherche - Lome+')

@section('content')
<main>
    <!-- Conteneur de publicité -->
    <div class="haut-publicite" id="hautPublicite">
        <a href="https://abonnements.lomeplus.com" class="gauche">
            <img src="{{ asset('images/true-logo.png') }}" alt="image-publicité">    
            cadeau  
        </a>
        @if(auth()->check())
        <a href="https://youtube.com/@lomeplus" class="droite">
            Nous suivre
        </a>
        @else
        <a href="https://whatsapp.com/channel/0029VatlBs06GcG5owxIlF0T" target="_blank"  class="droite">
            <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" width="20"> 
            WhatsApp
        </a>
        @endif
    </div>

    <style>
        /* Pour Chrome, Safari et Opera */
        body::-webkit-scrollbar {
        display: none;
        }

        /* Pour Firefox */
        body {
        scrollbar-width: none; /* Cache la barre sur Firefox */
        }

        /* Pour IE et Edge */
        body {
        -ms-overflow-style: none;
        }
    </style>

    <!-- Script de disparition et d'apparition des bouton de publicités -->
    <script>
        let lastScrollTop = 0;
        window.addEventListener("scroll", function() {
            let st = window.scrollY || document.documentElement.scrollTop;
            const pub = document.getElementById("hautPublicite");

            if (st > lastScrollTop) {
                // scroll vers le bas → cacher
                pub.classList.add("disparaitre");
            } else {
                // scroll vers le haut → montrer
                pub.classList.remove("disparaitre");
            }
            lastScrollTop = st <= 0 ? 0 : st; // évite valeur négative
        });
    </script>

    <!-- style pour les boutons de publicité  -->
    <style>
        .haut-publicite {
            position: relative;
            text-align: center;
            justify-content: center;
            align-items: center;
            top: 200px;
            max-width: 100%; 
            box-sizing: border-box;
            display: flex;
            gap: 20px;
            z-index: 100;
            transition: all 0.6s ease;
            overflow-x: hidden;
            overflow-y: visible;
            padding: 0 10px 10px 10px;
        }


        .droite, .gauche{
            min-width: 160px;
            max-width: 180px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            gap: 6px;
            padding: 4px 8px;
            text-decoration: none;
            color: white;
            border-radius: 8px;
            transition: transform 0.6s ease, opacity 0.6s ease;
            box-shadow: 4px 4px 6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(220, 218, 218, 1)
        }

        .gauche{
            background: linear-gradient(135deg, #f7971e, #ffd200); /* orange dégradé */
        }

        /* Bouton WhatsApp */
        a.droite[href*="whatsapp"] {
            background: linear-gradient(135deg, #25D366, #128C7E); /* vert WhatsApp */
        }

        /* Bouton YouTube */
        a.droite[href*="youtube"] {
            background: linear-gradient(135deg, #FF0000, #9d0303ff); /* rouge YouTube */
        }


        .bouton-gift img {
            width: 24px;
            height: 24px;
            background-color: white;
            border-radius: 50%;
        }

            .gauche img {
            width: 24px;
            height: 24px;
            background-color: white;
            border-radius: 50%;
        }


        .bouton-gift:hover {
            transform: scale(1.05);
        }

        /*  Animation disparition */
        .disparaitre .gauche {
            transform: translateX(-200%) rotate(-20deg);
            opacity: 0;
        }
        .disparaitre .droite {
            transform: translateX(200%) rotate(20deg);
            opacity: 0;
        }
    </style>

    <!-- style pour le scroll automatique des articles horizontaux  -->
    <style>
        /* Active le défilement fluide */
        .scroll-container {
            scroll-behavior: smooth;
        }
        .scroll-container .d-flex {
            display: flex;
            gap: 10px;
            transition: transform 2s ease; /* plus lent */
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search'); 
            const horizontalBlock = document.getElementById('horizontal-articles');
            const filterButton = document.getElementById('openFilter');
            const titreNouveate = document.querySelector('.titre-nouveaute');

            searchInput.addEventListener('input', function() {
                const value = this.value.trim();

                if (value.length > 0) {
                    horizontalBlock.classList.add('collapsed'); // bloc se rétracte
                    filterButton.classList.add('disparaitre'); // bouton de filtre disparait
                    titreNouveate.classList.add('disparaitre'); // titre nouveauté disparait

                } else {
                    horizontalBlock.classList.remove('collapsed'); // bloc reprend sa taille
                    filterButton.classList.remove('disparaitre'); // bouton de filtre réapparait
                    titreNouveate.classList.remove('disparaitre'); // titre nouveauté réapparait
                }
            });
        });

    </script>

    <!-- belle transition  -->
    <style>
        #horizontal-articles {
            height: 230px; /* hauteur normale du scroll horizontal */
            /* overflow: hidden;  */
            transition: height 0.5s ease, opacity 0.5s ease;
        }

        #horizontal-articles.collapsed {
            height: 0;   /* quand on veut “cacher” */
            margin-top: 120px; /* enlever l'espace au-dessus */;
            opacity: 0;  /* disparaît visuellement */
        }

        #openFilter.disparaitre {
            opacity: 0;
            height: 0;
            overflow: hidden;

        }
        .titre-nouveaute.disparaitre {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }



    </style>




<script>
    document.addEventListener("DOMContentLoaded", function () {
        let modal = document.getElementById("filterModal");
        let openBtn = document.getElementById("openFilter");
        let closeBtns = document.querySelectorAll(".close-filter");
        let form = document.getElementById("filterForm");


        // Ouvrir modal
        openBtn.onclick = () => modal.style.display = "block";

        // Fermer modal
        closeBtns.forEach(btn => btn.onclick = () => modal.style.display = "none");

        // Fermer si clic extérieur
        window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

        // Soumission AJAX
        form.onsubmit = function (e) {
            e.preventDefault();

            let formData = new FormData(form);

            fetch("{{ route('articles.index') }}", {
                method: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" },
            }).then(res => res.text())
            .then(html => {
                document.querySelector("#articles-list").innerHTML = html;
                modal.style.display = "none";
            });
        };
    });
</script>

            
            <h6 class="titre-nouveaute" style="margin-bottom: 20px; background-color: none;">Les nouveautés </h6>




        <div class="container mt-4" style="margin-top: 180px !important;">

  <!-- Informations sur la recherche -->
  @if(isset($q) && $articles->total() > 0)
  <div class="search-results-header mb-4" style="padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #FF9900; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <h5 style="margin: 0; color: #333; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <strong style="color: #FF9900;">{{ $articles->total() }}</strong> résultat(s) trouvé(s) pour "<strong style="color: #495057;">{{ $q }}</strong>"
    </h5>
  </div>
  @endif

  @if($articles->isEmpty())
    <div class="no-results-container" style="max-width: 600px; margin: 60px auto; padding: 40px 20px; text-align: center;">
      <!-- Icône de recherche -->
      <div class="no-results-icon" style="width: 120px; height: 120px; margin: 0 auto 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.5;">
          <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <!-- Message principal -->
      <h3 style="color: #333; font-size: 1.5rem; font-weight: 600; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">
        Aucun résultat trouvé
      </h3>
      
      <p style="color: #6c757d; font-size: 1rem; margin-bottom: 30px; line-height: 1.6;">
        Nous n'avons trouvé aucun article correspondant à <strong style="color: #495057;">"{{ $q }}"</strong>
      </p>

      <!-- Suggestions -->
      <div class="suggestions-box" style="background: #f8f9fa; border-radius: 12px; padding: 25px; margin-bottom: 30px; text-align: left;">
        <h5 style="color: #495057; font-size: 1rem; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 17L12 22L22 17" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Suggestions pour améliorer votre recherche :
        </h5>
        <ul style="list-style: none; padding: 0; margin: 0; color: #6c757d; line-height: 2;">
          <li style="margin-bottom: 10px; display: flex; align-items: start; gap: 10px;">
            <span style="color: #FF9900; font-weight: bold; margin-right: 5px;">•</span>
            <span>Vérifiez l'orthographe des mots-clés</span>
          </li>
          <li style="margin-bottom: 10px; display: flex; align-items: start; gap: 10px;">
            <span style="color: #FF9900; font-weight: bold; margin-right: 5px;">•</span>
            <span>Utilisez des termes plus généraux ou des synonymes</span>
          </li>
          <li style="margin-bottom: 10px; display: flex; align-items: start; gap: 10px;">
            <span style="color: #FF9900; font-weight: bold; margin-right: 5px;">•</span>
            <span>Essayez de réduire le nombre de mots dans votre recherche</span>
          </li>
          <li style="display: flex; align-items: start; gap: 10px;">
            <span style="color: #FF9900; font-weight: bold; margin-right: 5px;">•</span>
            <span>Parcourez les catégories pour découvrir nos produits</span>
          </li>
        </ul>
      </div>

      <!-- Boutons d'action -->
      <div class="action-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('articles.index') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #FF9900 0%, #E68900 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(255, 153, 0, 0.3);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 22V12H15V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Voir tous les articles
        </a>
        <button onclick="document.getElementById('search').focus(); document.getElementById('search').select();" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: white; color: #FF9900; text-decoration: none; border: 2px solid #FF9900; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Nouvelle recherche
        </button>
      </div>
    </div>

    <style>
      .no-results-container a:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 153, 0, 0.4) !important;
      }
      .no-results-container button:hover {
        background: #FF9900 !important;
        color: white !important;
        transform: translateY(-2px);
      }
      .no-results-icon {
        animation: pulse 2s ease-in-out infinite;
      }
      @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
      }
    </style>
@else
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach($articles as $article)
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
                            <img src="{{ asset('images/annonce.jpg') }}" alt="Publicité" class="ad-img active">
                            <img src="{{ asset('images/annonce2.png') }}" alt="Publicité" class="ad-img">
                            <img src="{{ asset('images/annonce3.png') }}" alt="Publicité" class="ad-img">
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
@endif

            </div>
  
  
        
        </div>



    </main>


        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $articles->links() }}
        </div>


            <footer class="text-body-secondary py-5">
            <div class="container">
                <!-- Footer content si nécessaire -->
            </div>
            </footer>
            
            <!-- Bouton Scroll to Top -->
            @include('components.scroll-to-top')
            <script src="{{asset('js/bootstrap.bundle.min.js')}}" defer></script>




@endsection


