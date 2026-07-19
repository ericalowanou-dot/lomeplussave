@php
    use App\Models\Publicite;

    try {
        $publicites = Publicite::active()
            ->byPosition($position ?? 'entre_articles')
            ->orderBy('ordre', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    } catch (\Exception $e) {
        $publicites = collect([]);
        \Log::error('Erreur dans partial publicites: ' . $e->getMessage());
    }

    $positionKey = $position ?? 'entre_articles';
@endphp

@if($publicites && $publicites->count() > 0)
    <div class="publicites-container publicites-{{ $positionKey }}" data-position="{{ $positionKey }}">
        @foreach($publicites as $publicite)
            <div class="publicite-item"
                 data-publicite-id="{{ $publicite->id }}"
                 data-lien="{{ $publicite->lien_url }}">
                @if($publicite->lien_url)
                    <a href="{{ $publicite->lien_url }}"
                       target="_blank"
                       rel="nofollow noopener"
                       class="publicite-link"
                       onclick="return handlePubliciteClick(event, {{ $publicite->id }});">
                        <img src="{{ asset($publicite->image) }}"
                             alt="{{ $publicite->titre ?? 'Publicité' }}"
                             class="publicite-image"
                             loading="lazy"
                             decoding="async"
                             sizes="(max-width: 767px) 100vw, (max-width: 1199px) 90vw, 960px"
                             onload="trackPubliciteView({{ $publicite->id }})"
                             onerror="this.src='{{ asset('images/placeholder.png') }}';">
                    </a>
                @else
                    <img src="{{ asset($publicite->image) }}"
                         alt="{{ $publicite->titre ?? 'Publicité' }}"
                         class="publicite-image"
                         loading="lazy"
                         decoding="async"
                         sizes="(max-width: 767px) 100vw, (max-width: 1199px) 90vw, 960px"
                         onload="trackPubliciteView({{ $publicite->id }})"
                         onerror="this.src='{{ asset('images/placeholder.png') }}';">
                @endif
            </div>
        @endforeach
    </div>

    @once
    <script>
        function trackPubliciteView(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/publicite/${publiciteId}/view`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                }
            }).catch(() => {});
        }

        function trackPubliciteClick(publiciteId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(`/publicite/${publiciteId}/click`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                }
            }).catch(() => {});
        }

        function handlePubliciteClick(event, publiciteId) {
            if (event.target && event.target.classList.contains('publicite-image')) {
                trackPubliciteClick(publiciteId);
                return true;
            }
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    </script>
    @endonce
@endif
