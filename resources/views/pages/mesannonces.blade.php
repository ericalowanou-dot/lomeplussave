@extends('layouts.app2')

@section('title', 'Mes Annonces')

@section('content')




       
    

        <main>
        <div class="page-article" style=" margin-top: 150px;">

   

















            <!-- pour les articles -->
            <div class="album py-5 bg-body-tertiary">

                <a class="mes-annonces-home-btn" href="{{route('articles.index')}}" title="Retour à l'accueil">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Accueil</span>
                </a>

                <style>
                    /* Bouton d'accueil professionnel - juste en dessous de la navigation */
                    .mes-annonces-home-btn {
                        position: fixed;
                        top: 180px; /* Header (45px) + Navigation (130px) + petit espace (5px) */
                        left: 20px;
                        z-index: 10;
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

                    .mes-annonces-home-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);
                        color: #fff;
                        background: linear-gradient(135deg, #3b4fd8 0%, #2a3bc7 100%);
                    }

                    .mes-annonces-home-btn i {
                        font-size: 1rem;
                    }

                    @media (max-width: 768px) {
                        .mes-annonces-home-btn {
                            top: 180px; /* Même position sur mobile */
                            left: 10px;
                            padding: 8px 14px;
                            font-size: 0.8rem;
                        }
                        .mes-annonces-home-btn span {
                            display: none;
                        }
                    }


                    /* Styles pour les cartes d'articles */
                    .card {
                        position: relative;
                        overflow: hidden;
                    }

                    .article-status {
                        z-index: 10;
                    }

                    .article-status .badge {
                        font-size: 0.75rem;
                        padding: 0.25rem 0.5rem;
                    }

                    /* En-tête professionnel */
                    .mes-annonces-header {
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.05) 0%, rgba(81, 95, 251, 0.02) 100%);
                        border-radius: 20px;
                        padding: 28px 32px;
                        margin-bottom: 32px;
                        border: 1px solid rgba(81, 95, 251, 0.1);
                        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
                    }

                    .mes-annonces-header__top {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 24px;
                        flex-wrap: wrap;
                        gap: 16px;
                    }

                    .mes-annonces-header__title h2 {
                        margin: 0 0 6px 0;
                        font-size: 1.75rem;
                        font-weight: 700;
                        color: #111827;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }

                    .mes-annonces-header__title h2 i {
                        color: #515ffb;
                        font-size: 1.5rem;
                    }

                    .mes-annonces-header__subtitle {
                        margin: 0;
                        color: #6b7280;
                        font-size: 0.95rem;
                    }

                    .mes-annonces-header__actions {
                        display: flex;
                        gap: 12px;
                        align-items: center;
                    }

                    .btn-create {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 20px;
                        font-weight: 600;
                        border-radius: 12px;
                        box-shadow: 0 4px 12px rgba(81, 95, 251, 0.25);
                        transition: all 0.3s ease;
                    }

                    .btn-create:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 16px rgba(81, 95, 251, 0.35);
                    }

                    /* Statistiques */
                    .mes-annonces-stats {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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
                        text-decoration: none;
                        color: inherit;
                        position: relative;
                        cursor: pointer;
                    }

                    .stat-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
                        text-decoration: none;
                        color: inherit;
                    }

                    .stat-card--filter {
                        cursor: pointer;
                    }

                    .stat-card--filter:hover {
                        border-color: rgba(81, 95, 251, 0.3);
                    }

                    .stat-card--active {
                        border: 2px solid #515ffb;
                        box-shadow: 0 4px 16px rgba(81, 95, 251, 0.2);
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.05) 0%, rgba(81, 95, 251, 0.02) 100%);
                    }

                    .stat-card--active:hover {
                        border-color: #3b4fd8;
                        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.3);
                    }

                    .stat-card__badge {
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        background: #515ffb;
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 0.75rem;
                        box-shadow: 0 2px 8px rgba(81, 95, 251, 0.3);
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
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.15), rgba(81, 95, 251, 0.08));
                        color: #515ffb;
                    }

                    .stat-card--pending .stat-card__icon {
                        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.08));
                        color: #f59e0b;
                    }

                    .stat-card--approved .stat-card__icon {
                        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.08));
                        color: #10b981;
                    }

                    .stat-card--blocked .stat-card__icon {
                        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.08));
                        color: #ef4444;
                    }

                    .stat-card--boosted .stat-card__icon {
                        background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.08));
                        color: #f97316;
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

                    /* Animation pour l'affichage des filtres */
                    #filters-container {
                        transition: all 0.3s ease;
                        overflow: hidden;
                    }

                    /* Filtres professionnels */
                    .mes-annonces-filters {
                        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                        border-radius: 20px;
                        padding: 32px;
                        border: 1px solid rgba(81, 95, 251, 0.12);
                        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.08);
                        position: relative;
                        overflow: hidden;
                    }

                    .mes-annonces-filters::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 4px;
                        background: linear-gradient(90deg, #515ffb 0%, #3b4fd8 100%);
                    }

                    .mes-annonces-filters__header {
                        margin-bottom: 24px;
                        padding-bottom: 16px;
                        border-bottom: 2px solid rgba(81, 95, 251, 0.1);
                    }

                    .mes-annonces-filters__title {
                        margin: 0;
                        font-size: 1.25rem;
                        font-weight: 600;
                        color: #111827;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }

                    .mes-annonces-filters__title i {
                        color: #515ffb;
                    }

                    .mes-annonces-filters__label {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-weight: 600;
                        color: #374151;
                        font-size: 0.9rem;
                        margin-bottom: 8px;
                    }

                    .mes-annonces-filters__label i {
                        color: #515ffb;
                        font-size: 0.85rem;
                    }

                    .mes-annonces-filters__select,
                    .mes-annonces-filters__input {
                        border-radius: 12px;
                        border: 2px solid rgba(15, 23, 42, 0.1);
                        padding: 12px 16px;
                        font-size: 0.95rem;
                        transition: all 0.2s ease;
                        background: #fff;
                    }

                    .mes-annonces-filters__select:hover,
                    .mes-annonces-filters__input:hover {
                        border-color: rgba(81, 95, 251, 0.3);
                    }

                    .mes-annonces-filters__select:focus,
                    .mes-annonces-filters__input:focus {
                        border-color: #515ffb;
                        box-shadow: 0 0 0 4px rgba(81, 95, 251, 0.15);
                        outline: none;
                        background: #fff;
                    }

                    .mes-annonces-filters__actions {
                        display: flex;
                        gap: 12px;
                        margin-top: 8px;
                        padding-top: 16px;
                        border-top: 1px solid rgba(15, 23, 42, 0.08);
                    }

                    .mes-annonces-filters__btn {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 20px;
                        border-radius: 12px;
                        font-weight: 500;
                        transition: all 0.3s ease;
                    }

                    .mes-annonces-filters__btn:hover {
                        transform: translateY(-1px);
                    }

                    /* Styles responsifs pour mobile */
                    @media (max-width: 768px) {
                        .container {
                            padding: 0 10px;
                        }
                        
                        .mes-annonces-header {
                            padding: 20px;
                        }

                        .mes-annonces-header__top {
                            flex-direction: column;
                        }

                        .mes-annonces-header__actions {
                            width: 100%;
                            flex-direction: column;
                        }

                        .mes-annonces-header__actions .btn {
                            width: 100%;
                            justify-content: center;
                        }

                        .mes-annonces-stats {
                            grid-template-columns: repeat(2, 1fr);
                            gap: 12px;
                        }

                        .stat-card {
                            padding: 16px;
                        }

                        .stat-card__value {
                            font-size: 1.5rem;
                        }

                        .mes-annonces-filters {
                            padding: 20px;
                        }

                        .mes-annonces-filters__actions {
                            flex-direction: column;
                        }

                        .mes-annonces-filters__btn {
                            width: 100%;
                            justify-content: center;
                        }
                    }

                    .mes-annonces-empty {
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

                    .mes-annonces-empty__icon {
                        width: 72px;
                        height: 72px;
                        border-radius: 18px;
                        display: grid;
                        place-items: center;
                        background: rgba(81, 95, 251, 0.12);
                        color: #515ffb;
                        font-size: 32px;
                    }

                    .mes-annonces-empty__title {
                        font-size: 1.6rem;
                        font-weight: 700;
                        color: #111827;
                        margin: 0;
                    }

                    .mes-annonces-empty__subtitle {
                        color: #475569;
                        font-size: 1rem;
                        max-width: 420px;
                        margin: 0 auto;
                        line-height: 1.6;
                    }

                    .mes-annonces-empty__actions {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 12px;
                        justify-content: center;
                    }

                    .mes-annonces-empty__cta {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 22px;
                        border-radius: 999px;
                        background: linear-gradient(135deg, #ff6b6b, #f97316);
                        color: #fff;
                        font-weight: 600;
                        text-decoration: none;
                        box-shadow: 0 12px 30px rgba(249, 115, 22, 0.25);
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }

                    .mes-annonces-empty__cta:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 16px 36px rgba(249, 115, 22, 0.35);
                        color: #fff;
                    }

                    .mes-annonces-empty__secondary {
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

                    .mes-annonces-empty__secondary:hover {
                        background: rgba(15, 23, 42, 0.04);
                        border-color: rgba(15, 23, 42, 0.18);
                        color: #1f2937;
                    }

                    @media (max-width: 576px) {
                        .mes-annonces-empty {
                            padding: 48px 24px;
                        }

                        .mes-annonces-empty__title {
                            font-size: 1.35rem;
                        }

                        .mes-annonces-empty__subtitle {
                            font-size: 0.95rem;
                        }
                    }
                    
                    /* Styles pour la pagination */
                    .mes-annonces-pagination {
                        margin: 32px 0;
                    }
                    
                    .mes-annonces-pagination .pagination {
                        justify-content: center;
                        flex-wrap: wrap;
                        gap: 8px;
                    }
                    
                    .mes-annonces-pagination .pagination .page-link {
                        border-radius: 8px;
                        padding: 10px 16px;
                        color: #515ffb;
                        border: 1px solid rgba(81, 95, 251, 0.2);
                        transition: all 0.2s ease;
                    }
                    
                    .mes-annonces-pagination .pagination .page-link:hover {
                        background: linear-gradient(135deg, rgba(81, 95, 251, 0.1), rgba(81, 95, 251, 0.05));
                        border-color: #515ffb;
                        transform: translateY(-1px);
                    }
                    
                    .mes-annonces-pagination .pagination .page-item.active .page-link {
                        background: linear-gradient(135deg, #515ffb, #3b4fd8);
                        border-color: #515ffb;
                        color: white;
                        box-shadow: 0 4px 12px rgba(81, 95, 251, 0.3);
                    }
                    
                    .mes-annonces-pagination .pagination .page-item.disabled .page-link {
                        color: #6b7280;
                        background-color: #f3f4f6;
                        border-color: #e5e7eb;
                        cursor: not-allowed;
                    }
                    
                    /* Styles pour la modale de partage */
                    .share-modal .close:hover {
                        color: #000;
                    }
                    
                    .share-modal .copy-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);
                    }
                    
                    .share-modal .share-icons a:hover {
                        transform: scale(1.1);
                    }
                </style>

                <div class="container mt-4">
                    @php
                        $hasActiveFilters = request('status') || request('boosted') || request('categorie') || request('date_filter') || request('prix_min') || request('prix_max') || request('ville');
                    @endphp
                    
                    @if($articles->isEmpty() && !$hasActiveFilters)
                        <div class="mes-annonces-empty">
                            <div class="mes-annonces-empty__icon">
                                <i class="bi bi-collection"></i>
                            </div>
                            <h2 class="mes-annonces-empty__title">Aucune annonce en ligne pour le moment</h2>
                            <p class="mes-annonces-empty__subtitle">
                                Publie ta première annonce pour toucher des centaines d’acheteurs. 
                                C’est rapide, sécurisé et entièrement gratuit.
                            </p>
                            <div class="mes-annonces-empty__actions">
                                <a href="{{ route('articles.create') }}" class="mes-annonces-empty__cta">
                                    <i class="bi bi-plus-circle"></i>
                                    Créer une annonce
                                </a>
                                <a href="{{ route('about') }}" class="mes-annonces-empty__secondary">
                                    Besoin d'aide&nbsp;?
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- En-tête avec statistiques -->
                        <div class="mes-annonces-header">
                            <div class="mes-annonces-header__top">
                                <div class="mes-annonces-header__title">
                                    <h2><i class="bi bi-collection"></i> Mes Annonces</h2>
                                    <p class="mes-annonces-header__subtitle">Gérez toutes vos annonces en un seul endroit</p>
                                </div>
                                <div class="mes-annonces-header__actions">
                                    <a href="{{ route('articles.create') }}" class="btn btn-primary btn-create">
                                        <i class="bi bi-plus-circle"></i>
                                        <span>Nouvelle annonce</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Statistiques / Filtres -->
                            <div class="mes-annonces-stats">
                                <a href="{{ route('mes_annonces') }}" class="stat-card stat-card--total stat-card--filter {{ !request('status') && !request('boosted') ? 'stat-card--active' : '' }}" data-filter-type="all">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-collection"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div>
                                        <div class="stat-card__label">Total</div>
                                    </div>
                                    @if(!request('status') && !request('boosted'))
                                        <div class="stat-card__badge">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ route('mes_annonces', ['status' => 'pending']) }}" class="stat-card stat-card--pending stat-card--filter {{ request('status') == 'pending' ? 'stat-card--active' : '' }}" data-filter-type="status" data-filter-value="pending">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['pending'] ?? 0 }}</div>
                                        <div class="stat-card__label">En attente</div>
                                    </div>
                                    @if(request('status') == 'pending')
                                        <div class="stat-card__badge">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ route('mes_annonces', ['status' => 'approved']) }}" class="stat-card stat-card--approved stat-card--filter {{ request('status') == 'approved' ? 'stat-card--active' : '' }}" data-filter-type="status" data-filter-value="approved">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['approved'] ?? 0 }}</div>
                                        <div class="stat-card__label">Approuvées</div>
                                    </div>
                                    @if(request('status') == 'approved')
                                        <div class="stat-card__badge">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ route('mes_annonces', ['status' => 'blocked']) }}" class="stat-card stat-card--blocked stat-card--filter {{ request('status') == 'blocked' ? 'stat-card--active' : '' }}" data-filter-type="status" data-filter-value="blocked">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['blocked'] ?? 0 }}</div>
                                        <div class="stat-card__label">Bloquées</div>
                                    </div>
                                    @if(request('status') == 'blocked')
                                        <div class="stat-card__badge">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ route('mes_annonces', ['boosted' => '1']) }}" class="stat-card stat-card--boosted stat-card--filter {{ request('boosted') == '1' ? 'stat-card--active' : '' }}" data-filter-type="boosted" data-filter-value="1">
                                    <div class="stat-card__icon">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="stat-card__content">
                                        <div class="stat-card__value">{{ $stats['boosted'] ?? 0 }}</div>
                                        <div class="stat-card__label">Boostées</div>
                                    </div>
                                    @if(request('boosted') == '1')
                                        <div class="stat-card__badge">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    @endif
                                </a>
                            </div>
                            
                            <!-- Bouton pour afficher les filtres avancés -->
                            <div style="text-align: center; margin-top: 16px;">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggle-advanced-filters" style="border-radius: 12px; padding: 8px 16px;">
                                    <i class="bi bi-sliders"></i>
                                    <span>Filtres avancés</span>
                                </button>
                            </div>
                        </div>

                        <!-- Filtres avancés (masqués par défaut) -->
                        <div class="row mb-4" id="filters-container" style="display: none;">
                            <div class="col-12">
                                <div class="mes-annonces-filters">
                                    <div class="mes-annonces-filters__header">
                                        <h5 class="mes-annonces-filters__title">
                                            <i class="bi bi-funnel-fill"></i>
                                            Filtres avancés
                                        </h5>
                                        <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 0.9rem;">
                                            Utilisez les cartes de statistiques ci-dessus pour filtrer rapidement, ou utilisez ces filtres pour des recherches plus précises.
                                        </p>
                                    </div>
                                    <form method="GET" action="{{ route('mes_annonces') }}" id="filter-form" class="mes-annonces-filters__form">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label for="status" class="mes-annonces-filters__label">
                                                    <i class="bi bi-info-circle"></i>
                                                    État de l'annonce
                                                </label>
                                                <select class="form-select mes-annonces-filters__select" name="status" id="status">
                                                    <option value="">Tous les états</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="boosted" class="mes-annonces-filters__label">
                                                    <i class="bi bi-lightning-charge"></i>
                                                    Produit boosté
                                                </label>
                                                <select class="form-select mes-annonces-filters__select" name="boosted" id="boosted">
                                                    <option value="">Tous</option>
                                                    <option value="1" {{ request('boosted') == '1' ? 'selected' : '' }}>Boostés uniquement</option>
                                                    <option value="0" {{ request('boosted') == '0' ? 'selected' : '' }}>Non boostés</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="date_filter" class="mes-annonces-filters__label">
                                                    <i class="bi bi-calendar"></i>
                                                    Période
                                                </label>
                                                <select class="form-select mes-annonces-filters__select" name="date_filter" id="date_filter">
                                                    <option value="">Toutes les périodes</option>
                                                    <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                                                    <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>Ce mois</option>
                                                    <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Période personnalisée</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3" id="date_from_container" style="display: none;">
                                                <label for="date_from" class="mes-annonces-filters__label">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Date de début
                                                </label>
                                                <input type="date" class="form-control mes-annonces-filters__input" name="date_from" id="date_from" value="{{ request('date_from') }}">
                                            </div>
                                            <div class="col-md-3" id="date_to_container" style="display: none;">
                                                <label for="date_to" class="mes-annonces-filters__label">
                                                    <i class="bi bi-calendar-event-fill"></i>
                                                    Date de fin
                                                </label>
                                                <input type="date" class="form-control mes-annonces-filters__input" name="date_to" id="date_to" value="{{ request('date_to') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mes-annonces-filters__actions">
                                                    <button type="submit" class="btn btn-primary mes-annonces-filters__btn">
                                                        <i class="bi bi-search"></i>
                                                        Appliquer les filtres
                                                    </button>
                                                    <a href="{{ route('mes_annonces') }}" class="btn btn-outline-secondary mes-annonces-filters__btn">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                        Réinitialiser
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 "> -->
                        <div id="mesannonces-results" data-context="mesannonces">
                            @include('partials.annonces-list', ['articles' => $articles])
                        </div>
                        
                        <!-- Pagination avec conservation des filtres -->
                        @if($articles->hasPages())
                            <div class="d-flex justify-content-center mt-5 mb-4">
                                <div class="mes-annonces-pagination">
                                    {{ $articles->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                
                <!-- Modale de partage (unique, hors de la boucle) -->
                <div id="share-modal" class="share-modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
                    <div class="share-modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 30px; border: 1px solid #888; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                        <span class="close" onclick="closeShareModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 20px;">&times;</span>
                        <h3 style="margin-top: 0; color: #333;">Partager ce lien</h3>
                        <input type="text" id="share-url" readonly style="width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background-color: #f9f9f9;">
                        <button onclick="copyToClipboard()" class="copy-btn" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #515ffb, #3b4fd8); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 15px; transition: transform 0.2s;">
                            <i class="fas fa-copy"></i> Copier le lien
                        </button>
                        <div class="share-icons" style="display: flex; gap: 12px; justify-content: center;">
                            <a id="facebook-share" target="_blank" class="facebook" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #1877f2; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a id="whatsapp-share" target="_blank" class="whatsapp" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #25d366; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a id="twitter-share" target="_blank" class="twitter" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: #1da1f2; color: white; text-decoration: none; font-size: 20px; transition: transform 0.2s;">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                        </div>
                    </div>
                </div>
           
</div>

        </main>





         


     


     




          
            <script src="{{asset('js/bootstrap.bundle.min.js')}}" defer></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gestion du bouton filtres avancés
                const toggleAdvancedFiltersBtn = document.getElementById('toggle-advanced-filters');
                const filtersContainer = document.getElementById('filters-container');
                const dateFilter = document.getElementById('date_filter');
                const dateFromContainer = document.getElementById('date_from_container');
                const dateToContainer = document.getElementById('date_to_container');

                if (toggleAdvancedFiltersBtn && filtersContainer) {
                    function toggleAdvancedFilters() {
                        if (filtersContainer.style.display === 'none' || !filtersContainer.style.display) {
                            filtersContainer.style.display = 'block';
                            toggleAdvancedFiltersBtn.innerHTML = '<i class="bi bi-x-circle"></i> <span>Masquer les filtres avancés</span>';
                            toggleAdvancedFiltersBtn.classList.remove('btn-outline-secondary');
                            toggleAdvancedFiltersBtn.classList.add('btn-secondary');
                            
                            // Scroll automatique vers les filtres
                            setTimeout(() => {
                                const headerOffset = 100;
                                const elementPosition = filtersContainer.getBoundingClientRect().top;
                                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                                
                                window.scrollTo({
                                    top: offsetPosition,
                                    behavior: 'smooth'
                                });
                            }, 100);
                        } else {
                            filtersContainer.style.display = 'none';
                            toggleAdvancedFiltersBtn.innerHTML = '<i class="bi bi-sliders"></i> <span>Filtres avancés</span>';
                            toggleAdvancedFiltersBtn.classList.remove('btn-secondary');
                            toggleAdvancedFiltersBtn.classList.add('btn-outline-secondary');
                        }
                    }

                    toggleAdvancedFiltersBtn.addEventListener('click', toggleAdvancedFilters);
                }

                if (dateFilter && dateFromContainer && dateToContainer) {
                    function toggleDateFields() {
                        if (dateFilter.value === 'custom') {
                            dateFromContainer.style.display = 'block';
                            dateToContainer.style.display = 'block';
                        } else {
                            dateFromContainer.style.display = 'none';
                            dateToContainer.style.display = 'none';
                        }
                    }

                    dateFilter.addEventListener('change', toggleDateFields);
                    toggleDateFields();

                    // Ouvrir les filtres avancés si des paramètres de date sont présents dans l'URL
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('date_filter') || urlParams.has('date_from') || urlParams.has('date_to')) {
                        if (filtersContainer && toggleAdvancedFiltersBtn) {
                            filtersContainer.style.display = 'block';
                            toggleAdvancedFiltersBtn.innerHTML = '<i class="bi bi-x-circle"></i> <span>Masquer les filtres avancés</span>';
                            toggleAdvancedFiltersBtn.classList.remove('btn-outline-secondary');
                            toggleAdvancedFiltersBtn.classList.add('btn-secondary');
                        }
                    }
                }

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

                // Gestion du double-clic pour réinitialiser le filtre
                const statFilterCards = document.querySelectorAll('.stat-card--filter');
                statFilterCards.forEach(card => {
                    card.addEventListener('dblclick', function(e) {
                        e.preventDefault();
                        window.location.href = '{{ route("mes_annonces") }}';
                    });
                });
                
                // Fonctions pour la modale de partage
                window.openShareModal = function(articleUrl) {
                    const modal = document.getElementById('share-modal');
                    const urlInput = document.getElementById('share-url');
                    const facebookShare = document.getElementById('facebook-share');
                    const whatsappShare = document.getElementById('whatsapp-share');
                    const twitterShare = document.getElementById('twitter-share');
                    
                    if (urlInput) urlInput.value = articleUrl;
                    if (facebookShare) facebookShare.href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(articleUrl);
                    if (whatsappShare) whatsappShare.href = "https://wa.me/?text=" + encodeURIComponent(articleUrl);
                    if (twitterShare) twitterShare.href = "https://twitter.com/intent/tweet?url=" + encodeURIComponent(articleUrl);
                    if (modal) modal.style.display = 'block';
                };
                
                window.closeShareModal = function() {
                    const modal = document.getElementById('share-modal');
                    if (modal) modal.style.display = 'none';
                };
                
                window.copyToClipboard = function() {
                    const urlInput = document.getElementById("share-url");
                    if (!urlInput) return;
                    
                    urlInput.select();
                    urlInput.setSelectionRange(0, 99999); // Mobile support
                    
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(urlInput.value).then(() => {
                            alert("Lien copié dans le presse-papiers !");
                        }).catch(() => {
                            // Fallback pour les navigateurs plus anciens
                            document.execCommand('copy');
                            alert("Lien copié dans le presse-papiers !");
                        });
                    } else {
                        // Fallback pour les navigateurs plus anciens
                        document.execCommand('copy');
                        alert("Lien copié dans le presse-papiers !");
                    }
                };
                
                // Fermer la modale si on clique à l'extérieur
                const shareModal = document.getElementById("share-modal");
                if (shareModal) {
                    shareModal.addEventListener('click', function(event) {
                        if (event.target === shareModal) {
                            closeShareModal();
                        }
                    });
                }
            });
        </script>

        <!-- Bouton Scroll to Top -->
        @include('components.scroll-to-top')

@endsection


