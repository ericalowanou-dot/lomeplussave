@extends('layouts.app2')

@section('title', "Lome+ – Site de vente et achat togolais | Marketplace")

@section('meta')
    <meta name="description" content="Lome+ est un site de vente et achat togolais. Marketplace simple, accessible et sécurisée au Togo. Découvrez des milliers d'annonces.">
@endsection

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
            <i class="bi bi-youtube" aria-hidden="true"></i>
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
            const filterBtn = document.getElementById("openFilter");

            if (st > lastScrollTop) {
                // scroll vers le bas → cacher les boutons de publicité, montrer le bouton filtre
                pub.classList.add("disparaitre");
                if (filterBtn) {
                    filterBtn.style.display = "inline-flex";
                }
            } else {
                // scroll vers le haut → montrer les boutons de publicité, cacher le bouton filtre
                pub.classList.remove("disparaitre");
                if (filterBtn) {
                    filterBtn.style.display = "none";
                }
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
            width: 100%;
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
            /*background: linear-gradient(135deg, #f7971e, #ffd200); /* orange dégradé */
             background: linear-gradient(135deg, #4a5f7a 0%, #5a6f8a 100%); /* gris éclairci */
        }

        /* Bouton WhatsApp */
        a.droite[href*="whatsapp"] {
            /*background: linear-gradient(135deg, #25D366, #128C7E); /* vert WhatsApp */
             background: linear-gradient(135deg, #4a5f7a 0%, #5a6f8a 100%); /* gris éclairci */
        }

        /* Bouton YouTube */
        a.droite[href*="youtube"] {
            /*background: linear-gradient(135deg, #FF0000, #9d0303ff); /* rouge YouTube */
             background: linear-gradient(135deg, #4a5f7a 0%, #5a6f8a 100%); /* gris éclairci */
        }

        .droite i.bi-youtube {
            font-size: 1.1rem;
            color: inherit;
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

    <!-- script pour le scroll automatique des articles horizontaux (.scroll-container peut exister sur articles, search, etc.) -->
    <script>
        window.addEventListener("load", function () {
            const container = document.querySelector(".scroll-container");

            if (container) {
                function animateScroll() {
                    container.scrollBy({ left: 50, behavior: "smooth" });
                    setTimeout(() => {
                        container.scrollBy({ left: -50, behavior: "smooth" });
                    }, 3000);
                }

                animateScroll();
                setInterval(animateScroll, 15000);
            }
        });
    </script>

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

    @php
        $villesList = [
            'Lomé', 'Sokodé', 'Kara', 'Kpalimé', 'Atakpamé', 'Dapaong', 'Tsévié', 'Aného', 'Mango', 'Notsé',
            'Bafilo', 'Bassar', 'Blitta', 'Tchamba', 'Vogan', 'Badou', 'Afagnan', 'Tabligbo', 'Amlamé',
            'Sotouboua', 'Kétao', 'Niamtougou', 'Kanté', 'Tchitchao', 'Kabou', 'Hahotoé', 'Kouvé',
            'Agbodrafo', 'Wahala', 'Kpélé', 'Amou-Oblo', 'Sanguéra', 'Djarkpanga', 'Tandjouaré', 'Biankouri',
            'Nyékonakpoé', 'Avedji', 'Totsi', 'Adidogomé', 'Kégué', 'Ségbé', 'Klikamé', 'Agou-Gadzépé',
            'Gando', 'Elavagnon', 'Alédjo', 'Kamina', 'Kambolé',
        ];
    @endphp

    <!-- Modal du filtre (toujours disponible) -->
    <div id="filterModal" class="filter-modal" aria-hidden="true">
        <div class="filter-modal-panel" role="dialog" aria-modal="true" aria-labelledby="filterModalTitle">
            <button type="button" class="filter-modal-close close-filter" aria-label="Fermer">
                <i class="bi bi-x"></i>
            </button>
            <div class="filter-modal-header">
                <div class="filter-modal-header-icon">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h5 class="filter-modal-title" id="filterModalTitle">Filtrer les articles</h5>
                    <p class="filter-modal-subtitle">Affinez votre recherche selon vos critères prioritaires.</p>
                </div>
            </div>
            <form id="filterForm" action="{{ route('articles.index') }}" method="GET">
                <div class="filter-modal-grid">
                    <div class="filter-field">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <select name="categorie" id="categorie" class="form-control" onchange="loadSousCategories(this.value)">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $categorie)
                                <option value="{{ $categorie->id }}" data-sous-categories='@json($categorie->sousCategories)' {{ request('categorie') == $categorie->id ? 'selected' : '' }}>{{ $categorie->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="sous_categorie" class="form-label">Sous-catégorie</label>
                        <select name="sous_categorie" id="sous_categorie" class="form-control">
                            <option value="">Toutes les sous-catégories</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="prix_min" class="form-label">Prix minimum</label>
                        <div class="input-with-prefix">
                            <span>CFA</span>
                            <input type="number" name="prix_min" id="prix_min" class="form-control" placeholder="0" value="{{ request('prix_min') }}" min="0">
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="prix_max" class="form-label">Prix maximum</label>
                        <div class="input-with-prefix">
                            <span>CFA</span>
                            <input type="number" name="prix_max" id="prix_max" class="form-control" placeholder="100 000" value="{{ request('prix_max') }}" min="0">
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="ville" class="form-label">Ville</label>
                        <select name="ville" id="ville" class="form-control">
                            <option value="">Toutes les villes</option>
                            @foreach($villesList as $ville)
                                <option value="{{ $ville }}" {{ request('ville') == $ville ? 'selected' : '' }}>{{ $ville }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-modal-toggles">
                    <label class="filter-toggle">
                        <input type="checkbox" name="pro_only" value="1" {{ request('pro_only') ? 'checked' : '' }}>
                        <span class="filter-toggle-switch"></span>
                        <span class="filter-toggle-label">Afficher uniquement les annonces Pro</span>
                    </label>

                    <label class="filter-toggle">
                        <input type="checkbox" name="livraison_only" value="1" {{ request('livraison_only') ? 'checked' : '' }}>
                        <span class="filter-toggle-switch"></span>
                        <span class="filter-toggle-label">Livraison disponible</span>
                    </label>
                </div>

                <div class="filter-modal-actions">
                    <button type="button" class="btn-reset" id="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Réinitialiser
                    </button>
                    <button type="submit" class="btn-apply">
                        <i class="bi bi-check2"></i>
                        Appliquer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CSS du modal de filtre -->
<style>
        .filter-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: none;
        align-items: center;
        justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 1600;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .filter-modal.show {
            display: flex;
        }

        @media (max-width: 576px) {
            .filter-modal {
                padding: 12px;
                align-items: center;
            }
            
            .filter-modal-panel {
                margin: auto;
                max-height: 85vh;
                overflow-y: auto;
            }
        }

        @supports (-webkit-touch-callout: none) {
    .filter-modal {
                height: 100%;
                height: -webkit-fill-available;
            }
    }

    body.modal-open {
        overflow: hidden;
    }

    .filter-modal-panel {
        position: relative;
            width: min(480px, 95%);
        background: linear-gradient(145deg, #ffffff 0%, #f8fafd 100%);
            border-radius: 16px;
            padding: 20px 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.2);
        animation: filterModalFade 0.28s ease;
    }

        @media (min-width: 576px) {
            .filter-modal-panel {
                border-radius: 20px;
                padding: 28px 24px;
            }
    }

    .filter-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        background: rgba(244, 244, 246, 0.9);
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1rem;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.25);
        cursor: pointer;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .filter-modal-close:hover {
        transform: rotate(90deg);
        color: #ef4444;
    }

    .filter-modal-header {
        display: flex;
        align-items: center;
            gap: 10px;
            margin-bottom: 14px;
    }

    .filter-modal-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        background: linear-gradient(135deg, rgba(255, 146, 72, 0.18), rgba(255, 93, 98, 0.18));
        color: #ff5d62;
            font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
            flex-shrink: 0;
    }

    .filter-modal-title {
        margin: 0;
        font-weight: 700;
            font-size: 1rem;
        color: #1f2937;
    }

    .filter-modal-subtitle {
        margin: 2px 0 0;
            font-size: 0.78rem;
        color: #6b7280;
            display: none;
        }

        @media (min-width: 576px) {
            .filter-modal-header {
                gap: 16px;
                margin-bottom: 20px;
            }
            .filter-modal-header-icon {
                width: 52px;
                height: 52px;
                border-radius: 16px;
                font-size: 1.35rem;
            }
            .filter-modal-title {
                font-size: 1.15rem;
            }
            .filter-modal-subtitle {
                font-size: 0.9rem;
                display: block;
            }
    }

    .filter-modal-grid {
        display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
    }

    @media (min-width: 576px) {
        .filter-modal-grid {
                gap: 18px;
        }
    }

    .filter-field {
        display: flex;
        flex-direction: column;
            gap: 6px;
    }

    .filter-field .form-label {
            font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0;
    }

    .filter-field .form-control {
        width: 100%;
            border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 8px 10px;
            font-size: 0.82rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-field .form-control:focus {
        outline: none;
        border-color: rgba(255, 110, 48, 0.7);
        box-shadow: 0 0 0 3px rgba(255, 110, 48, 0.15);
    }

        @media (min-width: 576px) {
            .filter-field {
                gap: 8px;
            }
            .filter-field .form-label {
                font-size: 0.85rem;
            }
            .filter-field .form-control {
                border-radius: 12px;
                padding: 10px 14px;
                font-size: 0.9rem;
            }
    }

    .input-with-prefix {
        display: flex;
        align-items: center;
            border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: #fff;
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .input-with-prefix:focus-within {
        border-color: rgba(255, 110, 48, 0.7);
        box-shadow: 0 0 0 3px rgba(255, 110, 48, 0.12);
    }

    .input-with-prefix span {
            padding: 8px 8px;
            background: rgba(255, 153, 0, 0.1);
            color: #FF9900;
            font-weight: 700;
            font-size: 0.75rem;
    }

    .input-with-prefix .form-control {
        border: none;
        border-radius: 0;
        box-shadow: none;
            padding: 8px 6px;
            font-size: 0.82rem;
        }

        @media (min-width: 576px) {
            .input-with-prefix {
                border-radius: 12px;
            }
            .input-with-prefix span {
                padding: 10px 12px;
                font-size: 0.82rem;
            }
            .input-with-prefix .form-control {
                padding: 10px 14px;
                font-size: 0.9rem;
            }
    }

    .filter-modal-toggles {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: rgba(248, 250, 252, 0.9);
        border-radius: 14px;
        padding: 14px 16px;
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .filter-toggle {
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        font-size: 0.9rem;
        color: #1f2937;
        margin: 0;
    }

    .filter-toggle input {
        display: none;
    }

    .filter-toggle-switch {
        position: relative;
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: rgba(209, 213, 219, 0.85);
        transition: background 0.2s ease;
    }

    .filter-toggle-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease;
    }

    .filter-toggle input:checked + .filter-toggle-switch {
        background: linear-gradient(135deg, #34d399, #059669);
    }

    .filter-toggle input:checked + .filter-toggle-switch::after {
        transform: translateX(20px);
    }

    .filter-toggle-label {
        font-weight: 600;
        color: #374151;
    }

    @media (max-width: 576px) {
        .filter-modal-toggles {
            padding: 12px;
        }
    }

    .filter-modal-actions {
        margin-top: 26px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .filter-modal-actions button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 18px;
        border: none;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .btn-reset {
        background: rgba(148, 163, 184, 0.14);
        color: #475569;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.35);
    }

    .btn-reset:hover {
        background: rgba(148, 163, 184, 0.24);
        transform: translateY(-1px);
    }

    .btn-apply {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.35);
    }

    .btn-apply:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 36px rgba(37, 99, 235, 0.42);
    }

    .btn-apply:active,
    .btn-reset:active {
        transform: translateY(0);
        box-shadow: none;
    }

    @keyframes filterModalFade {
        0% {
            opacity: 0;
            transform: translateY(14px) scale(0.98);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

    <!-- Script du modal de filtre -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("filterModal");
        const openBtn = document.getElementById("openFilter");
        const closeBtns = document.querySelectorAll(".close-filter");
        const form = document.getElementById("filterForm");
        const resetBtn = document.getElementById("resetFilters");

            if (!modal || !form) {
            return;
        }

        const openModal = () => {
            modal.classList.add("show");
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("modal-open");
        };

        const closeModal = () => {
            modal.classList.remove("show");
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("modal-open");
        };

            // Fonction globale pour ouvrir le modal (utilisée par le bouton "Modifier les filtres")
            window.openFilterModal = openModal;

            // Bouton d'ouverture (peut ne pas exister sur la page "aucun résultat")
            if (openBtn) {
        openBtn.addEventListener("click", openModal);
            }
            
        closeBtns.forEach(btn => btn.addEventListener("click", closeModal));

        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        window.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && modal.classList.contains("show")) {
                closeModal();
            }
        });

        resetBtn?.addEventListener("click", () => {
            form.reset();
                const sousCategorieSelect = document.getElementById('sous_categorie');
                if (sousCategorieSelect) {
                    sousCategorieSelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
                }
            closeModal();
            window.location.href = "{{ route('articles.index') }}";
        });

        form.addEventListener("submit", function () {
            closeModal();
        });

            // Charger les sous-catégories au chargement si une catégorie est déjà sélectionnée
            const categorieSelect = document.getElementById('categorie');
            if (categorieSelect && categorieSelect.value) {
                loadSousCategories(categorieSelect.value);
            }
        });

        // Fonction pour charger les sous-catégories
        function loadSousCategories(categorieId) {
            const sousCategorieSelect = document.getElementById('sous_categorie');
            const categorieSelect = document.getElementById('categorie');
            const selectedSousCategorie = '{{ request('sous_categorie') }}';
            
            sousCategorieSelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
            
            if (!categorieId) {
            return;
        }

            const selectedOption = categorieSelect.querySelector(`option[value="${categorieId}"]`);
            if (selectedOption) {
                try {
                    const sousCategories = JSON.parse(selectedOption.getAttribute('data-sous-categories') || '[]');
                    
                    sousCategories.forEach(function(sc) {
                        const option = document.createElement('option');
                        option.value = sc.id;
                        option.textContent = sc.nom;
                        if (selectedSousCategorie == sc.id) {
                            option.selected = true;
                        }
                        sousCategorieSelect.appendChild(option);
                    });
                } catch (e) {
                    console.error('Erreur lors du parsing des sous-catégories:', e);
                }
            }
        }
    </script>

    @if($articles->isEmpty())
    <!-- Masquer les boutons flottants quand aucun résultat -->
    <style>
        .gauche, .droite, #megaphone-button {
            display: none !important;
        }
    </style>
    <!-- Page Aucun résultat -->
    <div class="no-results-container">
        <div class="no-results-content">
            <div class="no-results-icon">
                <i class="bi bi-search"></i>
            </div>
            <h2 class="no-results-title">Aucun article trouvé</h2>
            <p class="no-results-text">
                Nous n'avons trouvé aucun article correspondant à vos critères.
                <br>Essayez de modifier vos filtres ou explorez d'autres options.
            </p>
            <div class="no-results-actions">
                <button type="button" class="btn-retry-filter" onclick="window.openFilterModal();">
                    <i class="bi bi-funnel"></i>
                    Modifier les filtres
                </button>
                <a href="{{ route('articles.index') }}" class="btn-reset-filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Réinitialiser
                </a>
                <a href="{{ url('/') }}" class="btn-go-home">
                    <i class="bi bi-house"></i>
                    Retour à l'accueil
                </a>
            </div>
            <div class="no-results-suggestions">
                <p class="suggestions-title">💡 Suggestions :</p>
                <ul>
                    <li>Vérifiez l'orthographe de votre recherche</li>
                    <li>Utilisez des termes plus généraux</li>
                    <li>Réduisez le nombre de filtres actifs</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .no-results-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50vh;
            padding: 30px 16px;
            margin-top: 140px;
        }

        .no-results-content {
            text-align: center;
            max-width: 450px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafd 100%);
            padding: 32px 24px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .no-results-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #94a3b8;
        }

        .no-results-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .no-results-text {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .no-results-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 24px;
        }

        .no-results-actions button,
        .no-results-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-retry-filter {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .btn-retry-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.45);
        }

        .btn-reset-filters {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-reset-filters:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-go-home {
            background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(255, 153, 0, 0.3);
        }

        .btn-go-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 153, 0, 0.4);
        }

        .no-results-suggestions {
            text-align: left;
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .suggestions-title {
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .no-results-suggestions ul {
            margin: 0;
            padding-left: 18px;
            color: #64748b;
            font-size: 0.8rem;
        }

        .no-results-suggestions li {
            margin-bottom: 3px;
        }

        @media (min-width: 576px) {
            .no-results-actions {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
    @else
    {{-- articles horizontaux --}}
    {{-- <div class="scroll-container" id="horizontal-articles">
        <div class="d-flex flex-nowrap gap-3">
            @foreach($articles as $article)
                <div class="article-container" style="z-index: 1;">
                    <div class="article-container-secondaire">
                        <a href="{{ route('article.details', ['id' => $article->id]) }}" style="text-decoration: none; color: inherit; border-bottom: 1px solid;">
                            <img class="article-container-image" 
                                 src="{{ $article->photo_url }}" 
                                 alt="image de l'article"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        </a>
                        <div class="card-body-horizontal">
                            <div class="article-container-text">
                                <div class="prix-article">
                                    <span>{{ intval($article->prix_ht) }} CFA</span>    
                                </div>
                                <div class="titre-article" style="margin-bottom: 25px;">
                                    {{ $article->titre }}
                                </div>

                                <hr style="border-top: 3px solid #000000;  width: 100%; margin-bottom: 5px; margin-top: 5px;">
                                                        
                                @if($article->isBoosted())
                                    <div class="badge bg-warning text-dark" style="font-size: 10px; border-radius: 4px; padding: 2px 8px; display:inline-block; margin-bottom:4px; text-transform: uppercase; letter-spacing: 0.5px;">Pro</div>
                                @else
                                    @if($article->user)
                                        <div class="info-utilisateur">
                                            <img class="photo-profil-utilisateur" 
                                                 src="{{ $article->user->getProfilPhotoUrl() }}" 
                                                 alt="Profil de {{ $article->user->name }}"
                                                 loading="lazy"
                                                 onerror="this.src='{{ asset('images/user_default.png') }}';">
                                            <p class="nom-utilisateur">{{ $article->user->name ?? 'nom non spécifiée' }}</p>
                                        </div>
                                    @endif

                                    @if($article->user && $article->user->estCertifie())
                                        <div class="certification-horizontal">
                                            <img src="{{ asset('images/certifier.png') }}" alt="Certifié" class="certification-logo-horizontal">
                                            <span class="certification-text-horizontal">Vérifié</span>
                                        </div>
                                    @endif
                                @endif
                                            
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div> --}}

    
    <!-- Bouton de filtre -->
    <div class="filter-btn-wrapper">
        <button id="openFilter" class="filter-btn" type="button">
            <i class="bi bi-funnel"></i>
            <span>Filtrer</span>
        </button>
    </div>

  <!-- CSS et Script du bouton filtre -->
<style>
    /* Conteneur du bouton filtre */
    .filter-btn-wrapper {
        position: relative;
        margin-top: 15px;
        height: 34px; /* Hauteur fixe pour éviter le saut de contenu */
    }

    /* Bouton filtre - style professionnel */
    .filter-btn {
        position: absolute;
        z-index: 99;
        display: none; /* Caché par défaut, apparaît seulement quand les boutons de publicité disparaissent */
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #3498db;
        border: 1px solid #fff;
        color: #fff;
        cursor: pointer;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transform-origin: right center;
    }

    /* Quand le bouton est fixé */
    .filter-btn.is-fixed {
        position: fixed;
        top: 180px;
        right: 10px;
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        animation: filterBtnSlideIn 0.7s ease-out forwards;
    }

    /* Animation d'entrée - simple et fluide */
    @keyframes filterBtnSlideIn {
        from {
            opacity: 0;
            transform: translateX(40px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Animation de sortie - simple et fluide */
    .filter-btn.is-unfixing {
        position: fixed;
        top: 180px;
        right: 10px;
        animation: filterBtnSlideOut 0.7s ease-in forwards;
    }

    @keyframes filterBtnSlideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(40px);
        }
    }

    .filter-btn:hover {
        background: #2980b9;
        box-shadow: 0 6px 16px rgba(0,0,0,0.35);
    }

    .filter-btn:active {
        transform: scale(0.98);
    }
</style>

<!-- Script pour fixer le bouton en dessous des catégories -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterBtn = document.getElementById("openFilter");
        if (!filterBtn) return;

        // Cacher le bouton filtre au chargement (les boutons de publicité sont visibles)
        filterBtn.style.display = "none";

        // Position initiale du bouton
        let btnOriginalTop = filterBtn.getBoundingClientRect().top + window.scrollY;
        const fixedTop = 180;
        let isCurrentlyFixed = false;
        let isAnimating = false;
        let animationTimeout = null;

        // Recalculer la position originale après le chargement complet
        window.addEventListener('load', function() {
            btnOriginalTop = filterBtn.getBoundingClientRect().top + window.scrollY;
        });

        function handleScroll() {
            const scrollY = window.scrollY;
            const shouldBeFixed = scrollY + fixedTop >= btnOriginalTop;
            const pub = document.getElementById("hautPublicite");
            const pubDisparait = pub && pub.classList.contains("disparaitre");

            // Le bouton filtre apparaît seulement si les boutons de publicité ont disparu
            if (pubDisparait) {
                // Boutons de publicité cachés → montrer le bouton filtre
                if (filterBtn.style.display === "none") {
                    filterBtn.style.display = "inline-flex";
                }
                
                // Passage à fixé (entrée)
                if (shouldBeFixed && !isCurrentlyFixed && !isAnimating) {
                    isCurrentlyFixed = true;
                    clearTimeout(animationTimeout);
                    filterBtn.classList.remove("is-unfixing");
                    filterBtn.classList.add("is-fixed");
                }
                // Retour à normal (sortie avec animation fluide)
                else if (!shouldBeFixed && isCurrentlyFixed && !isAnimating) {
                    isCurrentlyFixed = false;
                    isAnimating = true;
                    
                    // Garder is-fixed pendant l'animation de sortie
                    filterBtn.classList.add("is-unfixing");
                    
                    // Attendre la fin de l'animation (700ms) avant de retirer les classes
                    clearTimeout(animationTimeout);
                    animationTimeout = setTimeout(function() {
                        filterBtn.classList.remove("is-fixed");
                        filterBtn.classList.remove("is-unfixing");
                        isAnimating = false;
                    }, 700);
                }
            } else {
                // Boutons de publicité visibles → cacher le bouton filtre
                filterBtn.style.display = "none";
                filterBtn.classList.remove("is-fixed");
                filterBtn.classList.remove("is-unfixing");
                isCurrentlyFixed = false;
            }
        }

        window.addEventListener("scroll", handleScroll, { passive: true });
        
        // Vérification initiale
        handleScroll();
    });
</script>

            
            {{-- <h6 class="titre-nouveaute" style="margin-top: 90px; margin-bottom: 20px; background-color: none;">Les nouveautés </h6> --}}




        <div class="container" style="margin-top: 160px; width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
            <!-- Publicités en haut de page -->
            @include('partials.publicites', ['position' => 'header'])
            @include('partials.publicites', ['position' => 'homepage_top'])

                <!-- <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 articles">  -->
                    <div id="articles-results" data-context="articles">
        @include('partials.articles-list', ['articles' => $articles])
                
            </div>

            <!-- Publicités en bas de page -->
            @include('partials.publicites', ['position' => 'homepage_bottom'])
  
  
        
        </div>



    </main>
    @endif

    @if(!$articles->isEmpty() && $articles->hasPages())
        <!-- Pagination classique Laravel -->
        <div class="pagination-wrapper">
            {{ $articles->links() }}
        </div>

        <style>
            /* Conteneur de pagination */
            .pagination-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 40px 0;
                padding: 0 16px;
            }

            /* Styles pour la pagination Laravel */
            .pagination {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                gap: 8px;
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .pagination li {
                display: inline-block;
                margin: 0;
            }

            /* Liens de pagination */
            .pagination a,
            .pagination span {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 40px;
                height: 40px;
                padding: 8px 12px;
                font-size: 0.95rem;
                font-weight: 600;
                color: #475569;
                text-decoration: none;
                border-radius: 10px;
                transition: all 0.3s ease;
                background: #ffffff;
                border: 2px solid #e2e8f0;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            /* Liens cliquables */
            .pagination a:hover {
                background: linear-gradient(135deg, #4a5f7a 0%, #5a6f8a 100%);
                color: #ffffff;
                border-color: #4a5f7a;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(74, 95, 122, 0.3);
            }

            /* Page active */
            .pagination .active span {
                background: linear-gradient(135deg, #4a5f7a 0%, #5a6f8a 100%);
                color: #ffffff;
                border-color: #4a5f7a;
                box-shadow: 0 4px 12px rgba(74, 95, 122, 0.3);
                cursor: default;
            }

            /* Liens désactivés (première/dernière page) */
            .pagination .disabled span {
                background: #f1f5f9;
                color: #94a3b8;
                border-color: #e2e8f0;
                cursor: not-allowed;
                opacity: 0.6;
            }

            /* Flèches précédent/suivant */
            .pagination .page-link {
                font-weight: 700;
            }

            /* Responsive pour mobile */
            @media (max-width: 640px) {
                .pagination-wrapper {
                    margin: 30px 0;
                    padding: 0 12px;
                }

                .pagination {
                    gap: 6px;
                }

                .pagination a,
                .pagination span {
                    min-width: 36px;
                    height: 36px;
                    padding: 6px 10px;
                    font-size: 0.875rem;
                }

                /* Masquer les numéros de page sur très petit écran, garder seulement prev/next */
                .pagination li:not(.disabled):not(:first-child):not(:last-child) {
                    display: none;
                }

                /* Afficher quelques pages autour de la page active */
                .pagination li.active,
                .pagination li.active + li,
                .pagination li.active + li + li,
                .pagination li.active - li,
                .pagination li.active - li - li {
                    display: inline-block;
                }
            }

            /* Pour les écrans moyens */
            @media (min-width: 641px) and (max-width: 1024px) {
                .pagination a,
                .pagination span {
                    min-width: 38px;
                    height: 38px;
                    padding: 7px 11px;
                }
            }

            /* Animation au survol */
            .pagination a:active {
                transform: translateY(0);
                box-shadow: 0 2px 4px rgba(74, 95, 122, 0.2);
            }
        </style>
    @endif

            <footer class="text-body-secondary py-5">
            <div class="container">
                <!-- Footer content si nécessaire -->
            </div>
            </footer>
            
            <!-- Bouton Scroll to Top -->
            @include('components.scroll-to-top')
            <script src="{{asset('js/bootstrap.bundle.min.js')}}" defer></script>




@endsection


