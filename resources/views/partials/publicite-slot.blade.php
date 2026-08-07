{{-- Injecte les pubs configurées après le N-ème article ($after) --}}
@php
    $after = (int) ($after ?? 0);
    $pubsParSlot = $pubsParSlot ?? \App\Models\Publicite::activeSlotsEntreArticles();
    $pubsAuSlot = collect();
    if ($after > 0 && $pubsParSlot) {
        $pubsAuSlot = $pubsParSlot->get($after)
            ?? $pubsParSlot->get((string) $after)
            ?? collect();
    }
@endphp

@if($pubsAuSlot->isNotEmpty())
    <div class="col-12 publicites-slot-wrap" style="z-index: 1;">
        <div class="publicites-container publicites-entre_articles" data-position="entre_articles" data-after="{{ $after }}">
            @foreach($pubsAuSlot as $publicite)
                @include('partials.publicite-item', ['publicite' => $publicite])
            @endforeach
        </div>
    </div>

    @once
    <script>
        if (typeof trackPubliciteView !== 'function') {
            function trackPubliciteView(publiciteId) {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!token) return;
                fetch(`/spotlight/${publiciteId}/view`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                    }
                }).catch(() => {});
            }
        }

        if (typeof trackPubliciteClick !== 'function') {
            function trackPubliciteClick(publiciteId) {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!token) return;
                fetch(`/spotlight/${publiciteId}/click`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                    }
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
