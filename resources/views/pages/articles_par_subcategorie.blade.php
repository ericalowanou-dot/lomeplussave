@extends('layouts.app2')

@section('title', isset($selectedSubcategory) ? ($selectedSubcategory->nom . ' - Articles') : 'Articles par sous-catégorie')

@section('content')
<main>
    <style>
        .subcategory-page {
            margin-top: 200px;
            padding: 0 16px;
            min-height: 60vh;
        }

        @media (max-width: 768px) {
            .subcategory-page {
                margin-top: 190px;
                padding: 0 12px;
            }
        }

        /* Header de la page */
        .subcategory-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafd 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .subcategory-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }

        .breadcrumb-link {
            color: #64748b;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .breadcrumb-link:hover {
            color: #FF9900;
        }

        .breadcrumb-separator {
            color: #cbd5e1;
        }

        .breadcrumb-current {
            color: #1e293b;
            font-weight: 600;
        }

        .subcategory-title-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .subcategory-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .subcategory-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            box-shadow: 0 4px 12px rgba(255, 153, 0, 0.3);
        }

        .subcategory-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .subcategory-count {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 2px;
        }

        .subcategory-actions {
            display: flex;
            gap: 10px;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .back-home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .back-home-btn i {
            font-size: 1rem;
        }

        /* Badge nombre d'articles */
        .articles-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: rgba(255, 153, 0, 0.1);
            color: #FF9900;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .articles-count-badge i {
            font-size: 0.9rem;
        }

        /* Section des résultats */
        .results-section {
            margin-bottom: 24px;
        }

        /* Pagination améliorée */
        .pagination-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafd 100%);
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .pagination-info {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Empty state */
        .empty-results {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafd 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #94a3b8;
        }

        .empty-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .empty-text {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .empty-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 153, 0, 0.3);
        }

        .empty-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 153, 0, 0.4);
            color: white;
        }

        @media (max-width: 576px) {
            .subcategory-header {
                padding: 16px;
            }

            .subcategory-title-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .subcategory-icon {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }

            .subcategory-name {
                font-size: 1.2rem;
            }

            .back-home-btn span {
                display: none;
            }

            .back-home-btn {
                padding: 10px 12px;
            }
        }
    </style>

    <div class="subcategory-page">
        <!-- Header de la page -->
        <div class="subcategory-header">
            <!-- Fil d'Ariane -->
            <nav class="subcategory-breadcrumb">
                <a href="{{ route('articles.index') }}" class="breadcrumb-link">
                    <i class="bi bi-house-door"></i>
                    Accueil
                </a>
                <span class="breadcrumb-separator">
                    <i class="bi bi-chevron-right"></i>
                </span>
                @if(isset($selectedSubcategory) && $selectedSubcategory->categorie)
                    <span class="breadcrumb-link">{{ $selectedSubcategory->categorie->nom ?? 'Catégorie' }}</span>
                    <span class="breadcrumb-separator">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif
                <span class="breadcrumb-current">
                    {{ isset($selectedSubcategory) ? $selectedSubcategory->nom : 'Articles' }}
                </span>
            </nav>

            <!-- Titre et actions -->
            <div class="subcategory-title-section">
                <div class="subcategory-title">
                    <div class="subcategory-icon">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <div>
                        <h1 class="subcategory-name">
                            {{ isset($selectedSubcategory) ? $selectedSubcategory->nom : 'Articles' }}
                        </h1>
                        <p class="subcategory-count">
                            {{ $articles->total() }} article{{ $articles->total() > 1 ? 's' : '' }} trouvé{{ $articles->total() > 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>

                <div class="subcategory-actions">
                    <span class="articles-count-badge">
                        <i class="bi bi-box-seam"></i>
                        {{ $articles->total() }} résultat{{ $articles->total() > 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('articles.index') }}" class="back-home-btn">
                        <i class="bi bi-arrow-left"></i>
                        <span>Retour</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Publicités -->
        @include('partials.publicites', ['position' => 'entre_articles'])

        <!-- Résultats -->
        <div class="results-section">
            @if($articles->isEmpty())
                <div class="empty-results">
                    <div class="empty-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h2 class="empty-title">Aucun article trouvé</h2>
                    <p class="empty-text">
                        Il n'y a pas encore d'articles dans cette sous-catégorie.
                        <br>Revenez plus tard ou explorez d'autres catégories.
                    </p>
                    <a href="{{ route('articles.index') }}" class="empty-btn">
                        <i class="bi bi-house-door"></i>
                        Explorer les articles
                    </a>
                </div>
            @else
                <div id="articles-results" data-context="articles">
                    @include('partials.articles-list', ['articles' => $articles])
                </div>

                <!-- Pagination -->
                @if($articles->hasPages())
                    <div class="pagination-wrapper">
                        <p class="pagination-info">
                            Page {{ $articles->currentPage() }} sur {{ $articles->lastPage() }}
                        </p>
                        {{ $articles->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</main>
@endsection
