@php
    use App\Models\Publicite;

    $feedId = $feedId ?? ('ads-feed-' . uniqid());
    try {
        $feedPubs = Publicite::activeForFeed();
    } catch (\Throwable $e) {
        $feedPubs = collect();
        \Log::error('Erreur publicites feed: ' . $e->getMessage());
    }
@endphp

@if($feedPubs->isNotEmpty())
<div class="col-12 ads-feed-col {{ $feedVisibilityClass ?? '' }}">
    <section class="ads-feed"
             id="{{ $feedId }}"
             data-ads-feed
             data-duration="4500"
             aria-label="Publicités">
        <div class="ads-feed__progress d-md-none" aria-hidden="true">
            @foreach($feedPubs as $index => $publicite)
                <div class="ads-feed__bar" data-bar-index="{{ $index }}">
                    <span class="ads-feed__bar-fill"></span>
                </div>
            @endforeach
        </div>

        {{-- Mobile : carrousel plein format --}}
        <div class="ads-feed__mobile d-md-none">
            <div class="ads-feed__viewport">
                @foreach($feedPubs as $index => $publicite)
                    <article class="ads-feed__slide {{ $index === 0 ? 'is-active' : '' }}"
                             data-slide-index="{{ $index }}"
                             data-publicite-id="{{ $publicite->id }}">
                        @if($publicite->lien_url)
                            <a href="{{ $publicite->lien_url }}"
                               target="_blank"
                               rel="nofollow noopener"
                               class="ads-feed__link"
                               onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $publicite->id }}) : true;">
                                <img src="{{ $publicite->image_url }}"
                                     alt="{{ $publicite->titre ?? 'Publicité' }}"
                                     class="ads-feed__image publicite-image"
                                     loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            </a>
                        @else
                            <img src="{{ $publicite->image_url }}"
                                 alt="{{ $publicite->titre ?? 'Publicité' }}"
                                 class="ads-feed__image publicite-image"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        @endif
                    </article>
                @endforeach
            </div>

            @if($feedPubs->count() > 1)
                <button type="button" class="ads-feed__nav ads-feed__nav--prev" data-ads-prev aria-label="Publicité précédente">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="ads-feed__nav ads-feed__nav--next" data-ads-next aria-label="Publicité suivante">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <div class="ads-feed__counter d-md-none" data-ads-counter>1/{{ $feedPubs->count() }}</div>
            @endif
        </div>

        {{-- Desktop : scroll horizontal type catégories --}}
        <div class="ads-feed__desktop d-none d-md-block">
            <button type="button" class="ads-feed__scroll-btn ads-feed__scroll-btn--left" data-ads-scroll-left aria-label="Faire défiler à gauche">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="ads-feed__track" data-ads-track>
                @foreach($feedPubs as $index => $publicite)
                    <article class="ads-feed__card" data-publicite-id="{{ $publicite->id }}">
                        @if($publicite->lien_url)
                            <a href="{{ $publicite->lien_url }}"
                               target="_blank"
                               rel="nofollow noopener"
                               class="ads-feed__link"
                               onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $publicite->id }}) : true;">
                                <img src="{{ $publicite->image_url }}"
                                     alt="{{ $publicite->titre ?? 'Publicité' }}"
                                     class="ads-feed__image publicite-image"
                                     loading="lazy"
                                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            </a>
                        @else
                            <img src="{{ $publicite->image_url }}"
                                 alt="{{ $publicite->titre ?? 'Publicité' }}"
                                 class="ads-feed__image publicite-image"
                                 loading="lazy"
                                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                        @endif
                    </article>
                @endforeach
            </div>
            <button type="button" class="ads-feed__scroll-btn ads-feed__scroll-btn--right" data-ads-scroll-right aria-label="Faire défiler à droite">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </section>
</div>

@once
<link rel="stylesheet" href="{{ asset('css/ads-feed.css') }}">
<script src="{{ asset('js/ads-feed.js') }}" defer></script>
<script>
    if (typeof trackPubliciteView !== 'function') {
        function trackPubliciteView(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/publicite/${publiciteId}/view`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' }
            }).catch(() => {});
        }
    }
    if (typeof trackPubliciteClick !== 'function') {
        function trackPubliciteClick(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/publicite/${publiciteId}/click`, {
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
</script>
@endonce
@endif
