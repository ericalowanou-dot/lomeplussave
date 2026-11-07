@php
    $outcome = $articles instanceof \App\Services\Search\RankedPaginator ? $articles->outcome : null;
@endphp

@if($outcome?->isFallback())
    <div class="search-feedback" style="margin: 12px 0; padding: 12px 16px; background: #fff8e6; border-radius: 8px; color: #5c4a12;">
        Aucune annonce ne correspond à « {{ request('q') }} ».
        Vous cherchiez peut-être :
        <strong>{{ $outcome->categorieProche ?? 'ces annonces récentes et populaires' }}</strong>
    </div>
@elseif($outcome?->comprehension)
    <div class="search-feedback" style="margin: 12px 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; color: #495057;">
        <span>Résultats pour :</span>
        @foreach($outcome->criteres() as $critere)
            <a href="{{ $critere['q'] === '' ? route('articles.index') : route('article.search', ['q' => $critere['q']]) }}"
               title="Retirer ce critère"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #e9ecef; border-radius: 999px; color: #212529; text-decoration: none;">
                {{ $critere['label'] }} <span aria-hidden="true">&times;</span>
            </a>
        @endforeach
    </div>
@endif
