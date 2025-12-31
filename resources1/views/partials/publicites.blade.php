@php
    use App\Models\Publicite;
    
    // Récupérer les publicités actives pour la position spécifiée
    try {
        $publicites = Publicite::active()
            ->byPosition($position ?? 'entre_articles')
            ->orderBy('ordre', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    } catch (\Exception $e) {
        // En cas d'erreur (ex: table n'existe pas encore), retourner une collection vide
        $publicites = collect([]);
        \Log::error('Erreur dans partial publicites: ' . $e->getMessage());
    }
@endphp

@if($publicites && $publicites->count() > 0)
    <div class="publicites-container publicites-{{ $position ?? 'entre_articles' }}" data-position="{{ $position ?? 'entre_articles' }}">
        @foreach($publicites as $publicite)
            <div class="publicite-item" 
                 data-publicite-id="{{ $publicite->id }}"
                 data-lien="{{ $publicite->lien_url }}">
                @if($publicite->lien_url)
                    <a href="{{ $publicite->lien_url }}" 
                       target="_blank" 
                       rel="nofollow"
                       class="publicite-link"
                       onclick="trackPubliciteClick({{ $publicite->id }})">
                        <img src="{{ asset($publicite->image) }}" 
                             alt="{{ $publicite->titre ?? 'Publicité' }}"
                             class="publicite-image"
                             loading="lazy"
                             onload="trackPubliciteView({{ $publicite->id }})"
                             onerror="this.src='{{ asset('images/placeholder.png') }}';"
                    </a>
                @else
                    <img src="{{ asset($publicite->image) }}" 
                         alt="{{ $publicite->titre ?? 'Publicité' }}"
                         class="publicite-image"
                         loading="lazy"
                         onload="trackPubliciteView({{ $publicite->id }})"
                         onerror="this.src='{{ asset('images/placeholder.png') }}';">
                @endif
            </div>
        @endforeach
    </div>

    <style>
        .publicites-container {
            margin: 20px 0;
            width: 100%;
            padding: 10px 0;
            position: relative;
            z-index: 1;
        }

        .publicite-item {
            margin-bottom: 15px;
            width: 100%;
            position: relative;
        }

        .publicite-link {
            display: block;
            width: 100%;
            transition: transform 0.3s ease, opacity 0.3s ease;
            text-decoration: none;
        }

        .publicite-link:hover {
            transform: scale(1.02);
            opacity: 0.9;
        }

        .publicite-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto;
            background: #f8f9fa;
        }

        /* Styles spécifiques par position */
        .publicites-header {
            max-width: 1200px;
            margin: 20px auto 30px auto;
            padding: 15px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .publicites-header .publicite-image {
            max-height: 150px;
            object-fit: cover;
            width: 100%;
        }

        .publicites-sidebar {
            position: sticky;
            top: 180px;
        }

        .publicites-sidebar .publicite-image {
            max-height: 300px;
            object-fit: cover;
        }

        .publicites-footer {
            max-width: 1200px;
            margin: 20px auto;
        }

        .publicites-footer .publicite-image {
            max-height: 150px;
            object-fit: contain;
        }

        .publicites-entre_articles {
            max-width: 100%;
            margin: 30px auto;
            padding: 15px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .publicites-entre_articles .publicite-image {
            max-height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .publicites-homepage_top,
        .publicites-homepage_bottom {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .publicites-homepage_top .publicite-image,
        .publicites-homepage_bottom .publicite-image {
            max-height: 250px;
            object-fit: cover;
            width: 100%;
        }

        .publicites-homepage_top {
            margin-top: 30px !important;
        }

        .publicites-homepage_bottom {
            margin-bottom: 30px !important;
        }

        @media (max-width: 768px) {
            .publicites-container {
                margin: 15px 0;
            }

            .publicites-sidebar {
                position: relative;
                top: 0;
            }

            .publicites-sidebar .publicite-image {
                max-height: 200px;
            }

            .publicites-entre_articles .publicite-image {
                max-height: 150px;
            }

            .publicites-homepage_top .publicite-image,
            .publicites-homepage_bottom .publicite-image {
                max-height: 180px;
            }
        }
    </style>

    <script>
        // Fonction pour tracker les vues
        function trackPubliciteView(publiciteId) {
            fetch(`/publicite/${publiciteId}/view`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            }).catch(err => console.error('Erreur tracking vue:', err));
        }

        // Fonction pour tracker les clics
        function trackPubliciteClick(publiciteId) {
            fetch(`/publicite/${publiciteId}/click`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            }).catch(err => console.error('Erreur tracking clic:', err));
        }
    </script>
@endif

