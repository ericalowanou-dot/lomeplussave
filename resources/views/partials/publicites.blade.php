@php
    use App\Models\Publicite;

    // Les pubs « entre_articles » sont injectées slot par slot (voir publicite-slot).
    $positionKey = $position ?? 'entre_articles';
    if ($positionKey === 'entre_articles') {
        $publicites = collect([]);
    } else {
        try {
            $publicites = Publicite::active()
                ->byPosition($positionKey)
                ->orderBy('ordre', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            $publicites = collect([]);
            \Log::error('Erreur dans partial publicites: ' . $e->getMessage());
        }
    }
@endphp

@if($publicites && $publicites->count() > 0)
    <div class="publicites-container publicites-{{ $positionKey }}" data-position="{{ $positionKey }}">
        @foreach($publicites as $publicite)
            @include('partials.publicite-item', ['publicite' => $publicite])
        @endforeach
    </div>

    @once
    <script>
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
