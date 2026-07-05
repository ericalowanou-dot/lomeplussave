@php
    $hideMobileNav = in_array(Route::currentRouteName(), [
        'login',
        'register',
        'password.request',
        'password.reset',
        'password.store',
        'password.email',
        'verification.notice',
        'verification.verify',
        'confirm.password',
    ], true);

    $currentRoute = Route::currentRouteName();

    $unreadMessages = 0;
    if (auth()->check()) {
        try {
            $unreadMessages = \App\Models\Message::whereHas('recipients', function ($q) {
                $q->where('recipient_id', auth()->id())->whereNull('read_at');
            })->count();
        } catch (\Throwable $e) {
            $unreadMessages = 0;
        }
    }

    $isHome = in_array($currentRoute, ['articles.index', 'article.search', 'articles.sub'], true);
    $isAnnonces = in_array($currentRoute, ['mes_annonces', 'mes_favoris', 'annonce.show', 'user.my-articles'], true);
    $isMessages = in_array($currentRoute, ['messages.inbox', 'messages.show', 'messages.compose', 'messages.compose.reply'], true);
    $isCreate = $currentRoute === 'articles.create';
@endphp

@if (!$hideMobileNav)
<nav class="mobile-bottom-nav" aria-label="Navigation principale mobile">
    <div class="mobile-bottom-nav__inner">
        <div class="mobile-bottom-nav__bar">
            <svg class="mobile-bottom-nav__shape" viewBox="0 0 400 76" preserveAspectRatio="none" aria-hidden="true">
                {{-- Encoche : épaules concaves (C) + arc central r=28 --}}
                <path fill="#ffffff" d="M 18 76
                    H 382
                    A 18 18 0 0 0 400 58
                    V 36
                    A 18 18 0 0 0 382 18
                    H 235
                    C 232 18 230 18 228.5 20
                    C 227 22.5 226 25.5 225.4 28.5
                    A 28 28 0 0 1 174.6 28.5
                    C 174 25.5 173 22.5 171.5 20
                    C 170 18 168 18 165 18
                    H 18
                    A 18 18 0 0 0 0 36
                    V 58
                    A 18 18 0 0 0 18 76
                    Z"/>
            </svg>

            <div class="mobile-bottom-nav__items">
                <a href="{{ route('articles.index') }}"
                   class="mobile-bottom-nav__item {{ $isHome ? 'is-active' : '' }}"
                   aria-label="Accueil"
                   @if($isHome) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 11.2L12 5l7 6.2V19a1 1 0 01-1 1h-4v-6h-4v6H6a1 1 0 01-1-1v-7.8z"/>
                    </svg>
                </a>

                @auth
                    <a href="{{ route('mes_annonces') }}"
                       class="mobile-bottom-nav__item {{ $isAnnonces ? 'is-active' : '' }}"
                       aria-label="Mes annonces"
                       @if($isAnnonces) aria-current="page" @endif>
                        <i class="bi bi-megaphone-fill" aria-hidden="true"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mobile-bottom-nav__item" aria-label="Mes annonces">
                        <i class="bi bi-megaphone-fill" aria-hidden="true"></i>
                    </a>
                @endauth

                <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>

                @auth
                    <a href="{{ route('messages.inbox') }}"
                       class="mobile-bottom-nav__item mobile-bottom-nav__item--has-badge {{ $isMessages ? 'is-active' : '' }}"
                       aria-label="Messages{{ $unreadMessages > 0 ? ' (' . $unreadMessages . ' non lus)' : '' }}"
                       @if($isMessages) aria-current="page" @endif>
                        <i class="bi bi-chat-left-text-fill" aria-hidden="true"></i>
                        @if($unreadMessages > 0)
                            <span class="mobile-bottom-nav__badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mobile-bottom-nav__item" aria-label="Messages">
                        <i class="bi bi-chat-left-text-fill" aria-hidden="true"></i>
                    </a>
                @endauth

                @auth
                    <button type="button"
                            id="mobile-bottom-nav-menu"
                            class="mobile-bottom-nav__item mobile-bottom-nav__item--btn"
                            aria-label="Menu">
                        <i class="bi bi-grid-fill" aria-hidden="true"></i>
                    </button>
                @else
                    <button type="button"
                            id="mobile-bottom-nav-menu"
                            class="mobile-bottom-nav__item mobile-bottom-nav__item--btn"
                            aria-label="Connexion">
                        <i class="bi bi-grid-fill" aria-hidden="true"></i>
                    </button>
                @endauth
            </div>
        </div>

        <button type="button"
                id="mobile-bottom-nav-fab"
                class="mobile-bottom-nav__fab {{ $isCreate ? 'is-active' : '' }}"
                aria-label="Publier une annonce"
                title="Publier une annonce">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
        </button>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var menuBtn = document.getElementById('mobile-bottom-nav-menu');
        if (menuBtn) {
            menuBtn.addEventListener('click', function () {
                var accountModalEl = document.getElementById('accountModal');
                var loginModal = document.getElementById('ouvrirModalConnexion');

                if (accountModalEl) {
                    accountModalEl.style.display = 'flex';
                } else if (loginModal) {
                    loginModal.click();
                } else {
                    window.location.href = '{{ route('login') }}';
                }
            });
        }

        var fabBtn = document.getElementById('mobile-bottom-nav-fab');
        if (fabBtn) {
            fabBtn.addEventListener('click', function () {
                var isAuthenticated = @auth true @else false @endauth;
                var modalAuth = document.getElementById('modal-auth');

                if (isAuthenticated) {
                    window.location.href = "{{ route('articles.create') }}";
                } else if (modalAuth) {
                    modalAuth.style.display = 'flex';
                } else {
                    window.location.href = "{{ route('login') }}";
                }
            });
        }
    });
</script>
@endif
