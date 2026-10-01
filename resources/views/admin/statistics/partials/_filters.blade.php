{{--
    Barre de filtres partagée (page Statistiques, pages de détail, fiches).
    Variables : $filters (AdminStatistics), $periods, $granularities, $villeOptions, $categorieOptions,
    $showArticleFilters (bool), $showGrouping (bool), $meta (texte optionnel à droite).
--}}
@php
    $showArticleFilters = $showArticleFilters ?? true;
    $showGrouping = $showGrouping ?? false;
    $filterKeys = ['periode', 'du', 'au', 'ville', 'categorie', 'par', 'top', 'page', 'refresh', 'fans_page', 'annonces_page'];
    // Paramètres propres à la page (tri, recherche, statut…) conservés quand on change la période
    $keepParams = \Illuminate\Support\Arr::except(request()->query(), $filterKeys);
@endphp
<div class="admin-card">
    <div class="admin-card-body">
        <form method="GET" action="{{ url()->current() }}" class="st-filters" id="statsFilters">
            <input type="hidden" name="periode" id="periode" value="{{ $filters->period }}">
            @foreach($keepParams as $name => $value)
                @if(is_scalar($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
            @endforeach

            <div class="st-date">
                <label class="form-label" for="dateRangeBtn">Période</label>
                <button type="button" class="st-date-btn" id="dateRangeBtn" aria-haspopup="dialog">
                    <i class="far fa-calendar-alt"></i>
                    @if($filters->period !== 'perso')<span class="st-date-chip">{{ $periods[$filters->period] }}</span>@endif
                    <span class="st-date-text">Du {{ $filters->from->format('d/m/Y') }} au {{ $filters->to->format('d/m/Y') }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.7rem;opacity:.5"></i>
                </button>
                <input type="text" id="dateRangeInput" class="st-date-input" tabindex="-1" aria-hidden="true">
                {{-- Champs réels (repli si le calendrier ne se charge pas) --}}
                <div class="st-date-fallback mt-1" id="dateFallback" hidden>
                    <input type="date" name="du" id="du" class="form-control form-control-sm" value="{{ $filters->from->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="Date de début">
                    <input type="date" name="au" id="au" class="form-control form-control-sm" value="{{ $filters->to->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="Date de fin">
                </div>
            </div>

            @if($showArticleFilters)
                <div>
                    <label class="form-label" for="ville">Ville</label>
                    <select name="ville" id="ville" class="form-select form-select-sm st-autosubmit">
                        <option value="">Toutes les villes</option>
                        @foreach($villeOptions as $ville)
                            <option value="{{ $ville }}" @selected($filters->ville !== null && \App\Services\StatTracker::normalize($filters->ville) === \App\Services\StatTracker::normalize($ville))>{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="categorie">Catégorie</label>
                    <select name="categorie" id="categorie" class="form-select form-select-sm st-autosubmit">
                        <option value="">Toutes les catégories</option>
                        @foreach($categorieOptions as $cat)
                            <optgroup label="{{ $cat['nom'] }}">
                                <option value="c{{ $cat['id'] }}" @selected($filters->categorieParam() === 'c' . $cat['id'])>{{ $cat['nom'] }} (toute la catégorie)</option>
                                @foreach($cat['sous'] as $sous)
                                    <option value="s{{ $sous['id'] }}" @selected($filters->categorieParam() === 's' . $sous['id'])>{{ $cat['nom'] }} › {{ $sous['nom'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($showGrouping)
                <div>
                    <label class="form-label" for="par">Regrouper</label>
                    <select name="par" id="par" class="form-select form-select-sm st-autosubmit">
                        @foreach($granularities as $key => $label)
                            <option value="{{ $key }}" @selected($filters->granularity === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="top">Classements</label>
                    <select name="top" id="top" class="form-select form-select-sm st-autosubmit">
                        @foreach($topSizes as $size)
                            <option value="{{ $size }}" @selected($filters->top === $size)>Top {{ $size }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Appliquer</button>
                <a href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" class="btn btn-outline-secondary btn-sm st-keep-hash" title="Recalculer sans attendre le cache">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>

            <div class="st-meta ms-auto text-end">
                comparé au {{ $filters->previousFrom->format('d/m/Y') }} – {{ $filters->previousTo->format('d/m/Y') }}
                @isset($meta)<br>{{ $meta }}@endisset
            </div>
        </form>
    </div>
</div>

@if($showArticleFilters && $filters->hasArticleFilter())
    <div class="st-filter-active">
        <i class="fas fa-filter"></i>
        <span>Filtre actif : <strong>{{ $filters->filterLabel() }}</strong>. Les inscriptions et les recherches ne dépendent pas d'une annonce : elles restent globales.</span>
        <a href="{{ url()->current() . '?' . http_build_query(\Illuminate\Support\Arr::except(request()->query(), ['ville', 'categorie', 'page'])) }}" class="st-keep-hash ms-auto"><i class="fas fa-times"></i> Retirer le filtre</a>
    </div>
@endif
