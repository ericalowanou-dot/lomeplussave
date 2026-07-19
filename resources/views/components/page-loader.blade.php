<!-- Page Loader Professionnel -->
<div id="pageLoader" class="page-loader" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #ffffff; z-index: 99999; display: flex; align-items: center; justify-content: center;">
    <div class="loader-content">
        <!-- Logo avec spinner autour -->
        <div class="loader-logo-wrapper">
            <!-- Spinner autour du logo -->
            <div class="spinner-ring spinner-ring-1"></div>
            <div class="spinner-ring spinner-ring-2"></div>
            <div class="spinner-ring spinner-ring-3"></div>
            <!-- Logo au centre -->
            <img src="{{ asset('images/true-logo.png') }}" alt="Lome+" class="loader-logo" onerror="this.style.display='none';">
        </div>
        
        <!-- Texte -->
        <p class="loader-text">Chargement...</p>
    </div>
</div>

<style>
/* Le loader doit être visible immédiatement - IMPORTANT: pas de display:none */
#pageLoader {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: #ffffff !important;
    z-index: 99999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 1 !important;
    visibility: visible !important;
    margin: 0 !important;
    padding: 0 !important;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}

#pageLoader.hidden {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}

.loader-content {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 25px;
}

/* Logo avec spinner autour */
.loader-logo-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: logoFloat 2s ease-in-out infinite;
}

.loader-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 2px 8px rgba(255, 153, 0, 0.2));
}

/* Spinner rings autour du logo */
.spinner-ring {
    position: absolute;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: #FF9900;
    animation: spin 1.5s linear infinite;
}

.spinner-ring-1 {
    width: 100px;
    height: 100px;
    top: 10px;
    left: 10px;
    border-top-color: #FF9900;
    animation-duration: 1.2s;
}

.spinner-ring-2 {
    width: 110px;
    height: 110px;
    top: 5px;
    left: 5px;
    border-top-color: #E68900;
    animation-duration: 1.5s;
    animation-direction: reverse;
}

.spinner-ring-3 {
    width: 120px;
    height: 120px;
    top: 0;
    left: 0;
    border-top-color: #FFB84D;
    animation-duration: 1.8s;
    opacity: 0.7;
}

@keyframes logoFloat {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Texte */
.loader-text {
    color: #666;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Responsive */
@media (max-width: 768px) {
    .loader-logo-wrapper {
        width: 90px;
        height: 90px;
    }
    
    .loader-logo {
        width: 60px;
        height: 60px;
    }
    
    .spinner-ring-1 {
        width: 75px;
        height: 75px;
        top: 7.5px;
        left: 7.5px;
    }
    
    .spinner-ring-2 {
        width: 85px;
        height: 85px;
        top: 2.5px;
        left: 2.5px;
    }
    
    .spinner-ring-3 {
        width: 90px;
        height: 90px;
        top: 0;
        left: 0;
    }
    
    .loader-text {
        font-size: 14px;
    }
}
</style>

<script>
// Script qui s'exécute immédiatement - pas besoin d'attendre DOMContentLoaded
(function() {
    'use strict';

    function hideLoader() {
        var loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('hidden');
        }
    }

    function showLoader(message) {
        var loader = document.getElementById('pageLoader');
        if (!loader) return;
        var text = loader.querySelector('.loader-text');
        if (text && message) text.textContent = message;
        loader.classList.remove('hidden');
    }

    // API globale (aussi enrichie par resources/js/navigationFeedback.js)
    window.hidePageLoader = hideLoader;
    window.showPageLoader = showLoader;
    
    // Vérifier l'état de chargement immédiatement
    if (document.readyState === 'loading') {
        // Page encore en chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Attendre encore un peu pour que les images se chargent
            setTimeout(hideLoader, 800);
        });
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        // Page déjà chargée ou presque
        setTimeout(hideLoader, 500);
    }
    
    // Toujours écouter l'événement load (quand tout est chargé)
    window.addEventListener('load', function() {
        setTimeout(hideLoader, 300);
    });
    
    // Fallback de sécurité après 3 secondes maximum (premier chargement seulement)
    setTimeout(function() {
        var loader = document.getElementById('pageLoader');
        if (loader && !loader.classList.contains('hidden') && !loader.dataset.navPending) {
            hideLoader();
        }
    }, 3000);
})();
</script>

