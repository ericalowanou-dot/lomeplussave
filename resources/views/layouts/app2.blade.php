<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lome+')</title>
    
    <!-- Meta tags Open Graph pour le partage -->
    @yield('meta')
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="16x16" href="{{ asset('images/logo_icon.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/articles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/article-horizontale.css') }}">
    <link rel="stylesheet" href="{{asset('css/detail_article.css')}}"> 
    <link rel="stylesheet" href="{{asset('css/all.min.css')}}" crossorigin="anonymous" referrerpolicy="no-referrer"> 
    <!-- <link rel="stylesheet" href="{{ asset('css/background-articles.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('css/detail_article.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fix-stability.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-bottom-nav.css') }}">
    
    <!-- Typographie uniforme globale - Doit être chargé après les autres CSS -->
    <style>
        /* Typographie professionnelle uniforme - Override tous les autres styles,
           mais sans casser les polices d'icônes (Font Awesome, etc.) */
        html, body, *:not(.fa):not(.fas):not(.far):not(.fal):not(.fab):not(.bi):not([class^="bi-"]):not([class*=" bi-"]) {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        
        /* Hiérarchie typographique */
        h1, .h1 { font-family: 'Poppins', sans-serif !important; font-weight: 700 !important; }
        h2, .h2 { font-family: 'Poppins', sans-serif !important; font-weight: 600 !important; }
        h3, .h3 { font-family: 'Poppins', sans-serif !important; font-weight: 600 !important; }
        h4, .h4 { font-family: 'Poppins', sans-serif !important; font-weight: 600 !important; }
        h5, .h5 { font-family: 'Poppins', sans-serif !important; font-weight: 600 !important; }
        h6, .h6 { font-family: 'Poppins', sans-serif !important; font-weight: 600 !important; }
        
        /* Éléments de formulaire */
        input, textarea, select, button, .btn {
            font-family: 'Poppins', sans-serif !important;
        }
        
        /* Navigation et liens */
        nav, a, .nav-link {
            font-family: 'Poppins', sans-serif !important;
        }
        
        /* Cards et contenus */
        .card, .card-title, .card-text {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Rétablir la police correcte pour les icônes Font Awesome */
        .fa, .fas, .far, .fal {
            font-family: "Font Awesome 5 Free" !important;
        }
        .fab {
            font-family: "Font Awesome 5 Brands" !important;
        }
        .bi, [class^="bi-"], [class*=" bi-"] {
            font-family: "bootstrap-icons" !important;
        }
    </style>
    <!-- Police professionnelle uniforme pour tout le site -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

                   



  












        @if(isset($article))
        @php
            // S'assurer que l'URL de l'image est absolue
            $imageUrl = $article->photo_url;
            if (!Str::startsWith($imageUrl, ['http://', 'https://'])) {
                $imageUrl = url($imageUrl);
            }
            // S'assurer que l'URL de la page est absolue
            $pageUrl = Request::url();
            if (!Str::startsWith($pageUrl, ['http://', 'https://'])) {
                $pageUrl = url($pageUrl);
            }
        @endphp
        <meta property="og:title" content="{{ $article->titre }}">
        <meta property="og:description" content="{{ Str::limit(strip_tags($article->description), 200) }}">
        <meta property="og:image" content="{{ $imageUrl }}">
        <meta property="og:image:secure_url" content="{{ $imageUrl }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="{{ $pageUrl }}">
        <meta property="og:type" content="article">
        <meta property="og:site_name" content="Lome Plus">
        <meta property="article:author" content="{{ $article->user->name ?? 'Lome Plus' }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $article->titre }}">
        <meta name="twitter:description" content="{{ Str::limit(strip_tags($article->description), 200) }}">
        <meta name="twitter:image" content="{{ $imageUrl }}">
        <meta name="twitter:image:alt" content="{{ $article->titre }}">
        
        <!-- WhatsApp et autres réseaux sociaux -->
        <meta name="description" content="{{ Str::limit(strip_tags($article->description), 200) }}">
        @else
        <meta property="og:title" content="Lome+ – Site de vente et achat togolais | Marketplace">
        <meta property="og:description" content="Lome+ est une marketplace de vente et d'achat au Togo. Simple, accessible et sécurisé.">
        <meta property="og:url" content="{{ Request::url() }}">
        <meta property="og:type" content="website">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="Lome+ – Site de vente et achat togolais | Marketplace">
        <meta name="twitter:description" content="Lome+ est une marketplace de vente et d'achat au Togo. Simple, accessible et sécurisé.">
        @endif



       


</head>
<body>
    <!-- Page Loader -->
    @include('components.page-loader')

        <!-- Script pour prévenir le débordement horizontal -->
        <script>
            // Prévenir le débordement horizontal immédiatement
            document.addEventListener("DOMContentLoaded", function () {
                // Forcer overflow-x hidden sur html et body
                document.documentElement.style.overflowX = 'hidden';
                document.body.style.overflowX = 'hidden';
                document.documentElement.style.width = '100%';
                document.body.style.width = '100%';
                
                // Vérifier et corriger tout débordement horizontal
                function preventHorizontalScroll() {
                    document.documentElement.style.overflowX = 'hidden';
                    document.body.style.overflowX = 'hidden';
                    
                    // Vérifier si la largeur du body dépasse celle de la fenêtre
                    if (document.body.scrollWidth > window.innerWidth) {
                        document.body.style.maxWidth = window.innerWidth + 'px';
                    }
                }
                
                preventHorizontalScroll();
                window.addEventListener('resize', preventHorizontalScroll);
                window.addEventListener('load', preventHorizontalScroll);
            });
            
            // Correction immédiate même avant DOMContentLoaded
            (function() {
                if (document.documentElement) {
                    document.documentElement.style.overflowX = 'hidden';
                }
                if (document.body) {
                    document.body.style.overflowX = 'hidden';
                }
            })();
        </script>


        <!-- Icônes SVG utilisées dans la page, cachées -->
        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
            <symbol id="check2" viewBox="0 0 16 16">
                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
            </symbol>
            <symbol id="circle-half" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
            </symbol>
            <symbol id="moon-stars-fill" viewBox="0 0 16 16">
                <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
                <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
            </symbol>
            <symbol id="sun-fill" viewBox="0 0 16 16">
                <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
            </symbol>
        </svg>







        
    <div class="main-wrapper d-flex flex-column min-vh-100">
        <!-- Inclusion de l'entête -->
        @include('includes.header')

        <!-- Inclusion de la barre de navigation -->
        @include('includes.navigation')


        <main class="flex-fill">
            @if(isset($header) && $header)
                <div class="container py-4">
                    {{ $header }}
                </div>
            @endif
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>


        <style>
            /* Typographie professionnelle uniforme pour tout le site */
            * {
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            }
            
            /* Correction des problèmes de stabilité et de décalage */
            html {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
                position: relative;
            }
            
            body {
                background-color: #f5f5f5;
                height: 100%;
                margin: 0; 
                padding: 0;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
                position: relative;
                box-sizing: border-box;
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
                font-weight: 400;
                line-height: 1.6;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            
            /* Hiérarchie typographique professionnelle */
            h1, h2, h3, h4, h5, h6 {
                font-family: 'Poppins', sans-serif !important;
                font-weight: 600;
                line-height: 1.3;
                letter-spacing: -0.02em;
            }
            
            h1 {
                font-weight: 700;
                font-size: 2.25rem;
            }
            
            h2 {
                font-weight: 600;
                font-size: 1.875rem;
            }
            
            h3 {
                font-weight: 600;
                font-size: 1.5rem;
            }
            
            h4 {
                font-weight: 600;
                font-size: 1.25rem;
            }
            
            h5 {
                font-weight: 600;
                font-size: 1.125rem;
            }
            
            h6 {
                font-weight: 600;
                font-size: 1rem;
            }
            
            p, span, div, a, button, input, textarea, select, label {
                font-family: 'Poppins', sans-serif !important;
            }
            
            /* Amélioration de la lisibilité */
            p {
                line-height: 1.7;
            }
            
            /* Boutons avec typographie professionnelle */
            button, .btn {
                font-family: 'Poppins', sans-serif !important;
                font-weight: 500;
                letter-spacing: 0.01em;
            }
            
            /* Inputs avec typographie professionnelle */
            input, textarea, select {
                font-family: 'Poppins', sans-serif !important;
                font-weight: 400;
            }
            
            * {
                box-sizing: border-box;
            }
            
            .main-wrapper {
                min-height: 100vh;
                width: 100%;
                max-width: 100%;
                display: flex;
                flex-direction: column;
                overflow-x: hidden;
                position: relative;
            }
            
            main.flex-fill {
                flex: 1 0 auto;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }
            
            footer {
                flex-shrink: 0;
                width: 100%;
                max-width: 100%;
            }
            
            /* Empêcher tout débordement horizontal */
            .container {
                max-width: 100%;
                overflow-x: hidden;
            }
            
            /* Correction pour mobile */
            @media (max-width: 768px) {
                html, body {
                    overflow-x: hidden !important;
                    position: relative;
                    width: 100% !important;
                    max-width: 100% !important;
                }
                
                body > * {
                    max-width: 100%;
                    overflow-x: hidden;
                }
                
                /* Masquer complètement la scrollbar verticale sur mobile */
                html {
                    scrollbar-width: none !important; /* Firefox */
                    -ms-overflow-style: none !important; /* IE/Edge */
                }
                
                html::-webkit-scrollbar {
                    display: none !important; /* Chrome, Safari, Opera */
                    width: 0 !important;
                }
                
                body {
                    scrollbar-width: none !important; /* Firefox */
                    -ms-overflow-style: none !important; /* IE/Edge */
                }
                
                body::-webkit-scrollbar {
                    display: none !important; /* Chrome, Safari, Opera */
                    width: 0 !important;
                }
                
                * {
                    scrollbar-width: none !important; /* Firefox */
                    -ms-overflow-style: none !important; /* IE/Edge */
                }
                
                *::-webkit-scrollbar {
                    display: none !important; /* Chrome, Safari, Opera */
                    width: 0 !important;
                }
            }

        </style>

        <!-- Inclusion du pied de page -->

        @include('includes.footer')
    </div>

    @include('includes.mobile-bottom-nav')

                <!-- Bouton pour déclencher le modal -->
            <!-- <div id="megaphone-button" class="phone-bot-button">
                <img src="{{ asset('images/ajouter.png') }}" alt="Mégaphone"> 
                <span>Publier une annonce</span> 
            </div> -->

            <!-- pour la page des details de l'article  -->
            <div 
                id="megaphone-button" 
                class="phone-bot-button @if(Route::currentRouteName() === 'article.details') details-page @endif @if(Route::currentRouteName() === 'mes_annonces') hidden-on-mes-annonces @endif @if(Route::currentRouteName() === 'about') hidden-on-about @endif">
                <img src="{{ asset('images/ajouter.png') }}" alt="Mégaphone"> 
                <span>Publier une annonce</span> 
            </div>


            
<!-- style du bouton de publication d'annonce -->
<style>
    /* Caché par défaut sur la page détails */
    #megaphone-button.details-page {
        opacity: 0;
        pointer-events: none;
        transform: translateX(-50%) scale(0.9);
        transition: opacity .4s ease, transform .4s ease;
    }

    /* Quand visible (sur page détails, dans la section articles similaires) */
    #megaphone-button.details-page.is-visible {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) scale(1);
    }

    /* Styles propres au bouton */
    #megaphone-button {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ff7b00;
        color: #fff;
        padding: 10px 14px;
        border-radius: 9999px;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.5);
        cursor: pointer;
        overflow: hidden;
        transition: all .35s ease;
        z-index: 100;
    }

    #megaphone-button img {
        width: 22px;
        height: 22px;
        flex: 0 0 auto;
        transition: all .35s ease;
    }

    #megaphone-button span {
        display: inline-block;
        max-width: 160px;
        opacity: 1;
        transform: translateX(0);
        white-space: nowrap;
        transition: opacity .35s ease, max-width .35s ease, transform .35s ease;
    }

    /* État compact pendant le scroll */
    #megaphone-button.is-compact {
        left: 30px;
        padding: 10px;
        border: 1px solid #fff;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.5);
    }

    #megaphone-button.is-compact img {
        width: 30px;
        height: 30px;
        margin-right: -7px;
    }

    #megaphone-button.is-compact span {
        opacity: 0;
        max-width: 0;
        transform: translateX(-10px);
    }

    /* Masquer sur certaines pages */
    #megaphone-button.hidden-on-mes-annonces,
    #megaphone-button.hidden-on-about {
        display: none !important;
    }
</style>

<!-- script d'animation du bouton de publication d'annonce -->
<script>
    (function() {
        const btn = document.getElementById('megaphone-button');
        if (!btn) return;

        let scrollTimer;

        // Vérifier si on est sur la page détails
        const isDetailsPage = btn.classList.contains('details-page');

        // Fonction pour vérifier si on est proche du footer (pour TOUTES les pages)
        function isNearFooter() {
            const footer = document.querySelector('footer');
            if (!footer) return false;
            
            const footerRect = footer.getBoundingClientRect();
            // Masquer le bouton quand le footer est visible (à moins de 100px du bas de l'écran)
            return footerRect.top < window.innerHeight + 100;
        }

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;

            // ⚠️ PRIORITÉ : Masquer près du footer sur TOUTES les pages
            if (isNearFooter()) {
                btn.style.opacity = '0';
                btn.style.pointerEvents = 'none';
                if (isDetailsPage) {
                    btn.classList.remove('is-visible');
                }
                return; // Stop
            } else {
                // Réafficher le bouton quand on s'éloigne du footer
                if (!isDetailsPage) {
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                } else {
                    btn.style.opacity = '';
                    btn.style.pointerEvents = '';
                }
            }

            if (isDetailsPage) {
                // ➡️ Bouton visible UNIQUEMENT dans la section articles similaires
                const articlesSimilaires = document.getElementById('articles-similaires');
                
                if (articlesSimilaires) {
                    const rect = articlesSimilaires.getBoundingClientRect();
                    // Afficher quand la section est visible dans le viewport
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        btn.classList.add('is-visible');
                    } else {
                        btn.classList.remove('is-visible');
                    }
                } else {
                    // Fallback : afficher après 350px si pas de section articles similaires
                    if (scrollY > 350) {
                        btn.classList.add('is-visible');
                    } else {
                        btn.classList.remove('is-visible');
                    }
                }
            }

            // Comportement compact/expand pendant le scroll
            if (!isDetailsPage || btn.classList.contains('is-visible')) {
                btn.classList.add('is-compact');

                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => {
                    btn.classList.remove('is-compact');
                }, 1000);
            }
        }, { passive: true });
    })();
</script>


    
        <!-- Modal Connexion/Inscription -->
        <div id="modal-auth" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 350px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; text-align: center; position: relative;">
                <span class="close-button" id="close-auth-modal" style="position: absolute; top: 8px; right: 16px; font-size: 28px; cursor: pointer;">&times;</span>
                <h5 style="font-weight: bold;">Vous devez être connecté</h5>
                <div style="display: flex; text-align: center; align-items: center; justify-content: center; margin: 20px 0; gap: 10px;">
                    <a href="{{ route('login') }}" class="btn btn-primary w-100">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Inscription</a>
                </div>
            </div>
        </div>
    <!-- script de connexion / inscription si non connecté et ouverture de la page de création d'annonce si connecté --> 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var isAuthenticated = @auth true @else false @endauth;
            var boutonMegaphone = document.getElementById("megaphone-button");
            var modalAuth = document.getElementById("modal-auth");
            var closeAuthModal = document.getElementById("close-auth-modal");
        
            // Vérifier que les éléments existent avant de les utiliser
            if (boutonMegaphone) {
            boutonMegaphone.addEventListener("click", function() {
                if (isAuthenticated) {
                    window.location.href = "{{ route('articles.create') }}";
                    } else if (modalAuth) {
                    modalAuth.style.display = "flex";
                }
            });
            }
        
            if (closeAuthModal && modalAuth) {
            closeAuthModal.addEventListener("click", function() {
                modalAuth.style.display = "none";
            });
            }
        
            // Fermer le modal si on clique en dehors du contenu
            if (modalAuth) {
            window.addEventListener("click", function(event) {
                if (event.target === modalAuth) {
                    modalAuth.style.display = "none";
                }
            });
            }
        });
    </script>
    
    <style>
        .modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>



        <!-- Ajoutez ici vos scripts JS ou autres ressources nécessaires -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


        
          <!-- script des likes - Le code est maintenant dans resources/js/like.js et chargé via Vite via app.js -->


</body>
</html>
