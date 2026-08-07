(function () {
    function storageKey(id) {
        return 'lome_promo_modal_dismissed_' + id;
    }

    function wasDismissed(id, hours) {
        try {
            const raw = localStorage.getItem(storageKey(id));
            if (!raw) return false;
            const until = parseInt(raw, 10);
            if (!until || Number.isNaN(until)) return false;
            if (Date.now() > until) {
                localStorage.removeItem(storageKey(id));
                return false;
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    function dismiss(id, hours) {
        try {
            const ms = Math.max(1, hours) * 60 * 60 * 1000;
            localStorage.setItem(storageKey(id), String(Date.now() + ms));
        } catch (e) {}
    }

    function openModal(root) {
        root.hidden = false;
        document.body.classList.add('promo-modal-open');
    }

    function closeModal(root) {
        root.hidden = true;
        document.body.classList.remove('promo-modal-open');
        const id = root.dataset.promoId;
        const hours = parseInt(root.dataset.dismissHours || '24', 10);
        if (id) dismiss(id, hours);
    }

    function init() {
        const root = document.querySelector('[data-promo-modal]');
        if (!root) return;

        const id = root.dataset.promoId;
        const hours = parseInt(root.dataset.dismissHours || '24', 10);

        if (id && wasDismissed(id, hours)) {
            root.hidden = true;
            return;
        }

        // Léger délai pour ne pas bloquer le premier rendu
        window.setTimeout(() => openModal(root), 600);

        root.querySelectorAll('[data-promo-close]').forEach((btn) => {
            btn.addEventListener('click', () => closeModal(root));
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !root.hidden) {
                closeModal(root);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
