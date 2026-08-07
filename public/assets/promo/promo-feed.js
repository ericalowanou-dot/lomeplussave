(function () {
    function initFeed(root) {
        if (!root || root.dataset.promoReady === '1') return;
        root.dataset.promoReady = '1';

        const duration = parseInt(root.dataset.duration || '4500', 10);
        root.style.setProperty('--promo-duration', duration + 'ms');

        const mobileTrack = root.querySelector('[data-promo-mobile-track]');
        const slides = Array.from(root.querySelectorAll('.promo-feed__mobile-track .promo-feed__slide'));
        const bars = Array.from(root.querySelectorAll('.promo-feed__bar'));
        const counter = root.querySelector('[data-promo-counter]');
        const prevBtn = root.querySelector('[data-promo-prev]');
        const nextBtn = root.querySelector('[data-promo-next]');
        const desktopTrack = root.querySelector('[data-promo-track]');
        const scrollLeftBtn = root.querySelector('[data-promo-scroll-left]');
        const scrollRightBtn = root.querySelector('[data-promo-scroll-right]');

        let index = 0;
        let timer = null;
        let scrollingProgrammatically = false;
        let scrollSyncTimer = null;

        function isMobileMode() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }

        function isVisibleFeed() {
            const col = root.closest('.promo-feed-col');
            if (col && getComputedStyle(col).display === 'none') return false;
            if (getComputedStyle(root).display === 'none') return false;
            return true;
        }

        function updateBarsAndCounter() {
            bars.forEach((bar, i) => {
                bar.classList.remove('is-active', 'is-done');
                const fill = bar.querySelector('.promo-feed__bar-fill');
                if (fill) fill.style.width = '';
                if (i < index) bar.classList.add('is-done');
            });

            if (bars[index]) {
                void bars[index].offsetWidth;
                bars[index].classList.add('is-active');
            }

            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === index);
            });

            if (counter) {
                counter.textContent = (index + 1) + '/' + slides.length;
            }
        }

        function scrollToIndex(nextIndex, { smooth = true } = {}) {
            if (!mobileTrack || !slides.length) return;
            index = (nextIndex + slides.length) % slides.length;
            const target = slides[index];
            if (!target) return;

            scrollingProgrammatically = true;
            const left = target.offsetLeft - 6;
            mobileTrack.scrollTo({ left: left, behavior: smooth ? 'smooth' : 'auto' });
            updateBarsAndCounter();

            window.setTimeout(() => {
                scrollingProgrammatically = false;
            }, smooth ? 420 : 50);
        }

        function nearestIndexFromScroll() {
            if (!mobileTrack || !slides.length) return 0;
            const scrollLeft = mobileTrack.scrollLeft;
            let best = 0;
            let bestDist = Infinity;
            slides.forEach((slide, i) => {
                const dist = Math.abs(slide.offsetLeft - scrollLeft);
                if (dist < bestDist) {
                    bestDist = dist;
                    best = i;
                }
            });
            return best;
        }

        function stopAuto() {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
            root.classList.add('is-paused');
        }

        function scheduleNext() {
            stopAuto();
            root.classList.remove('is-paused');
            if (!isMobileMode() || !isVisibleFeed() || slides.length < 2 || document.hidden) return;

            timer = setTimeout(() => {
                scrollToIndex(index + 1);
                scheduleNext();
            }, duration);
        }

        function go(delta) {
            scrollToIndex(index + delta);
            scheduleNext();
        }

        if (prevBtn) prevBtn.addEventListener('click', () => go(-1));
        if (nextBtn) nextBtn.addEventListener('click', () => go(1));

        if (mobileTrack) {
            mobileTrack.addEventListener('scroll', () => {
                if (scrollingProgrammatically) return;
                stopAuto();
                clearTimeout(scrollSyncTimer);
                scrollSyncTimer = setTimeout(() => {
                    const nearest = nearestIndexFromScroll();
                    if (nearest !== index) {
                        index = nearest;
                        updateBarsAndCounter();
                    } else {
                        // Relancer la barre sur le slide courant
                        updateBarsAndCounter();
                    }
                    scheduleNext();
                }, 120);
            }, { passive: true });

            mobileTrack.addEventListener('touchstart', () => stopAuto(), { passive: true });
        }

        if (desktopTrack) {
            const scrollByCard = (dir) => {
                const card = desktopTrack.querySelector('.promo-feed__card');
                const amount = card ? card.offsetWidth + 16 : 200;
                desktopTrack.scrollBy({ left: dir * amount, behavior: 'smooth' });
            };
            if (scrollLeftBtn) scrollLeftBtn.addEventListener('click', () => scrollByCard(-1));
            if (scrollRightBtn) scrollRightBtn.addEventListener('click', () => scrollByCard(1));
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopAuto();
            else scheduleNext();
        });

        window.addEventListener('resize', () => {
            if (isMobileMode()) {
                scrollToIndex(index, { smooth: false });
                scheduleNext();
            } else {
                stopAuto();
            }
        });

        scrollToIndex(0, { smooth: false });
        scheduleNext();
    }

    function boot() {
        document.querySelectorAll('[data-promo-feed]').forEach((el) => {
            // Permettre la rÃ©-init aprÃ¨s partial reload
            if (el.dataset.promoReady === '1' && !el.isConnected) {
                delete el.dataset.promoReady;
            }
            initFeed(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('partial:reloaded', () => {
        document.querySelectorAll('[data-promo-feed]').forEach((el) => {
            delete el.dataset.promoReady;
        });
        boot();
    });
})();

