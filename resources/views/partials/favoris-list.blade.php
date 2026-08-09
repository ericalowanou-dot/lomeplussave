{{-- Favoris : même carte que l'accueil --}}
@include('partials.articles-list', [
    'articles' => $favoris ?? $articles ?? collect(),
    'showPromoFeed' => false,
])
