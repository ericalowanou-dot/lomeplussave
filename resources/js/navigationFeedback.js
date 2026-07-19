/**
 * Retour visuel immédiat sur navigation / actions (évite l'écran "figé").
 */

function getLoader() {
    return document.getElementById('pageLoader');
}

export function showPageLoader(message = 'Chargement...') {
    const loader = getLoader();
    if (!loader) return;

    const text = loader.querySelector('.loader-text');
    if (text && message) {
        text.textContent = message;
    }

    loader.classList.remove('hidden');
    loader.style.opacity = '1';
    loader.style.visibility = 'visible';
    loader.style.pointerEvents = 'auto';
}

export function hidePageLoader() {
    const loader = getLoader();
    if (!loader) return;
    loader.classList.add('hidden');
}

function sameOrigin(url) {
    try {
        return new URL(url, window.location.href).origin === window.location.origin;
    } catch {
        return false;
    }
}

function shouldSkipLink(a, event) {
    if (!a || !a.getAttribute('href')) return true;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return true;
    if (a.target === '_blank' || a.hasAttribute('download')) return true;
    if (a.dataset.noLoader === '1' || a.closest('[data-no-loader]')) return true;

    const href = a.getAttribute('href').trim();
    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
        return true;
    }

    if (!sameOrigin(a.href)) return true;

    try {
        const target = new URL(a.href, window.location.href);
        const current = new URL(window.location.href);
        // Même page, seul le hash change
        if (
            target.pathname === current.pathname &&
            target.search === current.search &&
            target.hash &&
            target.hash !== current.hash
        ) {
            return true;
        }
        // Clic vers la page courante exacte
        if (
            target.pathname === current.pathname &&
            target.search === current.search &&
            !target.hash
        ) {
            return true;
        }
    } catch {
        return true;
    }

    return false;
}

function shouldSkipForm(form) {
    if (!form) return true;
    if (form.target === '_blank') return true;
    if (form.dataset.noLoader === '1' || form.closest('[data-no-loader]')) return true;

    // Formulaires AJAX déjà gérés sans rechargement complet
    const ajaxIds = new Set([
        'filterForm',
        'filterFormSidebar',
        'search-form',
        'editUserForm',
        'logoutForm',
    ]);
    if (form.id && ajaxIds.has(form.id)) return true;

    return false;
}

export function initNavigationFeedback() {
    window.showPageLoader = showPageLoader;
    window.hidePageLoader = hidePageLoader;

    document.addEventListener(
        'click',
        (e) => {
            const a = e.target.closest?.('a[href]');
            if (!a || shouldSkipLink(a, e)) return;
            showPageLoader('Chargement...');
        },
        true,
    );

    document.addEventListener(
        'submit',
        (e) => {
            if (shouldSkipForm(e.target)) return;
            showPageLoader('Chargement...');
        },
        true,
    );

    // Sécurité : si le loader reste affiché trop longtemps (erreur réseau, etc.)
    window.addEventListener('pageshow', () => {
        hidePageLoader();
    });
}
