{{-- Une publicité individuelle --}}
@php
    $publicite = $publicite ?? null;
@endphp

@if($publicite)
<div class="publicite-item"
     data-publicite-id="{{ $publicite->id }}"
     data-lien="{{ $publicite->lien_url }}">
    @if($publicite->lien_url)
        <a href="{{ $publicite->lien_url }}"
           target="_blank"
           rel="nofollow noopener"
           class="publicite-link"
           onclick="return handlePubliciteClick(event, {{ $publicite->id }});">
            <img src="{{ $publicite->image_url }}"
                 alt="{{ $publicite->titre ?? 'Publicité' }}"
                 class="publicite-image"
                 loading="lazy"
                 decoding="async"
                 sizes="(max-width: 767px) 100vw, (max-width: 1199px) 90vw, 960px"
                 onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
        </a>
    @else
        <img src="{{ $publicite->image_url }}"
             alt="{{ $publicite->titre ?? 'Publicité' }}"
             class="publicite-image"
             loading="lazy"
             decoding="async"
             sizes="(max-width: 767px) 100vw, (max-width: 1199px) 90vw, 960px"
             onload="typeof trackPubliciteView === 'function' && trackPubliciteView({{ $publicite->id }})"
             onerror="this.src='{{ asset('images/placeholder.png') }}';">
    @endif
</div>
@endif
