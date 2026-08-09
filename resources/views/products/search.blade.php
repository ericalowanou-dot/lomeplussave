@extends('layouts.app2')

@section('title', 'Recherche - Lome+')

@section('content')
<main>
    {{-- Boutons pub haut (cadeau / WhatsApp) retirés — comme sur l'accueil --}}

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

  <div id="articles-results" data-context="articles">
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
    @include('partials.articles-list', [
        'articles' => $articles,
        'feedIdPrefix' => 'promo-feed-search',
    ])
@endif
  </div><!-- /#articles-results -->

            </div>
  
  
        
        </div>



    </main>


        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4" id="pagination-wrapper">
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


