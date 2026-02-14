<!-- Bouton Scroll to Top Professionnel -->
<button id="scrollToTopBtn" class="scroll-to-top-btn" aria-label="Retour en haut de la page" title="Retour en haut">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 19V5M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>

<style>
.scroll-to-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(255, 153, 0, 0.4);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.scroll-to-top-btn.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.scroll-to-top-btn:hover {
    background: linear-gradient(135deg, #E68900 0%, #CC7700 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 153, 0, 0.5);
}

.scroll-to-top-btn:active {
    transform: translateY(-1px) scale(0.95);
}

.scroll-to-top-btn svg {
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease;
}

.scroll-to-top-btn:hover svg {
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .scroll-to-top-btn {
        width: 45px;
        height: 45px;
        bottom: 20px;
        right: 20px;
    }
    
    .scroll-to-top-btn svg {
        width: 20px;
        height: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollBtn = document.getElementById('scrollToTopBtn');
    
    if (!scrollBtn) return;
    
    // Seuil de scroll pour afficher le bouton (200% de la hauteur de l'écran, minimum 2000px)
    const scrollThreshold = Math.max(2000, window.innerHeight * 2);
    
    // Afficher/masquer le bouton selon le scroll
    function toggleScrollButton() {
        if (window.pageYOffset > scrollThreshold) {
            scrollBtn.classList.add('show');
        } else {
            scrollBtn.classList.remove('show');
        }
    }
    
    // Scroll vers le haut
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Écouter le scroll (optimisé)
    window.addEventListener('scroll', toggleScrollButton, { passive: true });
    
    // Vérifier au chargement
    toggleScrollButton();
});
</script>

