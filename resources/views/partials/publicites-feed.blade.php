@php
    use App\Models\Publicite;

    $feedId = $feedId ?? ('promo-feed-' . uniqid());
    try {
        $feedPubs = Publicite::activeForFeed();
    } catch (\Throwable $e) {
        $feedPubs = collect();
        \Log::error('Erreur publicites feed: ' . $e->getMessage());
    }
@endphp

@if($feedPubs->isNotEmpty())
<div class="col-12 promo-feed-col {{ $feedVisibilityClass ?? '' }}">
    <section class="promo-feed"
             id="{{ $feedId }}"
             data-promo-feed
             data-duration="4500"
             aria-label="Promotions">
        <div class="promo-feed__progress d-md-none" aria-hidden="true">
            @foreach($feedPubs as $index => $publicite)
                <div class="promo-feed__bar" data-bar-index="{{ $index }}">
                    <span class="promo-feed__bar-fill"></span>
                </div>
            @endforeach
        </div>

        {{-- Mobile : bandeau horizontal scrollable (style catégories) + barres WhatsApp --}}
        <div class="promo-feed__mobile d-md-none">
            <div class="promo-feed__mobile-wrap">
                @if($feedPubs->count() > 1)
                    <button type="button" class="promo-feed__nav promo-feed__nav--prev" data-promo-prev aria-label="Promotion précédente">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                @endif

                <div class="promo-feed__mobile-track" data-promo-mobile-track>
                    @foreach($feedPubs as $index => $publicite)
                        <article class="promo-feed__slide {{ $index === 0 ? 'is-active' : '' }}"
                                 data-slide-index="{{ $index }}"
                                 data-item-id="{{ $publicite->id }}">
                            @if($publicite->lien_url)
                                <a href="{{ $publicite->lien_url }}"
                                   target="_blank"
                                   rel="nofollow noopener"
                                   class="promo-feed__link"
                                   onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $publicite->id }}) : true;">
                                    <img src="{{ $publicite->image_url }}"
                                         alt="{{ $publicite->titre ?? 'Promotion' }}"
                                         class="promo-feed__image promo-image"
                                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                         onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                         onerror="this.src='{{ asset('images/placeholder.png') }}';">
                                </a>
                            @else
                                <img src="{{ $publicite->image_url }}"
                                     alt="{{ $publicite->titre ?? 'Promotion' }}"
                                     class="promo-feed__image promo-image"
                                     loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            @endif
                        </article>
                    @endforeach
                </div>

                @if($feedPubs->count() > 1)
                    <button type="button" class="promo-feed__nav promo-feed__nav--next" data-promo-next aria-label="Promotion suivante">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <div class="promo-feed__counter" data-promo-counter>1/{{ $feedPubs->count() }}</div>
                @endif
            </div>
        </div>

        {{-- Desktop : scroll horizontal (phase 2) --}}
        <div class="promo-feed__desktop d-none d-md-block">
            <button type="button" class="promo-feed__scroll-btn promo-feed__scroll-btn--left" data-promo-scroll-left aria-label="Faire défiler à gauche">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="promo-feed__track" data-promo-track>
                @foreach($feedPubs as $index => $publicite)
                    <article class="promo-feed__card" data-item-id="{{ $publicite->id }}">
                        @if($publicite->lien_url)
                            <a href="{{ $publicite->lien_url }}"
                               target="_blank"
                               rel="nofollow noopener"
                               class="promo-feed__link"
                               onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $publicite->id }}) : true;">
                                <img src="{{ $publicite->image_url }}"
                                     alt="{{ $publicite->titre ?? 'Promotion' }}"
                                     class="promo-feed__image promo-image"
                                     loading="lazy"
                                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            </a>
                        @else
                            <img src="{{ $publicite->image_url }}"
                                 alt="{{ $publicite->titre ?? 'Promotion' }}"
                                 class="promo-feed__image promo-image"
                                 loading="lazy"
                                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        @endif
                    </article>
                @endforeach
            </div>
            <button type="button" class="promo-feed__scroll-btn promo-feed__scroll-btn--right" data-promo-scroll-right aria-label="Faire défiler à droite">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </section>
</div>

@once
<link rel="stylesheet" href="{{ asset('css/promo-feed.css') }}">
<script src="{{ asset('assets/promo/promo-feed.js') }}" defer></script>
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
            if (event.target && (event.target.classList.contains('promo-image') || event.target.classList.contains('publicite-image'))) {
                trackPubliciteClick(publiciteId);
                return true;
            }
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    }
</script>
@endonce
@endif
