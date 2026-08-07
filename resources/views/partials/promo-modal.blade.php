@php
    $promoPopup = \App\Models\Publicite::activePopup();
@endphp

@if($promoPopup)
<div id="promoModal"
     class="promo-modal"
     hidden
     data-promo-modal
     data-promo-id="{{ $promoPopup->id }}"
     data-dismiss-hours="24"
     role="dialog"
     aria-modal="true"
     aria-label="{{ $promoPopup->titre ?? 'Promotion' }}">
    <div class="promo-modal__backdrop" data-promo-close></div>
    <div class="promo-modal__dialog">
        <button type="button" class="promo-modal__close" data-promo-close aria-label="Fermer">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>

        @if($promoPopup->lien_url)
            <a href="{{ $promoPopup->lien_url }}"
               class="promo-modal__media"
               target="_blank"
               rel="nofollow noopener"
               onclick="return typeof handlePubliciteClick === 'function' ? handlePubliciteClick(event, {{ $promoPopup->id }}) : true;">
                <img src="{{ $promoPopup->image_url }}"
                     alt="{{ $promoPopup->titre ?? 'Promotion' }}"
                     class="promo-modal__image promo-image"
                     loading="eager"
                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $promoPopup->id }})"
                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
            </a>
        @else
            <div class="promo-modal__media">
                <img src="{{ $promoPopup->image_url }}"
                     alt="{{ $promoPopup->titre ?? 'Promotion' }}"
                     class="promo-modal__image promo-image"
                     loading="eager"
                     onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $promoPopup->id }})"
                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
            </div>
        @endif

        @if($promoPopup->lien_url)
            <div class="promo-modal__actions">
                <a href="{{ $promoPopup->lien_url }}"
                   class="promo-modal__cta"
                   target="_blank"
                   rel="nofollow noopener"
                   onclick="typeof trackPubliciteClick === 'function' && trackPubliciteClick({{ $promoPopup->id }});">
                    Découvrir
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    </div>
</div>

@once
<link rel="stylesheet" href="{{ asset('css/promo-modal.css') }}">
<script src="{{ asset('assets/promo/promo-modal.js') }}" defer></script>
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
