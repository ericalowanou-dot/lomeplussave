@php
    use App\Models\Publicite;

    $positionKey = $position ?? 'entre_articles';

    try {
        $publicites = Publicite::active()
            ->byPosition($positionKey)
            ->orderBy('ordre', 'asc')
            ->orderByDesc('created_at')
            ->get();
    } catch (\Throwable $e) {
        $publicites = collect();
        \Log::error('Erreur carousel publicités: ' . $e->getMessage());
    }
@endphp

@if($publicites->isNotEmpty())
<section class="promo-carousel"
         data-promo-carousel
         data-position="{{ $positionKey }}"
         data-duration="5000"
         aria-label="Publicités">
    {{-- Barres de progression (mobile / style WhatsApp) --}}
    @if($publicites->count() > 1)
        <div class="promo-carousel__progress" aria-hidden="true">
            @foreach($publicites as $index => $publicite)
                <div class="promo-carousel__progress-item" data-progress-index="{{ $index }}">
                    <span class="promo-carousel__progress-fill"></span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="promo-carousel__viewport">
        <button type="button" class="promo-carousel__nav promo-carousel__nav--prev" data-promo-prev aria-label="Publicité précédente">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>

        <div class="promo-carousel__track" data-promo-track>
            @foreach($publicites as $index => $publicite)
                <div class="promo-carousel__slide{{ $index === 0 ? ' is-active' : '' }}"
                     data-promo-slide
                     data-publicite-id="{{ $publicite->id }}"
                     data-index="{{ $index }}">
                    @if($publicite->lien_url)
                        <a href="{{ $publicite->lien_url }}"
                           target="_blank"
                           rel="nofollow noopener"
                           class="promo-carousel__link"
                           onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $publicite->id }}) : true;">
                            <img src="{{ $publicite->image_url }}"
                                 alt="{{ $publicite->titre ?? 'Publicité' }}"
                                 class="promo-carousel__image publicite-image"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                 decoding="async"
                                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        </a>
                    @else
                        <div class="promo-carousel__link">
                            <img src="{{ $publicite->image_url }}"
                                 alt="{{ $publicite->titre ?? 'Publicité' }}"
                                 class="promo-carousel__image publicite-image"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                 decoding="async"
                                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <button type="button" class="promo-carousel__nav promo-carousel__nav--next" data-promo-next aria-label="Publicité suivante">
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

    @if($publicites->count() > 1)
        <div class="promo-carousel__counter" data-promo-counter aria-live="polite">
            <span data-promo-current>1</span>/<span data-promo-total>{{ $publicites->count() }}</span>
        </div>
    @endif
</section>

@once
<script>
    if (typeof trackPubliciteView !== 'function') {
        function trackPubliciteView(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/spotlight/${publiciteId}/view`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' }
            }).catch(() => {});
        }
    }

    if (typeof trackPubliciteClick !== 'function') {
        function trackPubliciteClick(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/spotlight/${publiciteId}/click`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' }
            }).catch(() => {});
        }
    }

    if (typeof handlePubliciteClick !== 'function') {
        function handlePubliciteClick(event, publiciteId) {
            if (event.target && event.target.classList.contains('publicite-image')) {
                trackPubliciteClick(publiciteId);
                return true;
            }
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    }

    (function initAdsCarousels() {
        function isDesktop() {
            return window.matchMedia('(min-width: 768px)').matches;
        }

        function initCarousel(root) {
            if (root.dataset.adsReady === '1') return;
            root.dataset.adsReady = '1';

            const track = root.querySelector('[data-promo-track]');
            const slides = Array.from(root.querySelectorAll('[data-promo-slide]'));
            const prevBtn = root.querySelector('[data-promo-prev]');
            const nextBtn = root.querySelector('[data-promo-next]');
            const currentEl = root.querySelector('[data-promo-current]');
            const progressItems = Array.from(root.querySelectorAll('[data-progress-index]'));
            const duration = parseInt(root.dataset.duration || '5000', 10);

            if (!track || slides.length === 0) return;

            let index = 0;
            let timer = null;
            let paused = false;
            let touchStartX = 0;
            let touchDeltaX = 0;

            function updateCounter() {
                if (currentEl) currentEl.textContent = String(index + 1);
            }

            function resetProgress() {
                progressItems.forEach((item, i) => {
                    const fill = item.querySelector('.promo-carousel__progress-fill');
                    if (!fill) return;
                    fill.style.transition = 'none';
                    fill.style.width = i < index ? '100%' : '0%';
                    void fill.offsetWidth;
                    fill.style.transition = '';
                });
            }

            function runProgress() {
                if (isDesktop() || slides.length < 2) return;
                const active = progressItems[index];
                if (!active) return;
                const fill = active.querySelector('.promo-carousel__progress-fill');
                if (!fill) return;
                fill.style.transition = 'none';
                fill.style.width = '0%';
                void fill.offsetWidth;
                fill.style.transition = `width ${duration}ms linear`;
                fill.style.width = '100%';
            }

            function goTo(nextIndex, { auto = false } = {}) {
                if (slides.length === 0) return;
                index = (nextIndex + slides.length) % slides.length;

                if (isDesktop()) {
                    const slide = slides[index];
                    if (slide) {
                        track.scrollTo({
                            left: slide.offsetLeft - 8,
                            behavior: 'smooth'
                        });
                    }
                } else {
                    slides.forEach((slide, i) => {
                        slide.classList.toggle('is-active', i === index);
                    });
                    resetProgress();
                    if (auto !== false && !paused) {
                        runProgress();
                    }
                }

                updateCounter();
            }

            function stopAuto() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
            }

            function scheduleAuto() {
                stopAuto();
                if (isDesktop() || slides.length < 2 || paused) return;
                runProgress();
                timer = setTimeout(() => {
                    goTo(index + 1, { auto: true });
                    scheduleAuto();
                }, duration);
            }

            function next() {
                goTo(index + 1);
                scheduleAuto();
            }

            function prev() {
                goTo(index - 1);
                scheduleAuto();
            }

            if (prevBtn) prevBtn.addEventListener('click', prev);
            if (nextBtn) nextBtn.addEventListener('click', next);

            root.addEventListener('mouseenter', () => {
                paused = true;
                stopAuto();
                resetProgress();
            });
            root.addEventListener('mouseleave', () => {
                paused = false;
                scheduleAuto();
            });

            track.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].clientX;
                touchDeltaX = 0;
                paused = true;
                stopAuto();
            }, { passive: true });

            track.addEventListener('touchmove', (e) => {
                touchDeltaX = e.changedTouches[0].clientX - touchStartX;
            }, { passive: true });

            track.addEventListener('touchend', () => {
                paused = false;
                if (Math.abs(touchDeltaX) > 40) {
                    if (touchDeltaX < 0) next();
                    else prev();
                } else {
                    scheduleAuto();
                }
            });

            if (isDesktop() && slides.length > 1) {
                track.addEventListener('scroll', () => {
                    const center = track.scrollLeft + track.clientWidth / 2;
                    let closest = 0;
                    let min = Infinity;
                    slides.forEach((slide, i) => {
                        const mid = slide.offsetLeft + slide.offsetWidth / 2;
                        const dist = Math.abs(mid - center);
                        if (dist < min) {
                            min = dist;
                            closest = i;
                        }
                    });
                    if (closest !== index) {
                        index = closest;
                        updateCounter();
                    }
                }, { passive: true });
            }

            window.addEventListener('resize', () => {
                goTo(index);
                scheduleAuto();
            });

            goTo(0);
            scheduleAuto();
        }

        function boot() {
            document.querySelectorAll('[data-promo-carousel]').forEach(initCarousel);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }

        document.addEventListener('partial:reloaded', boot);
    })();
</script>
@endonce
@endif
