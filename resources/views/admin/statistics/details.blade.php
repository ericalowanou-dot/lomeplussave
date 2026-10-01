@extends('admin.layout')

@section('title', $detail['titre'] . ' — Statistiques')
@section('page-title', 'Statistiques')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $urlWith = function (array $changes) {
        $params = array_filter(array_merge(request()->query(), $changes), fn ($v) => $v !== null && $v !== '');
        unset($params['refresh']);

        return url()->current() . ($params ? '?' . http_build_query($params) : '');
    };
    $backUrl = route('admin.statistics.index', $filters->queryParams()) . '#' . $detail['retour'];
    $paginator = $detail['paginator'];
@endphp

@include('admin.statistics.partials._styles')

@section('content')

<nav class="st-crumb" aria-label="Fil d'Ariane">
    <a href="{{ $backUrl }}"><i class="fas fa-chart-line"></i> Statistiques</a>
    <i class="fas fa-angle-right"></i>
    <span>{{ $detail['titre'] }}</span>
</nav>

@include('admin.statistics.partials._filters', ['showArticleFilters' => true])

<div class="st-detail-head">
    <div>
        <h3>{{ $detail['titre'] }}</h3>
        @if($detail['description'])<p>{{ $detail['description'] }}</p>@endif
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="st-total"><strong>{{ $fmt($detail['total']) }}</strong> {{ $detail['total'] > 1 ? 'éléments' : 'élément' }}</span>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.statistics.details.export', ['type' => $detail['type']] + request()->except('page')) }}">
            <i class="fas fa-file-csv"></i> Exporter tout
        </a>
    </div>
</div>

@if($detail['onglets'])
    <div class="st-chips">
        @foreach($detail['onglets'] as $value => [$label, $active])
            <a class="st-chip {{ $active ? 'active' : '' }}" href="{{ $urlWith(['liste' => $value, 'page' => null, 'tri' => null, 'sens' => null]) }}">{{ $label }}</a>
        @endforeach
    </div>
@endif

@if($detail['filtres'])
    <div class="st-chips">
        @foreach($detail['filtres'] as $param => $label)
            <a class="st-chip" href="{{ $urlWith([$param => null, 'page' => null]) }}" title="Retirer ce filtre">{{ $label }} <i class="fas fa-times"></i></a>
        @endforeach
    </div>
@endif

@if($detail['avertissement'])
    <div class="st-notice"><i class="fas fa-database mt-1"></i><div>{{ $detail['avertissement'] }}</div></div>
@endif

<div class="admin-card">
    <div class="admin-card-body">
        <div class="st-toolbar">
            @if($detail['recherche_placeholder'])
                <form method="GET" action="{{ url()->current() }}" role="search">
                    @foreach(request()->except(['q', 'page']) as $name => $value)
                        @if(is_scalar($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                    @endforeach
                    <input type="search" name="q" value="{{ $detail['recherche'] }}" class="form-control form-control-sm" placeholder="{{ $detail['recherche_placeholder'] }}" aria-label="{{ $detail['recherche_placeholder'] }}">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    @if($detail['recherche'] !== '')
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $urlWith(['q' => null, 'page' => null]) }}">Effacer</a>
                    @endif
                </form>
            @endif
            <div class="d-flex align-items-center gap-2 ms-auto">
                <label for="perPage" class="st-total mb-0">Par page</label>
                <select id="perPage" class="form-select form-select-sm" style="width:auto" onchange="window.location.href = this.value">
                    @foreach($perPageOptions as $n)
                        <option value="{{ $urlWith(['par_page' => $n, 'page' => null]) }}" @selected(($paginator?->perPage() ?? 25) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(empty($detail['lignes']))
            <div class="st-empty">
                <i class="fas fa-inbox"></i>
                Aucun résultat sur cette période{{ $filters->hasArticleFilter() ? ' avec ce filtre' : '' }}.
                @if($filters->period !== 'tout')
                    <div class="mt-2"><a href="{{ $urlWith(['periode' => 'tout', 'du' => null, 'au' => null, 'page' => null]) }}">Voir depuis le début →</a></div>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="st-table">
                    <thead><tr>
                        <th class="num">#</th>
                        @foreach($detail['colonnes'] as $key => $col)
                            @php $numeric = in_array($col['format'], ['num', 'num_alerte', 'money', 'pct'], true); @endphp
                            <th class="{{ $numeric ? 'num' : '' }}">
                                @isset($col['tri'])
                                    @php
                                        $active = $detail['tri'] === $key;
                                        $next = $active ? ($detail['sens'] === 'asc' ? 'desc' : 'asc') : ($col['sens'] ?? 'desc');
                                    @endphp
                                    <a href="{{ $urlWith(['tri' => $key, 'sens' => $next, 'page' => null]) }}" class="{{ $active ? 'active' : '' }}" title="Trier">
                                        {{ $col['label'] }}<i class="fas {{ $active ? ($detail['sens'] === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort' }}"></i>
                                    </a>
                                @else
                                    {{ $col['label'] }}
                                @endisset
                            </th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                    @foreach($detail['lignes'] as $i => $row)
                        @php $rank = ($paginator ? ($paginator->currentPage() - 1) * $paginator->perPage() : 0) + $i + 1; @endphp
                        <tr @if(! empty($row['_href'])) data-href="{{ $row['_href'] }}" tabindex="0" @endif>
                            <td class="num"><span class="st-rank">{{ $rank }}</span></td>
                            @foreach($detail['colonnes'] as $key => $col)
                                @include('admin.statistics.partials._cell', ['value' => $row[$key] ?? null, 'format' => $col['format']])
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($paginator && $paginator->hasPages())
                <div class="st-pagination">
                    <span class="st-total">{{ $fmt($paginator->firstItem()) }}–{{ $fmt($paginator->lastItem()) }} sur {{ $fmt($paginator->total()) }}</span>
                    {{ $paginator->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection

@include('admin.statistics.partials._filters_script')
