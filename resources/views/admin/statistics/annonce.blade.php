@extends('admin.layout')

@section('title', 'Annonce — Statistiques')
@section('page-title', 'Statistiques')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $a = $fiche['article'];
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
    $chartData = $fiche['series'];
@endphp

@include('admin.statistics.partials._styles')

@section('content')

<nav class="st-crumb" aria-label="Fil d'Ariane">
    <a href="{{ route('admin.statistics.index', $params) }}#annonces"><i class="fas fa-chart-line"></i> Statistiques</a>
    <i class="fas fa-angle-right"></i>
    <a href="{{ route('admin.statistics.details', ['type' => 'annonces-vues'] + $params) }}">Annonces</a>
    <i class="fas fa-angle-right"></i>
    <span>{{ \Illuminate\Support\Str::limit($a->titre, 50) }}</span>
</nav>

@include('admin.statistics.partials._filters', ['showArticleFilters' => false])

<div class="admin-card">
    <div class="admin-card-body">
        <div class="st-fiche-head">
            <img class="st-fiche-photo" src="{{ $c['photo'] }}" alt="">
            <div class="st-fiche-info">
                <h3>{{ $a->titre }}</h3>
                <div class="st-fiche-meta">
                    <span><strong>{{ $c['prix'] !== null ? $fmt($c['prix']) . ' F' : '—' }}</strong></span>
                    <span><i class="fas fa-map-marker-alt"></i> {{ $a->lieu ?: '—' }}</span>
                    <span><i class="fas fa-tag"></i> {{ $a->sousCategorie?->categorie?->nom }} › {{ $a->sousCategorie?->nom ?? '—' }}</span>
                    <span><span class="st-badge {{ $a->status }}">{{ \App\Services\StatisticsDetails::statutLabel($a->status) }}</span></span>
                    <span>{{ $a->neuf ? 'Neuf' : 'Occasion' }} · {{ $a->livraison ? 'avec livraison' : 'sans livraison' }}</span>
                    <span>Publiée le <strong>{{ $a->created_at?->format('d/m/Y') }}</strong></span>
                    @if($a->isBoosted())<span class="st-badge pending"><i class="fas fa-rocket"></i> boostée jusqu'au {{ $a->boosted_until->format('d/m/Y') }}</span>@endif
                </div>
                <div class="st-fiche-meta">
                    <span>Vendeur :
                        @if($a->user)
                            <a href="{{ route('admin.statistics.vendeur', ['user' => $a->user->id] + $params) }}"><strong>{{ $a->user->name }}</strong></a>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="st-fiche-links">
                    @if($c['url'])<a class="btn btn-sm btn-outline-primary" href="{{ $c['url'] }}" target="_blank"><i class="fas fa-external-link-alt"></i> Voir sur le site</a>@endif
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.articles.show', $a) }}"><i class="fas fa-pen-to-square"></i> Fiche admin</a>
                    @if($a->user)<a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.statistics.vendeur', ['user' => $a->user->id] + $params) }}"><i class="fas fa-store"></i> Statistiques du vendeur</a>@endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="st-kpis mb-4">
    <div class="st-kpi" style="--kpi:#3b82f6">
        <div class="st-kpi-label"><span>Vues</span><i class="fas fa-eye"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['vues']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['vues']) !!} vs {{ $fmt($k['vues']['precedent']) }}</div>
    </div>
    <div class="st-kpi" style="--kpi:#6366f1">
        <div class="st-kpi-label"><span>Visiteurs uniques</span><i class="fas fa-user"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['visiteurs']) }}</div>
        <div class="st-kpi-sub">personnes différentes</div>
    </div>
    <div class="st-kpi" style="--kpi:#ef4444">
        <div class="st-kpi-label"><span>Likes sur la période</span><i class="fas fa-heart"></i></div>
        <div class="st-kpi-value">{{ $fmt($k['likes']['valeur']) }}</div>
        <div class="st-kpi-sub">{!! $trend($k['likes']) !!} · {{ $fmt($k['likes_total']) }} au total</div>
    </div>
    <div class="st-kpi" style="--kpi:#14b8a6">
        <div class="st-kpi-label"><span>Likes / vues</span><i class="fas fa-percentage"></i></div>
        <div class="st-kpi-value">{{ number_format($k['taux'], 1, ',', ' ') }} %</div>
        <div class="st-kpi-sub">part des vues qui finissent en like</div>
    </div>
</div>

@if($fiche['comparaison'])
    @php $cmp = $fiche['comparaison']; @endphp
    <div class="st-compare mb-4">
        <i class="fas fa-ranking-star text-primary"></i>
        Dans <strong>{{ $cmp['sous_categorie'] }}</strong>, cette annonce est <strong>{{ $cmp['rang'] }}<sup>{{ $cmp['rang'] === 1 ? 're' : 'e' }}</sup> sur {{ $cmp['total'] }}</strong> en nombre de vues.
        Moyenne de la sous-catégorie : <strong>{{ number_format($cmp['moyenne'], 1, ',', ' ') }} vues</strong> par annonce
        @if($cmp['ecart'] !== null)
            ({!! $cmp['ecart'] >= 0 ? '<span class="text-success fw-semibold">+' . $cmp['ecart'] . ' %</span>' : '<span class="text-danger fw-semibold">' . $cmp['ecart'] . ' %</span>' !!} pour celle-ci).
        @endif
    </div>
@endif

<div class="admin-card">
    <div class="admin-card-header"><h5 class="admin-card-title">Vues et likes dans le temps</h5></div>
    <div class="admin-card-body"><div class="st-chart"><canvas id="chartFiche"></canvas></div></div>
</div>

<div class="admin-card">
    <div class="admin-card-header st-card-head">
        <h5 class="admin-card-title">Qui a aimé cette annonce <small class="text-muted fw-normal">— {{ $fmt($fiche['fans']->total()) }} personne{{ $fiche['fans']->total() > 1 ? 's' : '' }}, depuis toujours</small></h5>
    </div>
    <div class="admin-card-body">
        @if($fiche['fans']->isEmpty())
            <div class="st-empty"><i class="fas fa-heart-crack"></i>Personne n'a encore aimé cette annonce.</div>
        @else
            <div class="table-responsive">
                <table class="st-table">
                    <thead><tr><th>Personne</th><th>Aimée le</th></tr></thead>
                    <tbody>
                    @foreach($fiche['fans']->items() as $fan)
                        @php $u = $fiche['fan_users']->get($fan->id); @endphp
                        <tr @if($u) data-href="{{ route('admin.statistics.vendeur', ['user' => $u->id] + $params) }}" tabindex="0" @endif>
                            <td>
                                <div class="st-person">
                                    <img src="{{ $u?->getProfilPhotoUrl() ?? \App\Models\User::defaultProfilPhotoUrl() }}" alt="" loading="lazy">
                                    <div>{{ $u?->name ?? 'Compte supprimé' }}<small>{{ $u?->email }}</small></div>
                                </div>
                            </td>
                            <td>
                                @if($fan->aime_le){{ \Carbon\Carbon::parse($fan->aime_le)->format('d/m/Y H:i') }}@else<span class="text-muted" title="Like enregistré avant la datation automatique">date inconnue</span>@endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($fiche['fans']->hasPages())
                <div class="st-pagination">{{ $fiche['fans']->onEachSide(1)->links() }}</div>
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
    new Chart(document.getElementById('chartFiche'), {
        data: { labels: s.labels, datasets: [
            { type: 'bar', label: 'Vues', data: s.vues, backgroundColor: '#3b82f6', borderRadius: 4, yAxisID: 'y' },
            { type: 'line', label: 'Likes', data: s.likes, borderColor: '#ef4444', backgroundColor: '#ef4444', tension: .3, pointRadius: s.labels.length > 60 ? 0 : 3, borderWidth: 2, yAxisID: 'y1' },
        ] },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { usePointStyle: true } }, tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label} : ${nf.format(ctx.parsed.y)}` } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Vues' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 }, title: { display: true, text: 'Likes' } },
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
            },
        },
    });
})();
</script>
@endpush
