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
                       onclick="return handlePubliciteClick(event, {{ $publicite->id }});">
                        <img src="{{ asset($publicite->image) }}" 
                             alt="{{ $publicite->titre ?? 'Publicité' }}"
                             class="publicite-image"
                             loading="lazy"
                             onload="trackPubliciteView({{ $publicite->id }})"
                             onerror="this.src='{{ asset('images/placeholder.png') }}';">
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
            padding: 0;
            position: relative;
            z-index: 1;
        }

        .publicite-item {
            margin-bottom: 15px;
            width: 100%;
            position: relative;
            text-align: center;
        }

        .publicite-link {
            display: block;
            width: 100%;
            max-width: 100%;
            transition: transform 0.3s ease, opacity 0.3s ease;
            text-decoration: none;
            line-height: 0; /* Éviter les espaces autour de l'image */
        }

        .publicite-link:hover .publicite-image {
            transform: scale(1.01);
            opacity: 0.97;
        }

        .publicite-image {
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            display: block;
            margin: 0;
            background: #f8f9fa;
            pointer-events: auto;
            object-fit: cover;
        }

        /* Empêcher les clics en dehors de l'image */
        .publicite-item {
            text-align: center;
        }

        /* Styles spécifiques par position */
        .publicites-header {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto 30px auto;
            padding: 0;
        }

        .publicites-header .publicite-image {
            max-height: 200px;
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
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0;
        }

        .publicites-footer .publicite-image {
            max-height: 200px;
            object-fit: cover;
        }

        .publicites-entre_articles {
            width: 100%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0;
        }

        .publicites-entre_articles .publicite-image {
            max-height: 260px;
            object-fit: cover;
            width: 100%;
        }

        .publicites-homepage_top,
        .publicites-homepage_bottom {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0;
        }

        .publicites-homepage_top .publicite-image,
        .publicites-homepage_bottom .publicite-image {
            max-height: 320px;
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
                margin: 12px 0;
            }

            .publicites-sidebar {
                position: relative;
                top: 0;
            }

            .publicites-sidebar .publicite-image {
                max-height: 200px;
            }

            .publicites-entre_articles .publicite-image {
                max-height: 180px;
            }

            .publicites-homepage_top .publicite-image,
            .publicites-homepage_bottom .publicite-image {
                max-height: 210px;
            }

            /* Sur mobile, coins légèrement arrondis et sans ombre forte */
            .publicite-image {
                border-radius: 8px;
                box-shadow: none;
                background: transparent;
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
        
        // Fonction pour gérer les clics sur les publicités
        function handlePubliciteClick(event, publiciteId) {
            // Vérifier si le clic est directement sur l'image
            if (event.target && event.target.classList.contains('publicite-image')) {
                // Le clic est sur l'image, autoriser la navigation et tracker
                trackPubliciteClick(publiciteId);
                return true;
            } else {
                // Le clic n'est pas sur l'image, empêcher la navigation
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                return false;
            }
        }
        
        // Protection supplémentaire : empêcher les clics en dehors de l'image
        document.addEventListener('DOMContentLoaded', function() {
            // Empêcher les clics sur le conteneur de publicité qui ne sont pas sur l'image
            const publiciteItems = document.querySelectorAll('.publicite-item');
            publiciteItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    const link = item.querySelector('.publicite-link');
                    const image = item.querySelector('.publicite-image');
                    
                    // Si le clic n'est pas sur le lien ou l'image, arrêter la propagation
                    if (link && e.target !== link && e.target !== image && !link.contains(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                    }
                }, true);
            });
        });
    </script>
@endif

