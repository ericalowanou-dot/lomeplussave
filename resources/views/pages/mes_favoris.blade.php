@extends('layouts.app2')

@section('title', 'Mes Favoris')

@section('content')
  
        <main>
        <div class="page-article" style=" margin-top: 150px;">

            <!-- pour les articles -->
            <div class="album py-5 bg-body-tertiary">

                <a class="mes-favoris-home-btn" href="{{route('articles.index')}}" title="Retour à l'accueil">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Accueil</span>
                </a>

                <style>
                    /* Bouton d'accueil professionnel - juste en dessous de la navigation */
                    .mes-favoris-home-btn {
                        position: fixed;
                        top: 180px; /* Header (45px) + Navigation (130px) + petit espace (5px) */
                        left: 20px;
                        z-index: 10; /* Bas pour ne pas passer au-dessus des modals */
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 18px;
                        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);
                        color: #fff;
                        text-decoration: none;
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 0.9rem;
                        box-shadow: 0 4px 16px rgba(81, 95, 251, 0.3);
                        transition: all 0.3s ease;
                        border: none;
                    }

                    .mes-favoris-home-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);
                        color: #fff;
                        background: linear-gradient(135deg, #3b4fd8 0%, #2a3bc7 100%);
                    }

                    .mes-favoris-home-btn i {
                        font-size: 1rem;
                    }

                    @media (max-width: 768px) {
                        .mes-favoris-home-btn {
                            top: 180px;
                            left: 10px;
                            padding: 8px 14px;
                            font-size: 0.8rem;
                        }
                        .mes-favoris-home-btn span {
                            display: none;
                        }
                    }


                    /* En-tête professionnel */
                    .mes-favoris-header {
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.05) 0%, rgba(81, 95, 251, 0.02) 100%);
                        border-radius: 20px;
                        padding: 28px 32px;
                        margin-bottom: 32px;
                        border: 1px solid rgba(81, 95, 251, 0.1);
                        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
                    }

                    .mes-favoris-header__top {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 24px;
                        flex-wrap: wrap;
                        gap: 16px;
                    }

                    .mes-favoris-header__title h2 {
                        margin: 0 0 6px 0;
                        font-size: 1.75rem;
                        font-weight: 700;
                        color: #111827;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }

                    .mes-favoris-header__title h2 i {
                        color: #ef4444;
                        font-size: 1.5rem;
                    }

                    .mes-favoris-header__subtitle {
                        margin: 0 0 16px 0;
                        color: #6b7280;
                        font-size: 0.95rem;
                        line-height: 1.6;
                    }

                    .mes-favoris-header__features {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                        display: flex;
                        flex-direction: column;
                        gap: 12px;
                    }

                    .mes-favoris-header__features li {
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        color: #475569;
                        font-size: 0.9rem;
                        line-height: 1.6;
                    }

                    .mes-favoris-header__features li i {
                        color: #515ffb;
                        font-size: 1rem;
                        margin-top: 2px;
                        flex-shrink: 0;
                    }

                    .mes-favoris-header__features li strong {
                        color: #1f2937;
                        font-weight: 600;
                    }

                    @media (max-width: 768px) {
                        .mes-favoris-header__features {
                            gap: 10px;
                        }

                        .mes-favoris-header__features li {
                            font-size: 0.85rem;
                        }
                    }

                    /* Statistiques */
                    .mes-favoris-stats {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 16px;
                    }

                    .stat-card {
                        background: #fff;
                        border-radius: 16px;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        border: 1px solid rgba(15, 23, 42, 0.08);
                        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
                        transition: all 0.3s ease;
                    }

                    .stat-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
                    }

                    .stat-card__icon {
                        width: 48px;
                        height: 48px;
                        border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                        font-size: 1.5rem;
                        flex-shrink: 0;
                    }

                    .stat-card--total .stat-card__icon {
                        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.08));
                        color: #ef4444;
                    }

                    .stat-card__content {
                        flex: 1;
                    }

                    .stat-card__value {
                        font-size: 1.75rem;
                        font-weight: 700;
                        color: #111827;
                        line-height: 1.2;
                    }

                    .stat-card__label {
                        font-size: 0.85rem;
                        color: #6b7280;
                        font-weight: 500;
                        margin-top: 4px;
                    }

                    /* État vide professionnel */
                    .mes-favoris-empty {
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.08), rgba(81, 95, 251, 0));
                        border: 1px solid rgba(81, 95, 251, 0.15);
                        border-radius: 18px;
                        padding: 64px 32px;
                        text-align: center;
                    display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        gap: 18px;
                        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
                    }

                    .mes-favoris-empty__icon {
                        width: 72px;
                        height: 72px;
                        border-radius: 18px;
                        display: grid;
                        place-items: center;
                        background: rgba(239, 68, 68, 0.12);
                        color: #ef4444;
                        font-size: 32px;
                    }

                    .mes-favoris-empty__title {
                        font-size: 1.6rem;
                        font-weight: 700;
                        color: #111827;
                        margin: 0;
                    }

                    .mes-favoris-empty__subtitle {
                        color: #475569;
                        font-size: 1rem;
                        max-width: 420px;
                        margin: 0 auto;
                        line-height: 1.6;
                    }

                    .mes-favoris-empty__actions {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 12px;
                        justify-content: center;
                    }

                    .mes-favoris-empty__cta {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 22px;
                        border-radius: 999px;
                        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);
                        color: #fff;
                        font-weight: 600;
                        text-decoration: none;
                        box-shadow: 0 12px 30px rgba(81, 95, 251, 0.25);
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }

                    .mes-favoris-empty__cta:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 16px 36px rgba(81, 95, 251, 0.35);
                        color: #fff;
                    }

                    .mes-favoris-empty__secondary {
                        display: inline-flex;
                        align-items: center;
                        padding: 10px 16px;
                        border-radius: 999px;
                        border: 1px solid rgba(15, 23, 42, 0.12);
                        color: #1f2937;
                        font-weight: 500;
                        text-decoration: none;
                        transition: background 0.2s ease, border-color 0.2s ease;
                    }

                    .mes-favoris-empty__secondary:hover {
                        background: rgba(15, 23, 42, 0.04);
                        border-color: rgba(15, 23, 42, 0.18);
                        color: #1f2937;
                    }

                    @media (max-width: 768px) {
                        .mes-favoris-header {
                            padding: 20px;
                        }

                        .mes-favoris-header__top {
                            flex-direction: column;
                        }

                        .mes-favoris-stats {
                            grid-template-columns: 1fr;
                            gap: 12px;
                        }

                        .stat-card {
                            padding: 16px;
                        }

                        .stat-card__value {
                            font-size: 1.5rem;
                        }

                        .mes-favoris-empty {
                            padding: 48px 24px;
                        }

                        .mes-favoris-empty__title {
                            font-size: 1.35rem;
                        }

                        .mes-favoris-empty__subtitle {
                            font-size: 0.95rem;
                        }
                    }
                </style>

                <div class="container mt-4">
                    @if($favoris->isEmpty())
                        <div class="mes-favoris-empty">
                            <div class="mes-favoris-empty__icon">
                                <i class="bi bi-heart"></i>
                            </div>
                            <h2 class="mes-favoris-empty__title">Aucun favori pour le moment</h2>
                            <p class="mes-favoris-empty__subtitle">
                                Commence à liker des articles pour les retrouver facilement ici. 
                                Tes favoris seront sauvegardés et accessibles à tout moment.
                            </p>
                            <div class="mes-favoris-empty__actions">
                                <a href="{{ route('articles.index') }}" class="mes-favoris-empty__cta">
                                    <i class="bi bi-search"></i>
                                    Découvrir des articles
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- En-tête avec statistiques -->
                        <div class="mes-favoris-header">
                            <div class="mes-favoris-header__top">
                                <div class="mes-favoris-header__title">
                                    <h2><i class="bi bi-heart-fill"></i> Mes Favoris</h2>
                                    <p class="mes-favoris-header__subtitle">
                                        Retrouve facilement tous tes articles likés. Les favoris te permettent de :
                                    </p>
                                    <ul class="mes-favoris-header__features">
                                        <li><i class="bi bi-check-circle-fill"></i> <strong>Retrouver rapidement</strong> les articles qui t'ont plu</li>
                                        <li><i class="bi bi-check-circle-fill"></i> <strong>Contacter le vendeur</strong> à tout moment depuis tes favoris</li>
                                        <li><i class="bi bi-check-circle-fill"></i> <strong>Comparer les prix</strong> et faire ton choix en toute sérénité</li>
                                        <li><i class="bi bi-check-circle-fill"></i> <strong>Accéder facilement</strong> aux détails de tes articles préférés</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Statistiques -->
                            <div class="mes-favoris-stats">
                                <div class="stat-card stat-card--total">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-heart-fill"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div>
                                        <div class="stat-card__label">Articles favoris</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des favoris -->
                        <div id="favoris-results" data-context="favoris">
                            @include('partials.favoris-list', ['articles' => $favoris])
                        </div>

                    @endif
                </div>
          
            </div>

        </div>

        </main>

            <!-- Bouton Scroll to Top -->
            @include('components.scroll-to-top')

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animation des cartes de statistiques au chargement
                const statCards = document.querySelectorAll('.stat-card');
                statCards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                    });
                });
            </script>

@endsection
