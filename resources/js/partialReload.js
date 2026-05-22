function sameOrigin(url) {
    try {
        const u = new URL(url, window.location.href);
        return u.origin === window.location.origin;
    } catch {
        return false;
    }
}

function initGuestBannerRotation(root = document) {
    const banner = root.querySelector('.banner-ad');
    if (!banner) return;

    // Évite de relancer plusieurs intervalles après un re-render AJAX
    if (banner.dataset.rotating === '1') return;
    banner.dataset.rotating = '1';

    const images = banner.querySelectorAll('.ad-img');
    const adLink = banner.querySelector('a');
    const loginUrl = banner.dataset.loginUrl || adLink?.getAttribute('href') || '';

    if (!images.length || !adLink || !loginUrl) return;

    let currentIndex = 0;
    window.setInterval(() => {
        const currentImage = images[currentIndex];
        currentImage.classList.remove('active');
        currentImage.classList.add('exit');

        currentIndex = (currentIndex + 1) % images.length;
        const nextImage = images[currentIndex];
        adLink.href = loginUrl;
        nextImage.classList.add('active');

        window.setTimeout(() => {
            currentImage.classList.remove('exit');
        }, 1000);
    }, 5000);
}

async function fetchArticlesPartial(url) {
    const res = await fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    });

    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
    }

    return await res.json();
}

function replaceArticlesDom({ list, pagination }) {
    const listEl = document.querySelector('main #articles-results[data-context]');
    if (listEl && typeof list === 'string') {
        listEl.innerHTML = list;
        initGuestBannerRotation(listEl);
    }

    const paginationEl = document.getElementById('pagination-wrapper');
    if (paginationEl && typeof pagination === 'string') {
        paginationEl.innerHTML = pagination;
    }
}

function closeFilterModalIfOpen() {
    const modal = document.getElementById('filterModal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}

function buildUrlWithFormParams(form) {
    const action = form.getAttribute('action') || window.location.pathname;
    const url = new URL(action, window.location.origin);
    const params = new URLSearchParams(new FormData(form));

    // Si on change des filtres, on revient à la page 1
    params.delete('page');

    const qs = params.toString();
    if (qs) url.search = qs;
    return url.toString();
}

function initAjaxFilters() {
    const form = document.getElementById('filterForm');
    if (!form) return;

    // Interception en capture pour passer avant d’éventuels handlers inline
    form.addEventListener(
        'submit',
        async (e) => {
            e.preventDefault();

            const url = buildUrlWithFormParams(form);

            try {
                const data = await fetchArticlesPartial(url);
                replaceArticlesDom(data);
                window.history.pushState({ partial: true }, '', url);
                closeFilterModalIfOpen();

                const listEl = document.querySelector('main #articles-results[data-context]');
                listEl?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                // Fallback navigation normale
                window.location.href = url;
            }
        },
        true,
    );

    const resetBtn = document.getElementById('resetFilters');
    resetBtn?.addEventListener(
        'click',
        async (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();

            form.reset();

            const sousCategorieSelect = document.getElementById('sous_categorie');
            if (sousCategorieSelect) {
                sousCategorieSelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
            }

            const url = new URL(form.getAttribute('action') || window.location.pathname, window.location.origin);

            try {
                const data = await fetchArticlesPartial(url.toString());
                replaceArticlesDom(data);
                window.history.pushState({ partial: true }, '', url.toString());
                closeFilterModalIfOpen();

                const listEl = document.querySelector('main #articles-results[data-context]');
                listEl?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                window.location.href = url.toString();
            }
        },
        true,
    );
}

function initAjaxPagination() {
    const initialListEl = document.querySelector('main #articles-results[data-context]');
    if (!initialListEl) return;

    // Délégation d’évènement : marche même après remplacement du HTML
    document.addEventListener('click', async (e) => {
        const a = e.target.closest?.('.pagination a');
        if (!a) return;
        if (!sameOrigin(a.href)) return;

        e.preventDefault();

        try {
            const data = await fetchArticlesPartial(a.href);
            replaceArticlesDom(data);
            window.history.pushState({ partial: true }, '', a.href);
            // Option UX : remonter au début de la liste
            const listEl = document.querySelector('main #articles-results[data-context]');
            listEl?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            // Fallback : navigation normale si AJAX KO
            window.location.href = a.href;
        }
    });

    window.addEventListener('popstate', async () => {
        // Évite d'interférer avec la navigation partielle home <-> détail.
        if (!document.querySelector('main #articles-results[data-context]')) return;
        try {
            const data = await fetchArticlesPartial(window.location.href);
            replaceArticlesDom(data);
        } catch {
            // Si ça échoue, on laisse le navigateur faire au prochain click
        }
    });
}

function debounce(fn, waitMs = 300) {
    let t;
    return (...args) => {
        window.clearTimeout(t);
        t = window.setTimeout(() => fn(...args), waitMs);
    };
}

function initAjaxSearch() {
    const listEl = document.querySelector('main #articles-results[data-context]');
    if (!listEl) return;

    const form = document.getElementById('search-form');
    const input = document.getElementById('search');
    if (!form || !input) return;

    const baseAction =
        document.getElementById('filterForm')?.getAttribute('action') ||
        window.location.pathname;

    const runSearch = async () => {
        const q = (input.value || '').trim();

        const url = new URL(window.location.href);
        url.pathname = new URL(baseAction, window.location.origin).pathname;

        if (q.length >= 3) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }

        // Nouvelle recherche => page 1
        url.searchParams.delete('page');

        try {
            const data = await fetchArticlesPartial(url.toString());
            replaceArticlesDom(data);
            window.history.pushState({ partial: true }, '', url.toString());
            listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch {
            window.location.href = url.toString();
        }
    };

    form.addEventListener(
        'submit',
        (e) => {
            e.preventDefault();
            runSearch();
        },
        true,
    );

    input.addEventListener('input', debounce(runSearch, 300));
}

function hideSubcategoriesUi() {
    const subcategoriesContainer = document.querySelector('.subcategories-container');
    const overlay = document.getElementById('overlay');
    if (subcategoriesContainer) subcategoriesContainer.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

function initAjaxCategories() {
    const listEl = document.querySelector('main #articles-results[data-context]');
    if (!listEl) return;

    // Sur desktop (pointeur fin), cliquer une catégorie filtre directement.
    // Sur mobile (pointer coarse), on garde le comportement existant (tap = ouvrir sous-catégories).
    document.addEventListener(
        'click',
        async (e) => {
            if (window.matchMedia?.('(pointer: coarse)').matches) return;

            const btn = e.target.closest?.('.categories-item-navigation');
            if (!btn) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            const categoryId = btn.getAttribute('data-id');
            if (!categoryId) return;

            const url = new URL(window.location.href);
            url.searchParams.set('categorie', categoryId);
            url.searchParams.delete('sous_categorie');
            url.searchParams.delete('page');

            try {
                const data = await fetchArticlesPartial(url.toString());
                replaceArticlesDom(data);
                window.history.pushState({ partial: true }, '', url.toString());
                hideSubcategoriesUi();
                listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                window.location.href = url.toString();
            }
        },
        true,
    );

    // Capture + stopImmediatePropagation pour neutraliser le handler inline
    // qui fait window.location.href sur les boutons de sous-catégorie.
    document.addEventListener(
        'click',
        async (e) => {
            const btn = e.target.closest?.('.subcategory-item');
            if (!btn) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            const subcategoryId = btn.getAttribute('data-id');
            if (!subcategoryId) return;

            const url = new URL(window.location.href);
            url.searchParams.set('sous_categorie', subcategoryId);
            url.searchParams.delete('categorie');
            url.searchParams.delete('page');

            // Conserver une éventuelle recherche q + autres filtres déjà dans l’URL
            try {
                const data = await fetchArticlesPartial(url.toString());
                replaceArticlesDom(data);
                window.history.pushState({ partial: true }, '', url.toString());
                hideSubcategoriesUi();
                listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                window.location.href = url.toString();
            }
        },
        true,
    );
}

/** Pages partageant le même layout (app2) + main.flex-fill : navigation sans rechargement complet. */
function isAppShellPartialPath(pathname) {
    if (pathname === '/' || /^\/article\/[^/]+\/?$/.test(pathname)) {
        return true;
    }
    if (/^\/mes-favoris\/?$/.test(pathname) || /^\/mes-annonces\/?$/.test(pathname)) {
        return true;
    }
    return false;
}

async function fetchPageHtml(url) {
    const res = await fetch(url, {
        method: 'GET',
        headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
    }

    return await res.text();
}

function wait(ms) {
    return new Promise((resolve) => window.setTimeout(resolve, ms));
}

async function animateMainSwap(mainEl, swapFn) {
    if (!mainEl) {
        swapFn();
        return;
    }

    mainEl.classList.add('main-fade-out');
    await wait(120);

    swapFn();

    mainEl.classList.remove('main-fade-out');
    mainEl.classList.add('main-fade-in');
    await wait(180);
    mainEl.classList.remove('main-fade-in');
}

function executeScriptsFromElement(root) {
    const scripts = root.querySelectorAll('script');
    scripts.forEach((oldScript) => {
        const newScript = document.createElement('script');

        for (const attr of oldScript.attributes) {
            newScript.setAttribute(attr.name, attr.value);
        }

        // Les scripts inline insérés via innerHTML ne s'exécutent pas:
        // on les recrée explicitement.
        if (!oldScript.src) {
            newScript.textContent = oldScript.textContent || '';
        }

        oldScript.replaceWith(newScript);
    });
}

async function replaceMainFromHtml(html) {
    const parser = new DOMParser();
    const nextDoc = parser.parseFromString(html, 'text/html');

    const nextMain = nextDoc.querySelector('main.flex-fill');
    const currentMain = document.querySelector('main.flex-fill');
    if (!nextMain || !currentMain) return false;

    await animateMainSwap(currentMain, () => {
        currentMain.innerHTML = nextMain.innerHTML;
    });

    if (nextDoc.title) {
        document.title = nextDoc.title;
    }

    executeScriptsFromElement(currentMain);
    return true;
}

function initPartialDetailNavigation() {
    document.addEventListener(
        'click',
        async (e) => {
            const a = e.target.closest?.('a[href]');
            if (!a) return;
            // La pagination de la home est déjà gérée par initAjaxPagination()
            // pour éviter un double chargement (AJAX list + swap <main>).
            if (a.closest('.pagination')) return;
            if (a.target === '_blank' || a.hasAttribute('download')) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (!sameOrigin(a.href)) return;

            const targetUrl = new URL(a.href, window.location.href);
            const currentUrl = new URL(window.location.href);

            // Accueil, détail article, mes favoris, ma boutique (même coque + swap du <main>).
            if (!isAppShellPartialPath(currentUrl.pathname)) return;
            if (!isAppShellPartialPath(targetUrl.pathname)) return;

            e.preventDefault();

            // Menu compte (header) : sans rechargement complet le modal restait ouvert.
            const accountModal = document.getElementById('accountModal');
            if (accountModal) {
                accountModal.style.display = 'none';
            }

            try {
                const html = await fetchPageHtml(targetUrl.toString());
                const replaced = await replaceMainFromHtml(html);
                if (!replaced) {
                    window.location.href = targetUrl.toString();
                    return;
                }

                window.history.pushState({ pjax: true }, '', targetUrl.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch {
                window.location.href = targetUrl.toString();
            }
        },
        true,
    );

    window.addEventListener('popstate', async () => {
        const url = new URL(window.location.href);
        if (!isAppShellPartialPath(url.pathname)) return;

        try {
            const html = await fetchPageHtml(url.toString());
            const replaced = await replaceMainFromHtml(html);
            if (!replaced) {
                window.location.reload();
            }
        } catch {
            window.location.reload();
        }
    });
}

function initShareModalFallback() {
    function openModal() {
        const modal = document.getElementById('share-modal');
        if (!modal) return;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('share-modal');
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    async function copyShareUrl() {
        const input = document.getElementById('share-url');
        if (!input) return;
        const text = input.value || window.location.href;

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
                return;
            }
        } catch {
            // fallback ci-dessous
        }

        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
        } catch {
            // no-op: fallback silencieux
        }
    }

    document.addEventListener('click', async (e) => {
        const openBtn = e.target.closest?.('.share-button-detail, .btn-share');
        if (openBtn) {
            e.preventDefault();
            openModal();
            return;
        }

        const closeBtn = e.target.closest?.('#share-modal .close');
        if (closeBtn) {
            e.preventDefault();
            closeModal();
            return;
        }

        const copyBtn = e.target.closest?.('#share-modal .copy-btn');
        if (copyBtn) {
            e.preventDefault();
            await copyShareUrl();
            return;
        }

        const modal = document.getElementById('share-modal');
        if (modal && e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const modal = document.getElementById('share-modal');
        if (modal?.style.display === 'block') {
            closeModal();
        }
    });
}

export function initPartialReload() {
    initPartialDetailNavigation();
    initShareModalFallback();
    initGuestBannerRotation(document);
    initAjaxFilters();
    initAjaxSearch();
    initAjaxCategories();
    initAjaxPagination();
}

