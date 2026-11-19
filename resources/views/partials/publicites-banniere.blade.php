{{--
    Carrousel « À la une » de la page d'accueil, au-dessus du titre « Annonces ».
    Position et carrousel de new.lomeplus.com (pastilles en bas, défilement 6 s),
    mais l'image de la pub s'affiche en entier sur toute la largeur. Le fond orange
    avec titre ne sert que pour une pub sans image.
    Source : publicités actives des positions « homepage_top » puis « header ».
--}}
@php
    use App\Models\Publicite;

    try {
        $bannieres = Publicite::active()
            ->whereIn('position', ['homepage_top', 'header'])
            ->orderByRaw("CASE WHEN position = 'homepage_top' THEN 0 ELSE 1 END")
            ->orderBy('ordre')
            ->orderByDesc('created_at')
            ->get();
    } catch (\Throwable $e) {
        $bannieres = collect();
        \Log::error('Erreur carrousel accueil: ' . $e->getMessage());
    }
@endphp

@if($bannieres->isNotEmpty())
<section class="site-banners" aria-roledescription="carrousel" aria-label="À la une"
         data-site-banners data-count="{{ $bannieres->count() }}">
    <div class="site-banners-track" data-site-banners-track>
        @foreach($bannieres as $i => $pub)
            @php $tag = $pub->lien_url ? 'a' : 'div'; @endphp
            <{{ $tag }} class="site-banner{{ $pub->image ? ' site-banner--image' : '' }}"
                @if($pub->lien_url) href="{{ $pub->lien_url }}" target="_blank" rel="nofollow noopener" @endif
                @if($pub->image) style="--banner-img: url('{{ $pub->image_url }}')" @endif
                data-publicite-id="{{ $pub->id }}"
                aria-roledescription="diapositive"
                aria-label="{{ $i + 1 }} sur {{ $bannieres->count() }}"
                tabindex="{{ $i === 0 ? 0 : -1 }}">
                @if($pub->image)
                    {{-- Image entière, sans fond orange ; ses bords sont comblés par un flou de la même image. --}}
                    <img src="{{ $pub->image_url }}" alt="{{ $pub->titre ?? 'Publicité' }}" class="site-banner-image"
                         @if($i > 0) loading="lazy" @endif decoding="async">
                @else
                    <span class="site-banner-text">
                        @if($pub->titre)
                            <span class="site-banner-headline">{{ $pub->titre }}</span>
                        @endif
                        <span class="site-banner-advertiser">Lome+</span>
                    </span>
                @endif
            </{{ $tag }}>
        @endforeach
    </div>

    @if($bannieres->count() > 1)
        <div class="site-banners-dots">
            @foreach($bannieres as $i => $pub)
                <button type="button" class="{{ $i === 0 ? 'is-active' : '' }}"
                        data-site-banners-dot="{{ $i }}" aria-label="Bannière {{ $i + 1 }}"></button>
            @endforeach
        </div>
    @endif
</section>

@once
<style>
    .site-banners {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.08);
        margin-bottom: 24px;
    }
    .site-banners-track {
        display: flex;
        transition: transform 0.5s;
    }
    .site-banner {
        flex: 0 0 100%;
        display: flex;
        align-items: stretch;
        width: 100%;
        height: 210px;
        padding: 0;
        border: 0;
        background: linear-gradient(270deg, rgba(0, 0, 0, 0.22), rgba(0, 0, 0, 0) 60%), #FF9900;
        color: #fff;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }
    div.site-banner { cursor: default; }
    .site-banner:hover, .site-banner:focus { color: #fff; text-decoration: none; }
    .site-banner-image {
        flex: 0 0 auto;
        display: block;
        width: 42%;
        height: 100%;
        object-fit: cover;
        -webkit-mask-image: linear-gradient(90deg, #000 72%, transparent);
                mask-image: linear-gradient(90deg, #000 72%, transparent);
    }
    /* Bannière avec image : l'image s'affiche en entier, sans dégradé ni texte. */
    .site-banner--image {
        position: relative;
        overflow: hidden;
        background: #1f2937;
    }
    .site-banner--image::before {
        content: "";
        position: absolute;
        inset: -20px;
        background: var(--banner-img) center / cover no-repeat;
        filter: blur(18px) brightness(0.85);
    }
    .site-banner--image .site-banner-image {
        position: relative;
        width: 100%;
        height: 100%;
        object-fit: contain;
        -webkit-mask-image: none;
                mask-image: none;
    }
    .site-banner-text {
        flex: 1 1 0%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 4px;
        padding: 24px 32px 30px 20px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    .site-banner-headline {
        font-size: clamp(1.3125rem, 1.1458rem + .7407vw, 1.8125rem);
        font-weight: 700;
        line-height: 1.15;
        overflow-wrap: anywhere;
    }
    .site-banner-advertiser {
        font-size: clamp(.6875rem, .6667rem + .0926vw, .75rem);
        opacity: 0.9;
        margin-top: 6px;
    }
    .site-banners-dots {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
    }
    .site-banners-dots button {
        width: 8px;
        height: 8px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.55);
        cursor: pointer;
        transition: width 0.3s;
    }
    .site-banners-dots button.is-active {
        width: 22px;
        background: #fff;
    }
    @media (max-width: 600px) {
        .site-banner { height: 150px; }
        .site-banner-text { padding: 14px 14px 24px 8px; }
        .site-banner-headline { font-size: clamp(1rem, .875rem + .5556vw, 1.375rem); }
        .site-banner-image { width: 45%; }
        .site-banner--image .site-banner-image { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .site-banners-track { transition: none; }
    }
</style>

<script>
    (function () {
        function post(url) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch(url, {
                method: 'POST',
                keepalive: true,
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' }
            }).catch(() => {});
        }

        function init(root) {
            if (root.dataset.ready === '1') return;
            root.dataset.ready = '1';

            const track = root.querySelector('[data-site-banners-track]');
            const slides = Array.from(track.children);
            const dots = Array.from(root.querySelectorAll('[data-site-banners-dot]'));
            const count = slides.length;
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
            const vues = new Set();
            let index = 0;
            let timer = null;
            let startX = 0;
            let deltaX = 0;

            function vue(i) {
                const id = slides[i]?.dataset.publiciteId;
                if (!id || vues.has(id)) return;
                vues.add(id);
                post(`/spotlight/${id}/view`);
            }

            function go(i) {
                index = (i + count) % count;
                track.style.transform = `translateX(-${index * 100}%)`;
                dots.forEach((d, k) => d.classList.toggle('is-active', k === index));
                slides.forEach((s, k) => s.tabIndex = k === index ? 0 : -1);
                vue(index);
            }

            function stop() { clearInterval(timer); timer = null; }
            function start() {
                stop();
                if (count > 1 && !reduced.matches) {
                    timer = setInterval(() => go(index + 1), 6000);
                }
            }

            dots.forEach((d) => d.addEventListener('click', () => go(parseInt(d.dataset.siteBannersDot, 10))));
            slides.forEach((s) => {
                if (s.tagName === 'A') {
                    s.addEventListener('click', () => post(`/spotlight/${s.dataset.publiciteId}/click`));
                }
            });

            root.addEventListener('mouseenter', stop);
            root.addEventListener('mouseleave', start);
            root.addEventListener('focusin', stop);

            track.addEventListener('touchstart', (e) => {
                startX = e.changedTouches[0].clientX;
                deltaX = 0;
                stop();
            }, { passive: true });
            track.addEventListener('touchmove', (e) => {
                deltaX = e.changedTouches[0].clientX - startX;
            }, { passive: true });
            track.addEventListener('touchend', () => {
                if (Math.abs(deltaX) > 40) go(index + (deltaX < 0 ? 1 : -1));
                start();
            });

            go(0);
            start();
        }

        function boot() {
            document.querySelectorAll('[data-site-banners]').forEach(init);
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
