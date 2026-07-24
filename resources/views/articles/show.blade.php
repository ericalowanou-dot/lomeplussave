@extends('layouts.app2')

@section('title', $article->titre ?? 'Article')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ $article->titre }}</h1>
                    @if($article->photo_url ?? null)
                        <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="img-fluid rounded mb-3" style="max-height: 300px; object-fit: cover;">
                    @endif
                    <p class="text-muted mb-2">{{ number_format($article->prix_ht ?? 0, 0, '', '.') }} CFA</p>
                    @if($article->user ?? null)
                        <p class="mb-1"><strong>Vendeur :</strong> {{ $article->user->name }}</p>
                        <p class="mb-1"><strong>Membre depuis :</strong> {{ $membreDepuis ?? '—' }}</p>
                        <p class="mb-1"><strong>Articles publiés :</strong> {{ $nbArticles ?? 0 }}</p>
                        <p class="mb-3"><strong>Total likes :</strong> {{ $totalLikes ?? 0 }}</p>
                        <a href="{{ route('boutique.show', $article->user->id) }}" class="btn btn-outline-primary btn-sm">Voir la boutique</a>
                    @endif
                    <hr class="my-3">
                    <a href="{{ $article->url }}" class="btn btn-primary">Voir la fiche complète</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
