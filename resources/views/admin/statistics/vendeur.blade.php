@extends('admin.layout')

@section('title', 'Vendeur — Statistiques')
@section('page-title', 'Statistiques')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $u = $fiche['user'];
    $c = $fiche['colonnes'];
    $k = $fiche['kpis'];
    $params = $filters->queryParams();
    $trend = function (array $m) {
        if ($m['evolution'] === null) {
            return '<span class="st-trend new"><i class="fas fa-star"></i> nouveau</span>';
        }
        $cls = $m['evolution'] > 0 ? 'up' : ($m['evolution'] < 0 ? 'down' : 'flat');
        $icon = $m['evolution'] > 0 ? 'fa-arrow-up' : ($m['evolution'] < 0 ? 'fa-arrow-down' : 'fa-minus');
        return "<span class=\"st-trend {$cls}\"><i class=\"fas {$icon}\"></i> " . number_format(abs($m['evolution']), 1, ',', ' ') . ' %</span>';
    };
    $urlWith = function (array $changes) {
        $p = array_filter(array_merge(request()->query(), $changes), fn ($v) => $v !== null && $v !== '');
        return url()->current() . ($p ? '?' . http_build_query($p) : '');
    };
    $sortLink = function (string $key, string $label) use ($fiche, $urlWith) {
        $active = $fiche['tri'] === $key;
        $next = $active && $fiche['sens'] === 'desc' ? 'asc' : 'desc';
        $icon = $active ? ($fiche['sens'] === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
        return '<a href="' . e($urlWith(['tri' => $key, 'sens' => $next, 'annonces_page' => null])) . '" class="' . ($active ? 'active' : '') . '">' . e($label) . '<i class="fas ' . $icon . '"></i></a>';
    };
    $chartData = $fiche['series'];
    $annonces = $fiche['annonces'];
@endphp

@include('admin.statistics.partials._styles')

@section('content')

<nav class="st-crumb" aria-label="Fil d'Ariane">
    <a href="{{ route('admin.statistics.index', $params) }}#utilisateurs"><i class="fas fa-chart-line"></i> Statistiques</a>
    <i class="fas fa-angle-right"></i>
    <a href="{{ route('admin.statistics.details', ['type' => 'boutiques'] + $params) }}">Boutiques</a>
    <i class="fas fa-angle-right"></i>
    <span>{{ $u->name }}</span>
</nav>

@include('admin.statistics.partials._filters', ['showArticleFilters' => false])

<div class="admin-card">
    <div class="admin-card-body">
        <div class="st-fiche-head">
            <img class="st-fiche-photo round" src="{{ $c['photo'] }}" alt="">
            <div class="st-fiche-info">
                <h3>
                    {{ $u->name }}
                    @if($c['certifie'])<i class="fas fa-circle-check badge-cert" title="Boutique certifiée"></i>@endif
                    @if($u->is_blocked)<span class="st-badge blocked">bloqué</span>@endif
                </h3>
                <div class="st-fiche-meta">
                    <span><i class="fas fa-envelope"></i> {{ $u->email }}</span>
                    @if($u->telephone || $u->whatsapp)<span><i class="fas fa-phone"></i> {{ $u->telephone ?: $u->whatsapp }}</span>@endif
                    <span>Inscrit le <strong>{{ $u->created_at?->format('d/m/Y') }}</strong></span>
                    <span><strong>{{ $fmt($k['annonces_en_ligne']) }}</strong> annonces en ligne sur {{ $fmt($k['annonces_total']) }}</span>
                </div>
                <div class="st-fiche-links">
                    <a class="btn btn-sm btn-outline-primary" href="{{ $c['boutique_url'] }}" target="_blank"><i class="fas fa-store"></i> Voir la boutique</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.show', $u) }}"><i class="fas fa-user-gear"></i> Fiche admin</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="st-kpis mb-4">
    <div class="st-kpi" style="--kpi:#8b5cf6">
        <div class="st-kpi-label"><span>Visites de la boutique</span><i class="fas fa-shop"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['visites_boutique']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['visites_boutique']) !!} vs {{ $fmt($k['visites_boutique']['precedent']) }}</div>
    </div>
    <div class="st-kpi" style="--kpi:#3b82f6">
        <div class="st-kpi-label"><span>Vues de ses annonces</span><i class="fas fa-eye"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['vues_annonces']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['vues_annonces']) !!} vs {{ $fmt($k['vues_annonces']['precedent']) }}</div>
        <div class="st-kpi-sub mt-1">{{ $fmt($k['visiteurs']) }} visiteurs uniques (boutique + annonces)</div>
    </div>
    <div class="st-kpi" style="--kpi:#ef4444">
        <div class="st-kpi-label"><span>Likes reçus</span><i class="fas fa-heart"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['likes']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['likes']) !!} · {{ $fmt($k['likes_total']) }} au total</div>
    </div>
    <div class="st-kpi" style="--kpi:#f97316">
        <div class="st-kpi-label"><span>Annonces publiées</span><i class="fas fa-newspaper"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['annonces']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['annonces']) !!} vs {{ $fmt($k['annonces']['precedent']) }}</div>
    </div>
</div>

@if($fiche['meilleure'])
    @php $best = $fiche['meilleure']; @endphp
    <a class="st-champion mb-4" style="--chip-bg:#fef3c7;--chip:#92400e" href="{{ route('admin.statistics.annonce', ['article' => $best['id']] + $params) }}">
        <div class="st-champion-icon">@if($best['photo'])<img src="{{ $best['photo'] }}" alt="">@else<i class="fas fa-trophy"></i>@endif</div>
        <div class="st-champion-body">
            <div class="st-champion-label"><i class="fas fa-trophy"></i> Son annonce la plus vue sur la période</div>
            <div class="st-champion-name">{{ $best['titre'] }}</div>
            <div class="st-champion-value">{{ $fmt($best['vues']) }} vues · {{ $best['prix'] !== null ? $fmt($best['prix']) . ' F' : '' }}</div>
        </div>
        <i class="fas fa-angle-right text-muted"></i>
    </a>
@endif

<div class="admin-card">
    <div class="admin-card-header"><h5 class="admin-card-title">Audience dans le temps</h5></div>
    <div class="admin-card-body"><div class="st-chart"><canvas id="chartFiche"></canvas></div></div>
</div>

<div class="admin-card">
    <div class="admin-card-header st-card-head">
        <h5 class="admin-card-title">Ses annonces <small class="text-muted fw-normal">— {{ $fmt($annonces->total()) }} au total, vues sur la période</small></h5>
    </div>
    <div class="admin-card-body">
        @if($annonces->isEmpty())
            <div class="st-empty"><i class="fas fa-newspaper"></i>Aucune annonce publiée.</div>
        @else
            <div class="table-responsive">
                <table class="st-table">
                    <thead><tr>
                        <th>Annonce</th>
                        <th>Statut</th>
                        <th class="num">{!! $sortLink('prix', 'Prix') !!}</th>
                        <th>{!! $sortLink('publie_le', 'Publiée le') !!}</th>
                        <th class="num">{!! $sortLink('vues', 'Vues (période)') !!}</th>
                        <th class="num">{!! $sortLink('likes', 'Likes (total)') !!}</th>
                    </tr></thead>
                    <tbody>
                    @foreach($annonces->items() as $row)
                        @php $m = $fiche['modeles']->get($row->id); @endphp
                        @continue(! $m)
                        <tr data-href="{{ route('admin.statistics.annonce', ['article' => $m->id] + $params) }}" tabindex="0">
                            <td>
                                <div class="st-person">
                                    <img class="st-thumb" src="{{ $m->photo_url }}" alt="" loading="lazy">
                                    <div>
                                        <a href="{{ route('admin.statistics.annonce', ['article' => $m->id] + $params) }}">{{ \Illuminate\Support\Str::limit($m->titre, 60) }}</a>
                                        <small>{{ $m->sousCategorie?->nom }} · {{ $m->lieu }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="st-badge {{ $m->status }}">{{ \App\Services\StatisticsDetails::statutLabel($m->status) }}</span></td>
                            <td class="num">{{ $fmt($m->prix_ht) }} F</td>
                            <td>{{ $m->created_at?->format('d/m/Y') }}</td>
                            <td class="num"><strong>{{ $fmt($row->vues) }}</strong></td>
                            <td class="num">{{ $fmt($row->likes) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($annonces->hasPages())
                <div class="st-pagination">{{ $annonces->onEachSide(1)->links() }}</div>
            @endif
        @endif
    </div>
</div>

@endsection

@include('admin.statistics.partials._filters_script')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    const s = @json($chartData);
    if (typeof Chart === 'undefined') return;
    const nf = new Intl.NumberFormat('fr-FR');
    const line = (label, data, color, extra = {}) => ({ type: 'line', label, data, borderColor: color, backgroundColor: color + '22',
        tension: .3, pointRadius: data.length > 60 ? 0 : 3, borderWidth: 2, ...extra });
    new Chart(document.getElementById('chartFiche'), {
        data: { labels: s.labels, datasets: [
            line('Visites de la boutique', s.visites_boutique, '#8b5cf6', { fill: true }),
            line('Vues de ses annonces', s.vues_annonces, '#3b82f6'),
            line('Likes reçus', s.likes, '#ef4444'),
        ] },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { usePointStyle: true } }, tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label} : ${nf.format(ctx.parsed.y)}` } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
            },
        },
    });
})();
</script>
@endpush
