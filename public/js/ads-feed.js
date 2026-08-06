(function () {
    function initFeed(root) {
        if (!root || root.dataset.adsReady === '1') return;
        root.dataset.adsReady = '1';

        const duration = parseInt(root.dataset.duration || '4500', 10);
        root.style.setProperty('--ads-duration', duration + 'ms');

        const slides = Array.from(root.querySelectorAll('.ads-feed__slide'));
        const bars = Array.from(root.querySelectorAll('.ads-feed__bar'));
        const counter = root.querySelector('[data-ads-counter]');
        const prevBtn = root.querySelector('[data-ads-prev]');
        const nextBtn = root.querySelector('[data-ads-next]');
        const track = root.querySelector('[data-ads-track]');
        const scrollLeftBtn = root.querySelector('[data-ads-scroll-left]');
        const scrollRightBtn = root.querySelector('[data-ads-scroll-right]');
        const viewport = root.querySelector('.ads-feed__viewport');

        let index = 0;
        let timer = null;
        let touching = false;
        let startX = 0;

        function isMobileMode() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }

        function isVisibleFeed() {
            if (!root.offsetParent && getComputedStyle(root).display === 'none') return false;
            const col = root.closest('.ads-feed-col');
            if (col && getComputedStyle(col).display === 'none') return false;
            return true;
        }

        function setSlide(nextIndex, { resetBars = true } = {}) {
            if (!slides.length) return;
            index = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === index);
            });

            bars.forEach((bar, i) => {
                bar.classList.remove('is-active', 'is-done');
                const fill = bar.querySelector('.ads-feed__bar-fill');
                if (fill) fill.style.width = '';
                if (i < index) bar.classList.add('is-done');
            });

            if (bars[index]) {
                // reflow pour relancer l'animation
                void bars[index].offsetWidth;
                bars[index].classList.add('is-active');
            }

            if (counter) {
                counter.textContent = (index + 1) + '/' + slides.length;
            }

            if (!resetBars) return;
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
                setSlide(index + 1);
                scheduleNext();
            }, duration);
        }

        function go(delta) {
            setSlide(index + delta);
            scheduleNext();
        }

        if (prevBtn) prevBtn.addEventListener('click', () => go(-1));
        if (nextBtn) nextBtn.addEventListener('click', () => go(1));

        if (viewport && slides.length > 1) {
            viewport.addEventListener('touchstart', (e) => {
                touching = true;
                startX = e.changedTouches[0].clientX;
                stopAuto();
            }, { passive: true });

            viewport.addEventListener('touchend', (e) => {
                if (!touching) return;
                touching = false;
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 40) {
                    go(dx < 0 ? 1 : -1);
                } else {
                    scheduleNext();
                }
            }, { passive: true });
        }

        if (track) {
            const scrollByCard = (dir) => {
                const card = track.querySelector('.ads-feed__card');
                const amount = card ? card.offsetWidth + 16 : 200;
                track.scrollBy({ left: dir * amount, behavior: 'smooth' });
            };
            if (scrollLeftBtn) scrollLeftBtn.addEventListener('click', () => scrollByCard(-1));
            if (scrollRightBtn) scrollRightBtn.addEventListener('click', () => scrollByCard(1));
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopAuto();
            else scheduleNext();
        });

        window.addEventListener('resize', () => {
            if (isMobileMode()) scheduleNext();
            else stopAuto();
        });

        setSlide(0);
        scheduleNext();
    }

    function boot() {
        document.querySelectorAll('[data-ads-feed]').forEach(initFeed);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Support rechargement partiel des listes
    document.addEventListener('partial:reloaded', boot);
})();
