<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Définition de l'encodage des caractères -->
  <meta charset="UTF-8">
  <!-- Configuration pour un affichage responsive -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
  <title>Catégories Scrollables</title>
</head>

<body>
  <nav class="navigation">

<!-- <div class="search-container-navigation">
    <form id="search-form" style="display:flex; align-items:center;">
        <input type="text"
               name="q"
               class="search-bar-navigation"
               id="search"
               placeholder="Rechercher..."
               value="{{ request()->q ?? ''}}"
               data-context="{{ $contextPage ?? 'articles' }}"> {{-- le contexte --}}
        <button type="submit" id="search-button" class="search-button-navigation">
            <img src="{{asset('images/search.png')}}" alt="Rechercher">
        </button>
    </form>
</div> -->
<div class="search-container-navigation">
    <form id="search-form" action="{{ route('article.search') }}" method="GET" style="display:flex; align-items:center;">
        <input type="text"
               name="q"
               class="search-bar-navigation"
               id="search"
               placeholder="Rechercher..."
               value="{{ request()->q ?? ''}}"
               data-context="{{ $contextPage ?? 'articles' }}"
               autocomplete="off"
               autocorrect="off"
               autocapitalize="off"
               spellcheck="false">
        <button type="submit" id="search-button" class="search-button-navigation">
            <img src="{{asset('images/search.png')}}" alt="Rechercher">
        </button>
    </form>
</div>

{{-- NOTE: ne pas utiliser l'id "articles-results" ici.
   Il est réservé au conteneur principal de la liste d'articles dans <main>. --}}

<!-- recherche ajax en fonction des pages -->
<!-- <script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = document.querySelectorAll('input[data-context]');

    searchInputs.forEach(searchInput => {
        const context = searchInput.dataset.context;
        const resultsContainer = document.getElementById(`${context}-results`);
        if (!resultsContainer) return;

        const routes = {
            'articles': '/search/articles',
            'favoris': '/search/favoris',
            'mesannonces': '/search/mesannonces'
        };

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length >= 3) {
                const url = `${routes[context]}?q=${encodeURIComponent(query)}`;
                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        resultsContainer.innerHTML = html;
                    });
            } else {
                // réaffiche les articles initiaux si query < 3
                resultsContainer.innerHTML = resultsContainer.dataset.initial;
            }
        });

        // sauvegarder le contenu initial pour reset
        resultsContainer.dataset.initial = resultsContainer.innerHTML;
    });
});

</script> -->
{{-- 
<!-- RECHERCHE AJAX TEMPORAIREMENT DÉSACTIVÉE -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.querySelector('input[data-context]');
      const context = searchInput.dataset.context;
      const resultsContainer = document.getElementById(`${context}-results`);
      if (!resultsContainer) return;

      const routes = {
          'articles': '/search/articles',
          'favoris': '/search/favoris',
          'mesannonces': '/search/mesannonces'
      };

      // sauvegarde du contenu initial
      resultsContainer.dataset.initial = resultsContainer.innerHTML;

      // Recherche AJAX à la frappe
      searchInput.addEventListener('input', function () {
          
          if (query.length >= 3) {
              const url = `${routes[context]}?q=${encodeURIComponent(query)}`;
              fetch(url)
                  .then(res => res.text())
                  .then(html => {
                      resultsContainer.innerHTML = html;
                  });
          } else {
              // si moins de 3 caractères on remet l'initial
              resultsContainer.innerHTML = resultsContainer.dataset.initial;
          }
      });

      // Laisser la recherche classique avec Entrée ou clic bouton
      const searchForm = document.getElementById('search-form');
      searchForm.addEventListener('submit', function (e) {
          // Ici on ne bloque PAS → la recherche classique se fera vers /search?q=...
          // Si tu veux tout faire en Ajax, décommente les lignes suivantes :
          // e.preventDefault();
          // const query = searchInput.value.trim();
          // fetch(`${routes[context]}?q=${encodeURIComponent(query)}`)
          //   .then(res => res.text())
          //   .then(html => {
          //       resultsContainer.innerHTML = html;
          //   });
      });
  });
</script>
--}}


      <!-- scrip de recherche ajax  -->


      <!-- Bouton de défilement vers la gauche -->
      <button class="scroll-button left"><</button>
      <!-- Bouton de défilement vers la droite -->
      <button class="scroll-button right">></button>
      
      <!-- Conteneur principal des catégories --> 
      <!-- Conteneur principal des catégories -->
      <div class="categories-wrapper">
        <div class="categories-container-navigation" id="categoriesScrollAuto">
          <div class="categories-navigation">
            @foreach($categories as $categorie)
              <button class="categories-item-navigation" data-id="{{ $categorie->id }}">
                <img src="{{ $categorie->image ? asset($categorie->image) : asset('images/placeholder.png') }}" alt="{{ $categorie->nom }}">
                <p class="categories-nom-navigation">{{ $categorie->nom }}</p>
              </button>
            @endforeach
          </div>
        </div>

<!-- Main de hint pour indiquer qu'on peut scroller -->
<div id="categoriesHint" class="categories-hint" style="display:none;">
  <img src="{{ asset('images/swipe3.png') }}" alt="Swipe" />
</div>


        <!-- Overlay pour le flou -->
<div class="overlay" id="overlay"></div>

        <!-- Conteneur pour afficher les sous-catégories -->
        <div class="subcategories-container" style="display: none; text-align: center;">
          <div class="subcategories"></div>
        </div>


        <script>
          document.addEventListener("DOMContentLoaded", function () {
              const isMobile = window.matchMedia("(pointer: coarse)").matches;
              const categories = document.querySelectorAll(".categories-item-navigation");
              const subcategoriesContainer = document.querySelector(".subcategories-container");
              const subcategoriesDiv = document.querySelector(".subcategories");
          
              // Positionne le container sous la catégorie survolée/clickée
              /*function positionSubcategoriesContainer(categoryBtn) {
                  const rect = categoryBtn.getBoundingClientRect();
                  subcategoriesContainer.style.left = rect.left + "px";
                  subcategoriesContainer.style.top = (rect.bottom + window.scrollY) + "px";
              } */

              function positionSubcategoriesContainer() {
                  const nav = document.querySelector(".navigation");
                  const overlay = document.getElementById("overlay");
                  const top = nav
                      ? Math.round(nav.getBoundingClientRect().bottom) + 2
                      : 147;

                  subcategoriesContainer.style.left = "0px";
                  subcategoriesContainer.style.width = "100%";
                  subcategoriesContainer.style.maxWidth = "100%";
                  subcategoriesContainer.style.marginTop = "0";
                  subcategoriesContainer.style.top = top + "px";

                  if (overlay) {
                      overlay.style.top = top + "px";
                  }
              }



                function showSubcategories() {
                    positionSubcategoriesContainer();
                    document.getElementById("overlay").style.display = "block";
                    document.querySelector(".subcategories-container").style.display = "block";
                }

                function hideSubcategories() {
                    document.getElementById("overlay").style.display = "none";
                    document.querySelector(".subcategories-container").style.display = "none";
                }

                // Exemple : fermer en cliquant sur l’overlay
                document.getElementById("overlay").addEventListener("click", hideSubcategories);

          


              categories.forEach(category => {
                  const showSubcategories = function (e) {
                      e.preventDefault();
                      const categoryId = this.getAttribute("data-id");
                      fetch(`/categories/${categoryId}/subcategories`)
                          .then(response => response.json())
                          .then(data => {
                              subcategoriesDiv.innerHTML = "";
                              if (data.length > 0) {
                                  data.forEach(subcategory => {
                                      const subcategoryBtn = document.createElement("button");
                                      subcategoryBtn.classList.add("subcategory-item");
                                      subcategoryBtn.textContent = subcategory.nom;
                                      subcategoryBtn.setAttribute("data-id", subcategory.id);
                                      subcategoriesDiv.appendChild(subcategoryBtn);
          
                                      subcategoryBtn.addEventListener("click", function () {
                                          window.location.href = `/articlesParCategorie?subcategory=${subcategory.id}`;
                                      });
                                  });
                              } else {
                                  const noSub = document.createElement("p");
                                  noSub.textContent = "Aucune sous-catégorie.";
                                  noSub.style.margin = "0";
                                  noSub.style.padding = "8px 16px";
                                  subcategoriesDiv.appendChild(noSub);
                              }
                              positionSubcategoriesContainer();
                              subcategoriesContainer.style.display = "block";
                              document.getElementById("overlay").style.display = "block"; // ✅ affiche overlay
                          });
                  };
          
                  if (isMobile) {
                      category.addEventListener("click", showSubcategories);
                  } else {
                      category.addEventListener("mouseenter", showSubcategories);
                  }
              });
          
              // Cacher les sous-catégories quand on quitte le container (desktop)
              if (!isMobile) {
                  subcategoriesContainer.addEventListener("mouseleave", function () {
                      subcategoriesContainer.style.display = "none";
                      document.getElementById("overlay").style.display = "none"; // ✅ cache overlay aussi
                  });
                  // Cacher aussi quand on quitte la catégorie
                  categories.forEach(category => {
                      category.addEventListener("mouseleave", function () {
                          setTimeout(() => {
                              if (!subcategoriesContainer.matches(':hover')) {
                                  subcategoriesContainer.style.display = "none";
                                  document.getElementById("overlay").style.display = "none"; // ✅ cache overlay aussi
                              }
                          }, 150);
                      });
                  });
              } else {
                  // Sur mobile, cacher au second clic/tap hors d'une catégorie
                  document.body.addEventListener("click", function (e) {
                      if (!e.target.classList.contains("categories-item-navigation") &&
                          !e.target.classList.contains("subcategory-item")) {
                          subcategoriesContainer.style.display = "none";
                          document.getElementById("overlay").style.display = "none"; // ✅ cache overlay aussi
                      }
                  });
              }
          });
        </script>
      </div>
      
  </nav>

  <!-- script pour le scroll horizontal-->
  <!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
      const categoriesContainer = document.querySelector(".categories");
      const leftButton = document.querySelector(".scroll-button.left");
      const rightButton = document.querySelector(".scroll-button.right");
      const scrollAmount = 150;

      function checkScroll() {
        leftButton.style.display = categoriesContainer.scrollLeft > 0 ? "block" : "none";
        rightButton.style.display = categoriesContainer.scrollLeft + categoriesContainer.clientWidth < categoriesContainer.scrollWidth ? "block" : "none";
      }

      leftButton.addEventListener("click", function () {
        categoriesContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
      });

      rightButton.addEventListener("click", function () {
        categoriesContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
      });

      categoriesContainer.addEventListener("scroll", checkScroll);
      checkScroll(); 
    });
  </script> -->


  <!-- script pour les sous categories -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const categories = document.querySelectorAll(".categories-item");
      const subcategoriesContainer = document.querySelector(".subcategories-container");
      const subcategoriesDiv = document.querySelector(".subcategories");

      categories.forEach(category => {
        category.addEventListener("mouseenter", function () {
          const categoryId = this.getAttribute("data-id");

          // Appeler une route Laravel pour récupérer les sous-catégories
          fetch(`/categories/${categoryId}/subcategories`)
            .then(response => response.json())
            .then(data => {
              // Vider les sous-catégories précédentes
              subcategoriesDiv.innerHTML = "";

            if (data.length > 0) {
              // Ajouter les sous-catégories
              data.forEach(subcategory => {
                const subcategoryButton = document.createElement("button");
                subcategoryButton.classList.add("subcategory-item");
                subcategoryButton.textContent = subcategory.nom;
                subcategoryButton.setAttribute("data-id", subcategory.id); // Ajouter l'ID de la sous-catégorie
                subcategoriesDiv.appendChild(subcategoryButton);

                // Ajouter un événement de clic pour filtrer
                subcategoryButton.addEventListener("click", function () {
                              const subcategoryId = this.getAttribute("data-id");
                              // Rediriger vers une route Laravel avec le filtre
                              window.location.href = `/articlesParCategorie?subcategory=${subcategoryId}`;
                          });
              });
            } else {
              // Si aucune sous-catégorie n'est trouvée, afficher un message
              const noSubcategoriesMessage = document.createElement("p");
              noSubcategoriesMessage.textContent = "Aucune sous-catégorie disponible.";
              noSubcategoriesMessage.style.color = "#555"; // Couleur grise
              noSubcategoriesMessage.style.fontSize = "0.9rem"; 
              subcategoriesDiv.appendChild(noSubcategoriesMessage);
            }

              // Afficher le conteneur des sous-catégories
              subcategoriesContainer.style.display = "block";
            });
        });
      });

      // Cacher les sous-catégories lorsque la souris quitte la zone
      subcategoriesContainer.addEventListener("mouseleave", function () {
        subcategoriesContainer.style.display = "none";
      });
    });
  </script> 

<style>
  .categories-container-navigation {
    overflow-x: auto;               /* active le scroll horizontal */
    scroll-behavior: smooth;        /* rend le scroll doux */
    -webkit-overflow-scrolling: touch;
  }

  .categories-navigation {
    display: flex;
    flex-wrap: nowrap;              /* empêche le retour à la ligne */
    gap: 10px;
  }

  .categories-item-navigation {
    flex: 0 0 auto;                 /* NE PAS rétrécir -> garde l’overflow */
    /* optionnel : donner une largeur mini pour garantir l’overflow */
    /* min-width: 120px; */
  }
</style>



<style>
  .categories-hint {
    position: absolute;
    right: 10px;
    bottom: 5px;
    width: 40px;
    height: 40px;
    opacity: 0.8;
    animation: swipeAnim 1.5s infinite;
    pointer-events: none; /* la main n'interfère pas avec le scroll */
    z-index: 999;
  }

  @keyframes swipeAnim {
    0% { transform: translateX(0); opacity: 0.8; }
    50% { transform: translateX(-20px); opacity: 0.5; }
    100% { transform: translateX(0); opacity: 0.8; }
  }
</style>

<script>
  window.addEventListener('load', function () {
    const container = document.getElementById('categoriesScrollAuto');
    
    if (!container) return;

    // Si pas assez de contenu, on ne fait rien
    if (container.scrollWidth <= container.clientWidth + 2) return;


    let direction = 1;    // 1 = droite, -1 = gauche  
    const pxPerTick = 10;   // vitesse du scroll ou nombre de scroll a augmenter (plus grand = plus rapide)
    const tickMs = 10;     // intervalle (~60FPS)
    let intervalId = null;
    let paused = false;
    let resumeTimeout = null;

    function startAutoScroll() {
      if (intervalId) return;
      intervalId = setInterval(() => {
        if (!paused) {
          container.scrollLeft += direction * pxPerTick;

          // Inverse au bord
          if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 2) direction = -1;
          else if (container.scrollLeft <= 0) direction = 1;
        }
      }, tickMs);
    }

    function stopAutoScroll() {
      if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
      }
    }

    function pauseScrollTemporarily() {
      paused = true;
      clearTimeout(resumeTimeout);
      // Reprend après 5 secondes d'inactivité
      resumeTimeout = setTimeout(() => {
        paused = false;
      }, 5000);
    }

    function userInteracted() {
      pauseScrollTemporarily();
    }

    // Listeners pour pause temporaire
    container.addEventListener('pointerdown', userInteracted, { passive: true });
    container.addEventListener('touchstart', userInteracted, { passive: true });
    container.addEventListener('wheel', userInteracted, { passive: true });
    container.addEventListener('scroll', userInteracted, { passive: true });

    startAutoScroll();
  });
</script>

<script>
  window.addEventListener('load', function () {
    const hint = document.getElementById('categoriesHint');
    if (!hint) return;

    // Montre la main
    hint.style.display = 'block';

    // La fait disparaître après 10 secondes
    setTimeout(() => {
      hint.style.display = 'none';
    }, 10000); // 10000 ms = 10 secondes
  });
</script>


</body>
</html>
