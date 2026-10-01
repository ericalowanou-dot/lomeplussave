@extends('admin.layout')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $pct = fn ($n) => number_format((float) $n, 1, ',', ' ') . ' %';
    $money = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' F';
    $query = array_filter(request()->only(['periode', 'du', 'au', 'par', 'top', 'ville', 'categorie']), fn ($v) => $v !== null && $v !== '');
    $exportUrl = fn (string $section) => route('admin.statistics.export', ['section' => $section] + $query);
    $kpis = $stats['kpis'];
    $tracking = $stats['tracking'];
    $r = $stats['recherches'];
    $jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $filtered = $filters->hasArticleFilter();
    $globalBadge = $filtered ? '<span class="st-global" title="Non concerné par le filtre ville / catégorie">global</span>' : '';

    // Menu : thème => [libellé, icône, [élément => libellé]]
    $groups = [
        'ensemble' => ["Vue d'ensemble", 'fa-gauge', ['chiffres' => 'Chiffres clés']],
        'courbes' => ['Courbes', 'fa-chart-line', [
            'activite' => 'Activité générale',
            'inscriptions' => 'Inscriptions et total cumulé',
            'audience' => 'Audience : vues, boutiques, recherches',
            'habitudes' => 'Jours et heures d\'activité',
        ]],
        'annonces' => ['Annonces', 'fa-newspaper', [
            'plus-vues' => 'Les plus vues (cliquées)',
            'plus-aimees' => 'Les plus aimées',
            'statut' => 'Statut, neuf / occasion, livraison',
            'prix' => 'Prix',
            'mots-cles' => 'Mots-clés des titres',
        ]],
        'utilisateurs' => ['Utilisateurs', 'fa-users', [
            'inscriptions' => 'Inscriptions et comptes',
            'vendeurs' => 'Vendeurs qui publient le plus',
            'boutiques' => 'Boutiques les plus visitées',
        ]],
        'categories' => ['Catégories', 'fa-tags', [
            'categories' => 'Catégories',
            'sous-categories' => 'Sous-catégories',
        ]],
        'villes' => ['Villes', 'fa-map-marker-alt', ['villes' => 'Villes où l\'on publie le plus']],
        'recherches' => ['Recherches', 'fa-search', [
            'frequentes' => 'Les plus fréquentes',
            'sans-resultat' => 'Sans résultat',
            'sources' => 'D\'où viennent les recherches',
        ]],
    ];

    // Préparé ici : @json() ne sait pas lire un tableau littéral à plusieurs clés
    $chartData = [
        'series' => $stats['series'],
        'repartitions' => $stats['repartitions'],
        'heatmaps' => $stats['heatmaps'],
        'villes' => $stats['top_villes'],
        'periode' => ['du' => $filters->from->toDateString(), 'au' => $filters->to->toDateString()],
        'presets' => array_diff_key($periods, ['perso' => true]),
        'baseUrl' => route('admin.statistics.index'),
    ];

    $trend = function (array $m) {
        if ($m['evolution'] === null) {
            return '<span class="st-trend new"><i class="fas fa-star"></i> nouveau</span>';
        }
        $cls = $m['evolution'] > 0 ? 'up' : ($m['evolution'] < 0 ? 'down' : 'flat');
        $icon = $m['evolution'] > 0 ? 'fa-arrow-up' : ($m['evolution'] < 0 ? 'fa-arrow-down' : 'fa-minus');
        $value = number_format(abs($m['evolution']), 1, ',', ' ');
        return "<span class=\"st-trend {$cls}\"><i class=\"fas {$icon}\"></i> {$value} %</span>";
    };
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<style>
    .main-content, .st-grid-2 > *, .st-grid-3 > *, .st-grid-4 > *, .st-kpis > * { min-width: 0; }

    /* ---- Filtres ---- */
    .st-filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .st-filters .form-label { font-size: .75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .03em; }
    .st-filters .form-select { min-width: 140px; }
    .st-date { position: relative; min-width: 290px; }
    .st-date .st-date-btn { display: flex; align-items: center; gap: 10px; width: 100%; background: #fff; border: 1px solid #ced4da; border-radius: 6px; padding: 5px 12px; font-size: .9rem; cursor: pointer; text-align: left; }
    .st-date .st-date-btn:hover, .st-date .st-date-btn:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
    .st-date .st-date-btn i { color: var(--primary-color); }
    .st-date-chip { background: #eef2ff; color: #3730a3; font-weight: 600; font-size: .75rem; border-radius: 999px; padding: 1px 8px; white-space: nowrap; }
    .st-date-text { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .st-date-input { position: absolute; left: 0; bottom: 0; width: 1px; height: 1px; opacity: 0; pointer-events: none; border: 0; padding: 0; }
    .st-date-fallback { display: flex; gap: 6px; }
    .st-date-fallback input { width: auto; }
    .st-meta { font-size: .8rem; color: var(--text-secondary); }
    .st-filter-active { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 10px; padding: 10px 14px; font-size: .88rem; margin-bottom: 16px; }
    .st-filter-active a { color: #9a3412; font-weight: 600; }
    .st-global { display: inline-block; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; background: #f1f5f9; color: #475569; border-radius: 4px; padding: 1px 5px; margin-left: 4px; vertical-align: middle; }

    /* Calendrier : raccourcis à gauche */
    .flatpickr-calendar.st-cal.open { display: flex; }
    .flatpickr-calendar.st-cal { width: auto !important; }
    .st-cal-main { position: relative; display: flex; flex-direction: column; }
    .st-presets { display: flex; flex-direction: column; gap: 2px; padding: 10px; border-right: 1px solid #e5e7eb; min-width: 160px; background: #f8fafc; border-radius: 5px 0 0 5px; }
    .st-presets button { border: 0; background: transparent; text-align: left; padding: 6px 10px; border-radius: 6px; font-size: .85rem; color: var(--dark-color); }
    .st-presets button:hover { background: #eef2ff; color: #3730a3; }
    .st-presets button.active { background: var(--primary-color); color: #fff; }
    .st-presets .st-presets-title { font-size: .7rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); padding: 2px 10px 6px; letter-spacing: .04em; }
    .flatpickr-day.inRange, .flatpickr-day.inRange:hover { background: #e0e7ff; border-color: #e0e7ff; box-shadow: -5px 0 0 #e0e7ff, 5px 0 0 #e0e7ff; }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background: var(--primary-color) !important; border-color: var(--primary-color) !important; }
    @media (max-width: 768px) {
        .flatpickr-calendar.st-cal.open { flex-direction: column; }
        .st-presets { order: 2; flex-direction: row; flex-wrap: wrap; border-right: 0; border-top: 1px solid #e5e7eb; min-width: 0; border-radius: 0 0 5px 5px; }
        .st-presets .st-presets-title { width: 100%; }
        .st-presets button { padding: 4px 9px; background: #fff; border: 1px solid #e5e7eb; }
    }

    /* ---- Menu par thèmes ---- */
    .st-tabs-wrap { position: sticky; top: var(--st-nav-top, 80px); z-index: 30; background: var(--light-color); padding: 8px 0 10px; margin-bottom: 12px; }
    .st-tabs { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 2px; }
    .st-tabs::-webkit-scrollbar { display: none; }
    .st-tab-btn { display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; border: 0; padding: 8px 14px; border-radius: 10px; background: #fff; color: var(--dark-color); font-size: .9rem; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .st-tab-btn .fa-chevron-down { font-size: .65rem; opacity: .5; transition: transform .15s; }
    .st-tab-btn:hover { background: #eef2ff; }
    .st-tab-btn.active { background: var(--primary-color); color: #fff; }
    .st-tab-btn.active .fa-chevron-down { opacity: .8; }
    .st-tab-btn[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
    .st-menu { position: absolute; top: 100%; margin-top: -4px; min-width: 260px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(15,23,42,.18); padding: 6px; z-index: 40; }
    .st-menu[hidden] { display: none; }
    .st-menu a { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: var(--dark-color); text-decoration: none; font-size: .9rem; }
    .st-menu a:hover, .st-menu a:focus { background: #eef2ff; color: #3730a3; outline: none; }
    .st-menu a.st-menu-all { font-weight: 600; border-bottom: 1px solid #f1f5f9; border-radius: 8px 8px 0 0; margin-bottom: 4px; }
    .st-menu a i { width: 16px; text-align: center; color: var(--primary-color); opacity: .8; }
    @media (max-width: 768px) {
        .st-menu { left: 0 !important; right: 0; min-width: 0; }
    }

    .st-panel[hidden] { display: none; }
    .st-block { scroll-margin-top: calc(var(--st-nav-top, 80px) + 80px); }
    .st-panel-title { font-size: 1.15rem; font-weight: 700; margin: 4px 0 16px; display: flex; align-items: center; gap: 10px; }
    .st-panel-title i { color: var(--primary-color); }
    .st-block-title { font-size: 1rem; font-weight: 700; margin: 24px 0 12px; color: var(--dark-color); }
    .st-block:first-of-type .st-block-title { margin-top: 0; }

    /* ---- Cartes chiffres ---- */
    .st-kpis { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
    .st-kpi { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-top: 3px solid var(--kpi, var(--primary-color)); }
    .st-kpi-label { font-size: .82rem; color: var(--text-secondary); font-weight: 500; display: flex; justify-content: space-between; gap: 8px; }
    .st-kpi-label i { color: var(--kpi, var(--primary-color)); opacity: .7; }
    .st-kpi-value { font-size: 1.75rem; font-weight: 700; color: var(--dark-color); margin: 6px 0 4px; font-variant-numeric: tabular-nums; }
    .st-kpi-sub { font-size: .78rem; color: var(--text-secondary); }
    .st-trend { display: inline-flex; align-items: center; gap: 3px; font-weight: 600; font-size: .78rem; padding: 1px 7px; border-radius: 999px; }
    .st-trend.up { color: #047857; background: #d1fae5; }
    .st-trend.down { color: #b91c1c; background: #fee2e2; }
    .st-trend.flat { color: #4b5563; background: #f3f4f6; }
    .st-trend.new { color: #1d4ed8; background: #dbeafe; }
    a.st-kpi { display: block; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s; }
    a.st-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }

    /* ---- Graphiques ---- */
    .st-chart { position: relative; height: 320px; user-select: none; -webkit-user-select: none; }
    .st-chart.sm { height: 230px; }
    .st-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
    .st-grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .st-grid-2 > .admin-card, .st-grid-3 > .admin-card { margin-bottom: 0; }
    .st-zoom-hint { font-size: .82rem; color: var(--text-secondary); display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
    .st-selection { position: sticky; top: calc(var(--st-nav-top, 80px) + 64px); z-index: 25; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #1e1b4b; color: #fff; border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; box-shadow: 0 8px 20px rgba(30,27,75,.25); }
    .st-selection[hidden] { display: none; }
    .st-selection strong { font-weight: 700; }
    .st-selection .btn { font-weight: 600; }
    .st-selection .st-sel-actions { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }

    .st-card-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
    .st-export { font-size: .8rem; text-decoration: none; color: var(--primary-color); font-weight: 600; white-space: nowrap; }
    .st-export:hover { text-decoration: underline; }

    /* ---- Tableaux ---- */
    .st-table { width: 100%; font-size: .88rem; }
    .st-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 8px 10px; white-space: nowrap; }
    .st-table th[data-sort] { cursor: pointer; user-select: none; }
    .st-table th[data-sort]:hover { color: var(--primary-color); }
    .st-table th[data-sort]::after { content: '\f0dc'; font-family: 'Font Awesome 6 Free'; font-weight: 900; margin-left: 5px; opacity: .3; }
    .st-table th.asc::after { content: '\f0de'; opacity: 1; }
    .st-table th.desc::after { content: '\f0dd'; opacity: 1; }
    .st-table td { padding: 9px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .st-table tr:last-child td { border-bottom: 0; }
    .st-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .st-rank { display: inline-flex; width: 26px; height: 26px; border-radius: 50%; align-items: center; justify-content: center; font-size: .78rem; font-weight: 700; background: #f3f4f6; color: #4b5563; }
    .st-rank.rank-1 { background: #fde68a; color: #92400e; }
    .st-rank.rank-2 { background: #e2e8f0; color: #334155; box-shadow: inset 0 0 0 1px #cbd5e1; }
    .st-rank.rank-3 { background: #fed7aa; color: #9a3412; }
    .st-person { display: flex; align-items: center; gap: 10px; min-width: 180px; }
    .st-person img, .st-thumb { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .st-thumb { border-radius: 8px; }
    .st-person a { color: var(--dark-color); font-weight: 600; text-decoration: none; }
    .st-person a:hover { color: var(--primary-color); }
    .st-person small { display: block; color: var(--text-secondary); font-weight: 400; }
    .st-bar { height: 6px; border-radius: 3px; background: #eef2ff; overflow: hidden; min-width: 60px; }
    .st-bar span { display: block; height: 100%; background: var(--primary-color); border-radius: 3px; }
    .st-empty { text-align: center; color: var(--text-secondary); padding: 28px 10px; font-size: .9rem; }
    .st-empty i { display: block; font-size: 1.6rem; margin-bottom: 8px; opacity: .4; }
    .st-best { font-size: .82rem; }
    .st-best a { color: var(--dark-color); text-decoration: none; font-weight: 500; }
    .st-best a:hover { color: var(--primary-color); }
    .badge-cert { color: #2563eb; font-size: .8rem; }

    .st-cloud { display: flex; flex-wrap: wrap; gap: 8px; align-items: baseline; }
    .st-cloud span { background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 3px 12px; font-weight: 600; line-height: 1.5; }
    .st-cloud span small { opacity: .6; font-weight: 500; margin-left: 4px; }
    .st-cloud.alt span { background: #fff7ed; color: #9a3412; }

    .st-heat { width: 100%; border-collapse: separate; border-spacing: 2px; font-size: .7rem; }
    .st-heat th { color: var(--text-secondary); font-weight: 600; text-align: center; padding: 2px; }
    .st-heat td { height: 24px; border-radius: 3px; text-align: center; color: transparent; cursor: default; min-width: 18px; }
    .st-heat td:hover { outline: 2px solid var(--dark-color); color: var(--dark-color); }
    .st-heat-legend { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: var(--text-secondary); margin-top: 8px; }
    .st-heat-legend i { display: inline-block; width: 60px; height: 8px; border-radius: 4px; background: linear-gradient(90deg, #eef2ff, #4338ca); }

    .st-notice { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: 10px; padding: 12px 16px; font-size: .88rem; display: flex; gap: 10px; align-items: flex-start; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .st-filters > div { flex: 1 1 140px; }
        .st-filters .st-date { flex-basis: 100%; min-width: 0; }
        .st-filters .form-select { min-width: 0; width: 100%; }
        .st-kpi-value { font-size: 1.45rem; }
        .st-chart { height: 260px; }
        .st-selection .st-sel-actions { margin-left: 0; width: 100%; }
        .st-selection .st-sel-actions .btn { flex: 1; }
    }
</style>
@endpush

@section('content')

{{-- ================= Filtres ================= --}}
<div class="admin-card">
    <div class="admin-card-body">
        <form method="GET" action="{{ route('admin.statistics.index') }}" class="st-filters" id="statsFilters">
            <input type="hidden" name="periode" id="periode" value="{{ $filters->period }}">

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

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Appliquer</button>
                <a href="{{ route('admin.statistics.index', $query + ['refresh' => 1]) }}" class="btn btn-outline-secondary btn-sm st-keep-hash" title="Recalculer sans attendre le cache">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>

            <div class="st-meta ms-auto text-end">
                comparé au {{ $filters->previousFrom->format('d/m/Y') }} – {{ $filters->previousTo->format('d/m/Y') }}<br>
                calculé le {{ $stats['generated_at'] }}
            </div>
        </form>
    </div>
</div>

@if($stats['filtre'])
    <div class="st-filter-active">
        <i class="fas fa-filter"></i>
        <span>Filtre actif : <strong>{{ $stats['filtre'] }}</strong>. Les inscriptions et les recherches ne dépendent pas d'une annonce : elles restent globales.</span>
        <a href="{{ route('admin.statistics.index', \Illuminate\Support\Arr::except($query, ['ville', 'categorie'])) }}" class="st-keep-hash ms-auto"><i class="fas fa-times"></i> Retirer le filtre</a>
    </div>
@endif

@if(! $tracking['visites'] || ! $tracking['recherches'])
    <div class="st-notice">
        <i class="fas fa-database mt-1"></i>
        <div>Les tables de suivi n'existent pas encore. Lancez <code>php artisan migrate</code> pour activer le suivi des vues, des visites de boutiques et des recherches.</div>
    </div>
@endif

{{-- ================= Menu par thèmes ================= --}}
<div class="st-tabs-wrap" id="statsTabs">
    <nav class="st-tabs" aria-label="Thèmes des statistiques">
        @foreach($groups as $key => [$label, $icon, $items])
            <button type="button" class="st-tab-btn" data-group="{{ $key }}" aria-expanded="false" aria-controls="st-menu-{{ $key }}">
                <i class="fas {{ $icon }}"></i> {{ $label }} <i class="fas fa-chevron-down"></i>
            </button>
        @endforeach
    </nav>
    @foreach($groups as $key => [$label, $icon, $items])
        <div class="st-menu" id="st-menu-{{ $key }}" data-menu="{{ $key }}" role="menu" hidden>
            <a href="#{{ $key }}" class="st-menu-all" role="menuitem"><i class="fas {{ $icon }}"></i> Tout voir : {{ $label }}</a>
            @foreach($items as $item => $itemLabel)
                <a href="#{{ $key }}/{{ $item }}" role="menuitem"><i class="fas fa-angle-right"></i> {{ $itemLabel }}</a>
            @endforeach
        </div>
    @endforeach
</div>

{{-- ================= Vue d'ensemble ================= --}}
<section class="st-panel" data-panel="ensemble">
    <div class="st-block" id="st-ensemble-chiffres">
        <h3 class="st-panel-title"><i class="fas fa-gauge"></i> Vue d'ensemble</h3>
        @php
            $cards = [
                ['Nouveaux inscrits', 'fa-user-plus', '#6366f1', $kpis['inscriptions'], $fmt($kpis['utilisateurs_total']) . ' inscrits au total', '#utilisateurs/inscriptions', true],
                ['Annonces publiées', 'fa-newspaper', '#f97316', $kpis['annonces'], $fmt($kpis['annonces_en_ligne']) . ' en ligne · ' . $fmt($kpis['annonces_en_attente']) . ' en attente', '#annonces/statut', false],
                ['Vendeurs actifs', 'fa-store', '#10b981', $kpis['vendeurs_actifs'], 'ont publié sur la période', '#utilisateurs/vendeurs', false],
                ['Vues d\'annonces', 'fa-eye', '#3b82f6', $kpis['vues_annonces'], $fmt($kpis['visiteurs_uniques']) . ' visiteurs uniques', '#annonces/plus-vues', false],
                ['Visites de boutiques', 'fa-shop', '#8b5cf6', $kpis['visites_boutiques'], 'pages boutique consultées', '#utilisateurs/boutiques', false],
                ['Recherches', 'fa-search', '#0ea5e9', $kpis['recherches'], $pct($r['sans_resultat_pct']) . ' sans résultat', '#recherches', true],
                ['Likes (favoris)', 'fa-heart', '#ef4444', $kpis['likes'], 'ajouts en favoris', '#annonces/plus-aimees', false],
            ];
        @endphp
        <div class="st-kpis">
            @foreach($cards as [$label, $icon, $color, $metric, $sub, $link, $isGlobal])
                <a class="st-kpi" href="{{ $link }}" style="--kpi: {{ $color }}">
                    <div class="st-kpi-label"><span>{{ $label }}{!! $isGlobal ? $globalBadge : '' !!}</span><i class="fas {{ $icon }}"></i></div>
                    <div class="st-kpi-value">{{ $fmt($metric['valeur']) }}</div>
                    <div class="st-kpi-sub">{!! $trend($metric) !!} <span title="Période précédente">vs {{ $fmt($metric['precedent']) }}</span></div>
                    <div class="st-kpi-sub mt-1">{{ $sub }}</div>
                </a>
            @endforeach
            <a class="st-kpi" href="#utilisateurs/inscriptions" style="--kpi: #14b8a6">
                <div class="st-kpi-label"><span>Inscrits devenus vendeurs{!! $globalBadge !!}</span><i class="fas fa-percentage"></i></div>
                <div class="st-kpi-value">{{ $pct($kpis['conversion_vendeurs_pct']) }}</div>
                <div class="st-kpi-sub">des nouveaux inscrits ont publié au moins une annonce</div>
            </a>
            <a class="st-kpi" href="#annonces/prix" style="--kpi: #eab308">
                <div class="st-kpi-label"><span>Prix des annonces</span><i class="fas fa-coins"></i></div>
                <div class="st-kpi-value">{{ $money($kpis['prix_median']) }}</div>
                <div class="st-kpi-sub">prix médian · moyenne {{ $money($kpis['prix_moyen']) }}</div>
            </a>
        </div>
    </div>
</section>

{{-- ================= Courbes ================= --}}
<section class="st-panel" data-panel="courbes" hidden>
    <h3 class="st-panel-title"><i class="fas fa-chart-line"></i> Courbes <small class="text-muted fw-normal fs-6">— {{ strtolower($granularities[$filters->granularity]) }}</small></h3>

    <div class="st-zoom-hint" id="zoomHint">
        <i class="fas fa-hand-pointer"></i>
        <span id="zoomHintText">Glissez la souris sur une courbe pour sélectionner une période, ou cliquez sur un point.</span>
        Les trois courbes suivent la même sélection.
    </div>

    <div class="st-selection" id="chartSelection" hidden>
        <i class="fas fa-crop-alt"></i>
        <span>Sélection : <strong id="selectionLabel"></strong></span>
        <div class="st-sel-actions">
            <button type="button" class="btn btn-light btn-sm" id="selectionApply"><i class="fas fa-check"></i> Appliquer cette période à toute la page</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="selectionReset"><i class="fas fa-undo"></i> Réinitialiser</button>
        </div>
    </div>

    <div class="st-block" id="st-courbes-activite">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Activité générale <small class="text-muted fw-normal">— cliquez une légende pour masquer une courbe</small></h5>
                <a class="st-export" href="{{ $exportUrl('evolution') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body"><div class="st-chart"><canvas id="chartActivity"></canvas></div></div>
        </div>
    </div>

    <div class="st-grid-2">
        <div class="st-block" id="st-courbes-inscriptions">
            <div class="admin-card mb-0">
                <div class="admin-card-header"><h5 class="admin-card-title">Inscriptions et total cumulé{!! $globalBadge !!}</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartUsers"></canvas></div></div>
            </div>
        </div>
        <div class="st-block" id="st-courbes-audience">
            <div class="admin-card mb-0">
                <div class="admin-card-header"><h5 class="admin-card-title">Audience : vues, boutiques, recherches</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartAudience"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="st-block mt-4" id="st-courbes-habitudes">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Jours et heures d'activité (heure de Lomé)</h5>
                <select id="heatmapSelect" class="form-select form-select-sm" style="width:auto">
                    <option value="annonces">Publications d'annonces</option>
                    <option value="inscriptions">Inscriptions</option>
                    @if(isset($stats['heatmaps']['vues']))<option value="vues">Vues et visites</option>@endif
                </select>
            </div>
            <div class="admin-card-body">
                <div class="table-responsive">
                    <table class="st-heat" id="heatmap">
                        <thead><tr><th></th>@for($h = 0; $h < 24; $h++)<th>{{ $h }}h</th>@endfor</tr></thead>
                        <tbody>
                        @foreach($jours as $d => $jour)
                            <tr><th>{{ $jour }}</th>@for($h = 0; $h < 24; $h++)<td data-d="{{ $d }}" data-h="{{ $h }}"></td>@endfor</tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="st-heat-legend">Moins <i></i> Plus · <span id="heatmapPeak"></span></div>
            </div>
        </div>
    </div>
</section>

{{-- ================= Annonces ================= --}}
<section class="st-panel" data-panel="annonces" hidden>
    <h3 class="st-panel-title"><i class="fas fa-newspaper"></i> Annonces</h3>

    <div class="st-block" id="st-annonces-plus-vues">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Les plus vues (cliquées)</h5>
                <a class="st-export" href="{{ $exportUrl('articles_vus') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_articles_vus']))
                    <div class="st-empty"><i class="fas fa-eye-slash"></i>Aucune vue enregistrée sur cette période.</div>
                @else
                    <div class="table-responsive">
                        <table class="st-table" data-sortable>
                            <thead><tr>
                                <th data-sort="num">#</th>
                                <th data-sort="text">Annonce</th>
                                <th data-sort="text">Vendeur</th>
                                <th data-sort="text">Ville</th>
                                <th data-sort="num" class="num">Prix</th>
                                <th data-sort="num" class="num">Vues</th>
                                <th data-sort="num" class="num">Visiteurs uniques</th>
                                <th data-sort="num" class="num">Likes</th>
                            </tr></thead>
                            <tbody>
                            @foreach($stats['top_articles_vus'] as $i => $a)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $a['titre'] }}">
                                        <div class="st-person">
                                            @if($a['photo'])<img class="st-thumb" src="{{ $a['photo'] }}" alt="" loading="lazy">@endif
                                            <div>
                                                @if($a['url'])<a href="{{ $a['url'] }}" target="_blank">{{ \Illuminate\Support\Str::limit($a['titre'], 50) }}</a>@else{{ $a['titre'] }}@endif
                                                <small>{{ $a['categorie'] ?? '' }} @if($a['admin_url'])· <a href="{{ $a['admin_url'] }}" class="fw-normal">fiche admin</a>@endif</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-value="{{ $a['vendeur'] }}">{{ $a['vendeur'] ?? '—' }}</td>
                                    <td data-value="{{ $a['lieu'] }}">{{ $a['lieu'] ?? '—' }}</td>
                                    <td class="num" data-value="{{ $a['prix'] ?? 0 }}">{{ $a['prix'] !== null ? $money($a['prix']) : '—' }}</td>
                                    <td class="num" data-value="{{ $a['vues'] }}"><strong>{{ $fmt($a['vues']) }}</strong></td>
                                    <td class="num" data-value="{{ $a['visiteurs'] }}">{{ $fmt($a['visiteurs']) }}</td>
                                    <td class="num" data-value="{{ $a['likes'] }}">{{ $fmt($a['likes']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="st-block" id="st-annonces-plus-aimees">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Les plus aimées <small class="text-muted fw-normal">— ajouts en favoris sur la période</small></h5>
                <a class="st-export" href="{{ $exportUrl('articles_aimes') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_articles_aimes']))
                    <div class="st-empty"><i class="fas fa-heart-crack"></i>Aucun like sur cette période.</div>
                @else
                    <div class="table-responsive">
                        <table class="st-table" data-sortable>
                            <thead><tr>
                                <th data-sort="num">#</th><th data-sort="text">Annonce</th><th data-sort="text">Vendeur</th>
                                <th data-sort="num" class="num">Likes</th><th data-sort="num" class="num">Vues</th>
                            </tr></thead>
                            <tbody>
                            @foreach($stats['top_articles_aimes'] as $i => $a)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $a['titre'] }}">
                                        <div class="st-person">
                                            @if($a['photo'])<img class="st-thumb" src="{{ $a['photo'] }}" alt="" loading="lazy">@endif
                                            <div>@if($a['url'])<a href="{{ $a['url'] }}" target="_blank">{{ \Illuminate\Support\Str::limit($a['titre'], 50) }}</a>@else{{ $a['titre'] }}@endif</div>
                                        </div>
                                    </td>
                                    <td data-value="{{ $a['vendeur'] }}">{{ $a['vendeur'] ?? '—' }}</td>
                                    <td class="num" data-value="{{ $a['likes'] }}"><strong>{{ $fmt($a['likes']) }}</strong></td>
                                    <td class="num" data-value="{{ $a['vues'] }}">{{ $fmt($a['vues']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="st-block" id="st-annonces-statut">
        <h4 class="st-block-title">Statut, neuf / occasion, livraison <small class="text-muted fw-normal fs-6">— annonces publiées sur la période</small></h4>
        <div class="st-grid-3">
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Statut</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartStatus"></canvas></div></div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Neuf / occasion</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartEtat"></canvas></div></div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Livraison</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartLivraison"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="st-block" id="st-annonces-prix">
        <h4 class="st-block-title">Prix</h4>
        <div class="st-grid-2">
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Tranches de prix (F CFA)</h5></div>
                <div class="admin-card-body"><div class="st-chart sm"><canvas id="chartPrix"></canvas></div></div>
            </div>
            <div class="st-kpis" style="align-content:start">
                <div class="st-kpi" style="--kpi:#eab308">
                    <div class="st-kpi-label"><span>Prix médian</span><i class="fas fa-coins"></i></div>
                    <div class="st-kpi-value">{{ $money($kpis['prix_median']) }}</div>
                    <div class="st-kpi-sub">la moitié des annonces sont moins chères</div>
                </div>
                <div class="st-kpi" style="--kpi:#f59e0b">
                    <div class="st-kpi-label"><span>Prix moyen</span><i class="fas fa-calculator"></i></div>
                    <div class="st-kpi-value">{{ $money($kpis['prix_moyen']) }}</div>
                    <div class="st-kpi-sub">tiré vers le haut par les articles chers</div>
                </div>
                <div class="st-kpi" style="--kpi:#a855f7">
                    <div class="st-kpi-label"><span>Annonces boostées</span><i class="fas fa-rocket"></i></div>
                    <div class="st-kpi-value">{{ $fmt($kpis['annonces_boostees']) }}</div>
                    <div class="st-kpi-sub">boost en cours aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>

    <div class="st-block" id="st-annonces-mots-cles">
        <div class="admin-card mt-4">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Mots-clés des titres</h5>
                <span>
                    <a class="st-export" href="{{ $exportUrl('mots') }}"><i class="fas fa-file-csv"></i> Mots</a>
                    <a class="st-export ms-2" href="{{ $exportUrl('expressions') }}"><i class="fas fa-file-csv"></i> Expressions</a>
                </span>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_mots']['mots']))
                    <div class="st-empty"><i class="fas fa-font"></i>Aucune annonce sur cette période.</div>
                @else
                    @php $maxMot = max(array_column($stats['top_mots']['mots'], 'annonces')); @endphp
                    <div class="st-grid-2">
                        <div>
                            <div class="st-cloud mb-3">
                                @foreach($stats['top_mots']['mots'] as $m)
                                    <span style="font-size: {{ 0.8 + ($maxMot ? $m['annonces'] / $maxMot : 0) * 0.7 }}rem" title="{{ $m['annonces'] }} annonces">{{ $m['mot'] }}<small>{{ $m['annonces'] }}</small></span>
                                @endforeach
                            </div>
                            @if(! empty($stats['top_mots']['expressions']))
                                <h6 class="mt-4 mb-2 fw-bold">Expressions fréquentes (2 mots)</h6>
                                <div class="st-cloud alt">
                                    @foreach($stats['top_mots']['expressions'] as $m)
                                        <span>{{ $m['mot'] }}<small>{{ $m['annonces'] }}</small></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <table class="st-table" data-sortable>
                            <thead><tr><th data-sort="num">#</th><th data-sort="text">Mot</th><th data-sort="num" class="num">Annonces</th><th data-sort="num" class="num">% des titres</th></tr></thead>
                            <tbody>
                            @foreach($stats['top_mots']['mots'] as $i => $m)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $m['mot'] }}"><strong>{{ $m['mot'] }}</strong></td>
                                    <td class="num" data-value="{{ $m['annonces'] }}">{{ $fmt($m['annonces']) }}</td>
                                    <td class="num" data-value="{{ $m['part'] }}">{{ $pct($m['part']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ================= Utilisateurs ================= --}}
<section class="st-panel" data-panel="utilisateurs" hidden>
    <h3 class="st-panel-title"><i class="fas fa-users"></i> Utilisateurs</h3>

    <div class="st-block" id="st-utilisateurs-inscriptions">
        <h4 class="st-block-title">Inscriptions et comptes{!! $globalBadge !!}</h4>
        <div class="st-kpis">
            <div class="st-kpi" style="--kpi:#6366f1">
                <div class="st-kpi-label"><span>Nouveaux inscrits</span><i class="fas fa-user-plus"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['inscriptions']['valeur']) }}</div>
                <div class="st-kpi-sub">{!! $trend($kpis['inscriptions']) !!} vs {{ $fmt($kpis['inscriptions']['precedent']) }}</div>
            </div>
            <div class="st-kpi" style="--kpi:#4f46e5">
                <div class="st-kpi-label"><span>Inscrits au total</span><i class="fas fa-users"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['utilisateurs_total']) }}</div>
                <div class="st-kpi-sub">{{ $pct($kpis['emails_verifies_pct']) }} d'emails vérifiés</div>
            </div>
            <div class="st-kpi" style="--kpi:#14b8a6">
                <div class="st-kpi-label"><span>Inscrits devenus vendeurs</span><i class="fas fa-percentage"></i></div>
                <div class="st-kpi-value">{{ $pct($kpis['conversion_vendeurs_pct']) }}</div>
                <div class="st-kpi-sub">des nouveaux inscrits ont publié</div>
            </div>
            <div class="st-kpi" style="--kpi:#2563eb">
                <div class="st-kpi-label"><span>Boutiques certifiées</span><i class="fas fa-circle-check"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['certifies']) }}</div>
                <div class="st-kpi-sub">certification en cours</div>
            </div>
            <div class="st-kpi" style="--kpi:#ef4444">
                <div class="st-kpi-label"><span>Comptes bloqués</span><i class="fas fa-user-slash"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['bloques']) }}</div>
                <div class="st-kpi-sub"><a href="#courbes/inscriptions">voir la courbe des inscriptions →</a></div>
            </div>
        </div>
    </div>

    <div class="st-block" id="st-utilisateurs-vendeurs">
        <div class="admin-card mt-4">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Vendeurs qui publient le plus <small class="text-muted fw-normal">— cliquez un en-tête pour trier</small></h5>
                <a class="st-export" href="{{ $exportUrl('vendeurs') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_vendeurs']))
                    <div class="st-empty"><i class="fas fa-user-slash"></i>Aucune annonce publiée sur cette période.</div>
                @else
                    @php $maxV = max(array_column($stats['top_vendeurs'], 'annonces')); @endphp
                    <div class="table-responsive">
                        <table class="st-table" data-sortable>
                            <thead><tr>
                                <th data-sort="num">#</th>
                                <th data-sort="text">Vendeur</th>
                                <th data-sort="num" class="num">Annonces</th>
                                <th></th>
                                <th data-sort="num" class="num">Approuvées</th>
                                <th data-sort="num" class="num">Total (tout temps)</th>
                                <th data-sort="num" class="num">Vues reçues</th>
                                <th data-sort="num" class="num">Likes reçus</th>
                                <th data-sort="num">Inscrit le</th>
                            </tr></thead>
                            <tbody>
                            @foreach($stats['top_vendeurs'] as $i => $v)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $v['nom'] }}">
                                        <div class="st-person">
                                            <img src="{{ $v['photo'] }}" alt="" loading="lazy">
                                            <div>
                                                @if($v['admin_url'])<a href="{{ $v['admin_url'] }}">{{ $v['nom'] }}</a>@else{{ $v['nom'] }}@endif
                                                @if($v['certifie'])<i class="fas fa-circle-check badge-cert" title="Certifié"></i>@endif
                                                <small>{{ $v['email'] }} @if($v['boutique_url'])· <a href="{{ $v['boutique_url'] }}" target="_blank" class="fw-normal">boutique</a>@endif</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="num" data-value="{{ $v['annonces'] }}"><strong>{{ $fmt($v['annonces']) }}</strong></td>
                                    <td><div class="st-bar"><span style="width: {{ $maxV ? round($v['annonces'] / $maxV * 100) : 0 }}%"></span></div></td>
                                    <td class="num" data-value="{{ $v['approuvees'] }}">{{ $fmt($v['approuvees']) }}</td>
                                    <td class="num" data-value="{{ $v['annonces_total'] }}">{{ $fmt($v['annonces_total']) }}</td>
                                    <td class="num" data-value="{{ $v['vues'] }}">{{ $fmt($v['vues']) }}</td>
                                    <td class="num" data-value="{{ $v['likes'] }}">{{ $fmt($v['likes']) }}</td>
                                    <td data-value="{{ $v['inscrit_le'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $v['inscrit_le'])->format('Ymd') : 0 }}">{{ $v['inscrit_le'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="st-block" id="st-utilisateurs-boutiques">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Boutiques (profils vendeurs) les plus visitées</h5>
                <a class="st-export" href="{{ $exportUrl('boutiques') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_boutiques']))
                    <div class="st-empty"><i class="fas fa-store-slash"></i>Aucune visite enregistrée sur cette période.</div>
                @else
                    <div class="table-responsive">
                        <table class="st-table" data-sortable>
                            <thead><tr>
                                <th data-sort="num">#</th>
                                <th data-sort="text">Vendeur</th>
                                <th data-sort="num" class="num">Visites boutique</th>
                                <th data-sort="num" class="num">Vues de ses annonces</th>
                                <th data-sort="num" class="num">Visiteurs uniques</th>
                                <th data-sort="num" class="num">Annonces en ligne</th>
                                <th data-sort="num">Son annonce la plus vue</th>
                            </tr></thead>
                            <tbody>
                            @foreach($stats['top_boutiques'] as $i => $b)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $b['nom'] }}">
                                        <div class="st-person">
                                            <img src="{{ $b['photo'] }}" alt="" loading="lazy">
                                            <div>
                                                @if($b['boutique_url'])<a href="{{ $b['boutique_url'] }}" target="_blank">{{ $b['nom'] }}</a>@else{{ $b['nom'] }}@endif
                                                @if($b['certifie'])<i class="fas fa-circle-check badge-cert" title="Certifié"></i>@endif
                                                <small>@if($b['admin_url'])<a href="{{ $b['admin_url'] }}" class="fw-normal">fiche admin</a>@endif</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="num" data-value="{{ $b['visites_boutique'] }}"><strong>{{ $fmt($b['visites_boutique']) }}</strong></td>
                                    <td class="num" data-value="{{ $b['vues_annonces'] }}">{{ $fmt($b['vues_annonces']) }}</td>
                                    <td class="num" data-value="{{ $b['visiteurs'] }}">{{ $fmt($b['visiteurs']) }}</td>
                                    <td class="num" data-value="{{ $b['annonces_en_ligne'] }}">{{ $fmt($b['annonces_en_ligne']) }}</td>
                                    <td class="st-best" data-value="{{ $b['meilleure_annonce']['vues'] ?? 0 }}">
                                        @if($b['meilleure_annonce'])
                                            <div class="st-person">
                                                @if($b['meilleure_annonce']['photo'])<img class="st-thumb" src="{{ $b['meilleure_annonce']['photo'] }}" alt="" loading="lazy">@endif
                                                <div>
                                                    @if($b['meilleure_annonce']['url'])
                                                        <a href="{{ $b['meilleure_annonce']['url'] }}" target="_blank">{{ \Illuminate\Support\Str::limit($b['meilleure_annonce']['titre'], 40) }}</a>
                                                    @else
                                                        {{ $b['meilleure_annonce']['titre'] }}
                                                    @endif
                                                    <small>{{ $fmt($b['meilleure_annonce']['vues']) }} vues</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ================= Catégories ================= --}}
<section class="st-panel" data-panel="categories" hidden>
    <h3 class="st-panel-title"><i class="fas fa-tags"></i> Catégories</h3>
    <div class="st-grid-2">
        @foreach([
            ['top_categories', 'categories', 'Catégories les plus publiées', false, 'categories'],
            ['top_sous_categories', 'sous_categories', 'Sous-catégories les plus publiées', true, 'sous-categories'],
        ] as [$key, $section, $title, $withParent, $anchor])
            <div class="st-block" id="st-categories-{{ $anchor }}">
                <div class="admin-card mb-0">
                    <div class="admin-card-header st-card-head">
                        <h5 class="admin-card-title">{{ $title }}</h5>
                        <a class="st-export" href="{{ $exportUrl($section) }}"><i class="fas fa-file-csv"></i> Exporter</a>
                    </div>
                    <div class="admin-card-body">
                        @if(empty($stats[$key]))
                            <div class="st-empty"><i class="fas fa-tags"></i>Aucune donnée sur cette période.</div>
                        @else
                            <div class="table-responsive">
                                <table class="st-table" data-sortable>
                                    <thead><tr>
                                        <th data-sort="num">#</th>
                                        <th data-sort="text">{{ $withParent ? 'Sous-catégorie' : 'Catégorie' }}</th>
                                        <th data-sort="num" class="num">Annonces</th>
                                        <th data-sort="num">Part</th>
                                        <th data-sort="num" class="num">Vues</th>
                                    </tr></thead>
                                    <tbody>
                                    @foreach($stats[$key] as $i => $c)
                                        <tr>
                                            <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                            <td data-value="{{ $c['nom'] }}">
                                                <strong>{{ $c['nom'] }}</strong>
                                                @if($withParent)<small class="d-block text-muted">{{ $c['categorie'] }}</small>@endif
                                            </td>
                                            <td class="num" data-value="{{ $c['annonces'] }}">{{ $fmt($c['annonces']) }}</td>
                                            <td data-value="{{ $c['part'] }}">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="st-bar flex-grow-1"><span style="width: {{ $c['part'] }}%"></span></div>
                                                    <small class="text-muted">{{ $pct($c['part']) }}</small>
                                                </div>
                                            </td>
                                            <td class="num" data-value="{{ $c['vues'] }}">{{ $fmt($c['vues']) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= Villes ================= --}}
<section class="st-panel" data-panel="villes" hidden>
    <h3 class="st-panel-title"><i class="fas fa-map-marker-alt"></i> Villes</h3>
    <div class="st-block" id="st-villes-villes">
        <div class="admin-card">
            <div class="admin-card-header st-card-head">
                <h5 class="admin-card-title">Villes où l'on publie le plus</h5>
                <a class="st-export" href="{{ $exportUrl('villes') }}"><i class="fas fa-file-csv"></i> Exporter</a>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_villes']))
                    <div class="st-empty"><i class="fas fa-map"></i>Aucune donnée sur cette période.</div>
                @else
                    <div class="st-grid-2">
                        <div class="st-chart"><canvas id="chartVilles"></canvas></div>
                        <table class="st-table" data-sortable>
                            <thead><tr><th data-sort="num">#</th><th data-sort="text">Ville</th><th data-sort="num" class="num">Annonces</th><th data-sort="num" class="num">Part</th></tr></thead>
                            <tbody>
                            @foreach($stats['top_villes'] as $i => $v)
                                <tr>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $v['nom'] }}">
                                        <strong>{{ $v['nom'] }}</strong>
                                        @if(! $filters->ville)
                                            <a href="{{ route('admin.statistics.index', ['ville' => $v['nom']] + $query) }}" class="st-keep-hash ms-1 small" title="Filtrer toute la page sur {{ $v['nom'] }}"><i class="fas fa-filter"></i></a>
                                        @endif
                                    </td>
                                    <td class="num" data-value="{{ $v['annonces'] }}">{{ $fmt($v['annonces']) }}</td>
                                    <td class="num" data-value="{{ $v['part'] }}">{{ $pct($v['part']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ================= Recherches ================= --}}
<section class="st-panel" data-panel="recherches" hidden>
    <h3 class="st-panel-title"><i class="fas fa-search"></i> Ce que cherchent les visiteurs{!! $globalBadge !!}</h3>

    <div class="st-kpis mb-4">
        <div class="st-kpi" style="--kpi:#0ea5e9">
            <div class="st-kpi-label"><span>Recherches</span><i class="fas fa-search"></i></div>
            <div class="st-kpi-value">{{ $fmt($r['total']) }}</div>
            <div class="st-kpi-sub">par {{ $fmt($r['chercheurs']) }} visiteurs</div>
        </div>
        <div class="st-kpi" style="--kpi:#ef4444">
            <div class="st-kpi-label"><span>Sans résultat</span><i class="fas fa-circle-exclamation"></i></div>
            <div class="st-kpi-value">{{ $pct($r['sans_resultat_pct']) }}</div>
            <div class="st-kpi-sub">{{ $fmt($r['sans_resultat']) }} recherches n'ont rien trouvé</div>
        </div>
    </div>

    <div class="st-grid-2">
        <div class="st-block" id="st-recherches-frequentes">
            <div class="admin-card mb-0">
                <div class="admin-card-header st-card-head">
                    <h5 class="admin-card-title">Les plus fréquentes</h5>
                    <a class="st-export" href="{{ $exportUrl('recherches') }}"><i class="fas fa-file-csv"></i> Exporter</a>
                </div>
                <div class="admin-card-body">
                    @if(empty($r['top']))
                        <div class="st-empty"><i class="fas fa-search"></i>Aucune recherche enregistrée sur cette période.</div>
                    @else
                        <div class="table-responsive">
                            <table class="st-table" data-sortable>
                                <thead><tr>
                                    <th data-sort="num">#</th><th data-sort="text">Recherche</th>
                                    <th data-sort="num" class="num">Fois</th><th data-sort="num" class="num">Visiteurs</th>
                                    <th data-sort="num" class="num">Résultats moy.</th>
                                </tr></thead>
                                <tbody>
                                @foreach($r['top'] as $i => $t)
                                    <tr>
                                        <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                        <td data-value="{{ $t['terme'] }}"><strong>{{ $t['terme'] }}</strong></td>
                                        <td class="num" data-value="{{ $t['recherches'] }}">{{ $fmt($t['recherches']) }}</td>
                                        <td class="num" data-value="{{ $t['chercheurs'] }}">{{ $fmt($t['chercheurs']) }}</td>
                                        <td class="num" data-value="{{ $t['resultats_moyens'] }}">
                                            @if($t['resultats_moyens'] === 0)<span class="text-danger fw-semibold">0</span>@else{{ $fmt($t['resultats_moyens']) }}@endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="st-block" id="st-recherches-sans-resultat">
            <div class="admin-card mb-0">
                <div class="admin-card-header st-card-head">
                    <h5 class="admin-card-title">Sans résultat <small class="text-muted fw-normal">— produits demandés mais absents</small></h5>
                    <a class="st-export" href="{{ $exportUrl('recherches_vides') }}"><i class="fas fa-file-csv"></i> Exporter</a>
                </div>
                <div class="admin-card-body">
                    @if(empty($r['sans_resultat_top']))
                        <div class="st-empty"><i class="fas fa-check-circle"></i>Toutes les recherches ont trouvé des annonces.</div>
                    @else
                        <div class="table-responsive">
                            <table class="st-table" data-sortable>
                                <thead><tr>
                                    <th data-sort="num">#</th><th data-sort="text">Recherche</th>
                                    <th data-sort="num" class="num">Fois</th><th data-sort="num" class="num">Visiteurs</th><th data-sort="num">Dernière</th>
                                </tr></thead>
                                <tbody>
                                @foreach($r['sans_resultat_top'] as $i => $t)
                                    <tr>
                                        <td data-value="{{ $i + 1 }}"><span class="st-rank">{{ $i + 1 }}</span></td>
                                        <td data-value="{{ $t['terme'] }}"><strong>{{ $t['terme'] }}</strong></td>
                                        <td class="num" data-value="{{ $t['recherches'] }}">{{ $fmt($t['recherches']) }}</td>
                                        <td class="num" data-value="{{ $t['chercheurs'] }}">{{ $fmt($t['chercheurs']) }}</td>
                                        <td data-value="{{ \Carbon\Carbon::createFromFormat('d/m/Y H:i', $t['derniere'])->format('YmdHi') }}"><small>{{ $t['derniere'] }}</small></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="st-block mt-4" id="st-recherches-sources">
        <div class="admin-card">
            <div class="admin-card-header"><h5 class="admin-card-title">D'où viennent les recherches</h5></div>
            <div class="admin-card-body"><div class="st-chart sm" style="height:180px"><canvas id="chartSources"></canvas></div></div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/2.0.1/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/fr.min.js"></script>
<script>
(function () {
    const stats = @json($chartData);
    const jours = @json($jours);
    const nf = new Intl.NumberFormat('fr-FR');
    const form = document.getElementById('statsFilters');
    const finePointer = window.matchMedia('(pointer: fine)').matches;
    const frDate = iso => { const [y, m, d] = iso.split('-'); return `${d}/${m}/${y}`; };

    // ================= Barre collée sous l'en-tête de l'admin =================
    const topbar = document.querySelector('.top-navbar');
    const syncNavTop = () => document.documentElement.style.setProperty('--st-nav-top', (topbar ? topbar.offsetHeight : 0) + 'px');
    syncNavTop();
    window.addEventListener('resize', syncNavTop);

    // ================= Navigation vers une autre période / un autre filtre =================
    // Construit l'URL de la page en gardant les filtres et l'onglet ouvert.
    function goTo(changes) {
        const params = new URLSearchParams(window.location.search);
        params.delete('refresh');
        Object.entries(changes).forEach(([k, v]) => (v === null || v === '') ? params.delete(k) : params.set(k, v));
        window.location.href = stats.baseUrl + (params.toString() ? '?' + params : '') + window.location.hash;
    }

    form.addEventListener('submit', function () {
        // Période prédéfinie : inutile d'envoyer les dates
        if (document.getElementById('periode').value !== 'perso') {
            document.getElementById('du').disabled = true;
            document.getElementById('au').disabled = true;
        }
        // Champs vides (« Toutes les villes »…) : URL plus propre
        form.querySelectorAll('select').forEach(el => { if (el.value === '') el.disabled = true; });
        form.action = stats.baseUrl + window.location.hash;
    });
    form.querySelectorAll('.st-autosubmit').forEach(select => select.addEventListener('change', () => {
        // Le regroupement automatique suit la période ; un choix manuel est gardé
        form.requestSubmit ? form.requestSubmit() : form.submit();
    }));
    document.querySelectorAll('.st-keep-hash').forEach(a => a.addEventListener('click', () => {
        a.href = a.href.split('#')[0] + window.location.hash;
    }));

    // ================= Calendrier =================
    const dateBtn = document.getElementById('dateRangeBtn');
    if (window.flatpickr) {
        if (flatpickr.l10ns && flatpickr.l10ns.fr) {
            flatpickr.localize(flatpickr.l10ns.fr);
        }
        const fp = flatpickr(document.getElementById('dateRangeInput'), {
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: [stats.periode.du, stats.periode.au],
            maxDate: 'today',
            showMonths: window.innerWidth > 768 ? 2 : 1,
            clickOpens: false,
            disableMobile: true,
            positionElement: dateBtn,
            onReady(selected, str, instance) {
                const cal = instance.calendarContainer;
                cal.classList.add('st-cal');
                // Mois + jours regroupés dans une colonne, raccourcis dans une autre
                const main = document.createElement('div');
                main.className = 'st-cal-main';
                Array.from(cal.children).forEach(child => main.appendChild(child));
                const presets = document.createElement('div');
                presets.className = 'st-presets';
                presets.innerHTML = '<div class="st-presets-title">Raccourcis</div>';
                const current = document.getElementById('periode').value;
                Object.entries(stats.presets).forEach(([key, label]) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = label;
                    if (key === current) b.classList.add('active');
                    b.addEventListener('click', () => goTo({ periode: key, du: null, au: null, par: null }));
                    presets.appendChild(b);
                });
                cal.appendChild(presets);
                cal.appendChild(main);
            },
            onOpen(selected, str, instance) {
                // Affiche les mois les plus récents de la sélection (le dernier à droite)
                instance.jumpToDate(stats.periode.au);
                if (instance.config.showMonths > 1 && stats.periode.du.slice(0, 7) !== stats.periode.au.slice(0, 7)) {
                    instance.changeMonth(-1);
                }
            },
            onChange(selected) {
                if (selected.length === 2) {
                    const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                    goTo({ periode: 'perso', du: iso(selected[0]), au: iso(selected[1]), par: null });
                }
            },
        });
        dateBtn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); fp.toggle(); });
    } else {
        // Repli : deux champs de date natifs
        document.getElementById('dateFallback').hidden = false;
        dateBtn.disabled = true;
        ['du', 'au'].forEach(id => document.getElementById(id).addEventListener('change', () => {
            document.getElementById('periode').value = 'perso';
        }));
    }

    // ================= Tri des tableaux =================
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        table.querySelectorAll('th[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const index = Array.from(th.parentNode.children).indexOf(th);
                const numeric = th.dataset.sort === 'num';
                const asc = th.classList.contains('desc') || (!th.classList.contains('asc') && !numeric);
                table.querySelectorAll('th').forEach(h => h.classList.remove('asc', 'desc'));
                th.classList.add(asc ? 'asc' : 'desc');

                const tbody = table.tBodies[0];
                const rows = Array.from(tbody.rows);
                rows.sort((a, b) => {
                    const va = a.cells[index]?.dataset.value ?? '';
                    const vb = b.cells[index]?.dataset.value ?? '';
                    const cmp = numeric ? (parseFloat(va) || 0) - (parseFloat(vb) || 0) : va.localeCompare(vb, 'fr', { sensitivity: 'base' });
                    return asc ? cmp : -cmp;
                });
                rows.forEach(r => tbody.appendChild(r));
            });
        });
    });

    // ================= Heatmap =================
    const heatmapSelect = document.getElementById('heatmapSelect');
    function drawHeatmap(key) {
        const grid = stats.heatmaps[key] || [];
        let max = 0, peak = null;
        grid.forEach((row, d) => row.forEach((v, h) => { if (v > max) { max = v; peak = [d, h]; } }));
        document.querySelectorAll('#heatmap td').forEach(td => {
            const v = grid[td.dataset.d]?.[td.dataset.h] ?? 0;
            const t = max ? v / max : 0;
            const c = (a, b) => Math.round(a + (b - a) * t); // #eef2ff → #4338ca
            td.style.background = v ? `rgb(${c(238, 67)}, ${c(242, 56)}, ${c(255, 202)})` : '#f9fafb';
            td.textContent = v || '';
            td.title = `${jours[td.dataset.d]} ${td.dataset.h}h : ${nf.format(v)}`;
        });
        document.getElementById('heatmapPeak').textContent = peak
            ? `pic : ${jours[peak[0]]} vers ${peak[1]}h (${nf.format(max)})`
            : 'aucune activité sur la période';
    }
    heatmapSelect.addEventListener('change', () => drawHeatmap(heatmapSelect.value));
    drawHeatmap(heatmapSelect.value);

    // ================= Graphiques (créés à l'ouverture de leur onglet) =================
    const hasChart = typeof Chart !== 'undefined';
    if (hasChart) {
        if (window.ChartZoom) Chart.register(window.ChartZoom);
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.tooltip.callbacks.label = ctx => ` ${ctx.dataset.label || ctx.label} : ${nf.format(ctx.parsed.y ?? ctx.parsed)}`;
    }

    const s = stats.series;
    const r = stats.repartitions;
    const emptyChart = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.parentNode.innerHTML = `<div class="st-empty"><i class="fas fa-chart-pie"></i>${text}</div>`;
    };

    // ---- Sélection sur les courbes (zoom synchronisé) ----
    const timeCharts = [];
    let syncing = false;
    let selection = null;
    const selBar = document.getElementById('chartSelection');

    function showSelection(minIdx, maxIdx) {
        minIdx = Math.max(0, Math.round(minIdx));
        maxIdx = Math.min(s.labels.length - 1, Math.round(maxIdx));
        if (minIdx === 0 && maxIdx === s.labels.length - 1) {
            selection = null;
            selBar.hidden = true;
            return;
        }
        selection = { du: s.debuts[minIdx], au: s.fins[maxIdx] };
        const days = Math.round((new Date(selection.au) - new Date(selection.du)) / 86400000) + 1;
        document.getElementById('selectionLabel').textContent =
            selection.du === selection.au ? `le ${frDate(selection.du)}` : `du ${frDate(selection.du)} au ${frDate(selection.au)} (${days} jours)`;
        selBar.hidden = false;
    }

    function syncZoom(source) {
        if (syncing) return;
        syncing = true;
        const { min, max } = source.scales.x;
        timeCharts.forEach(c => { if (c !== source) c.zoomScale('x', { min, max }, 'none'); });
        syncing = false;
        showSelection(min, max);
    }

    document.getElementById('selectionApply').addEventListener('click', () => {
        if (selection) goTo({ periode: 'perso', du: selection.du, au: selection.au, par: null });
    });
    document.getElementById('selectionReset').addEventListener('click', () => {
        timeCharts.forEach(c => c.resetZoom('none'));
        selection = null;
        selBar.hidden = true;
    });

    function timeOptions(extraScales) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: Object.assign({
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
            }, extraScales || {}),
            plugins: {
                zoom: {
                    limits: { x: { min: 'original', max: 'original', minRange: 0 } },
                    zoom: {
                        drag: { enabled: finePointer, backgroundColor: 'rgba(99,102,241,.15)', borderColor: '#6366f1', borderWidth: 1, threshold: 8 },
                        pinch: { enabled: true },
                        wheel: { enabled: false },
                        mode: 'x',
                        onZoomComplete: ({ chart }) => syncZoom(chart),
                    },
                    pan: { enabled: !finePointer, mode: 'x', onPanComplete: ({ chart }) => syncZoom(chart) },
                },
            },
            // Clic / toucher sur un point : sélectionne cette période
            onClick(evt, elements, chart) {
                if (chart.$dragMoved) return;
                const points = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                if (points.length) showSelection(points[0].index, points[0].index);
            },
        };
    }

    function trackDrag(chart) {
        // Distingue un clic d'un glissement (le glissement sert à la sélection)
        let start = null;
        chart.canvas.addEventListener('mousedown', e => { start = [e.clientX, e.clientY]; chart.$dragMoved = false; });
        chart.canvas.addEventListener('mouseup', e => {
            chart.$dragMoved = start && Math.hypot(e.clientX - start[0], e.clientY - start[1]) > 5;
        });
        return chart;
    }

    const line = (label, data, color, extra = {}) => ({
        label, data, borderColor: color, backgroundColor: color + '22', pointBackgroundColor: color,
        borderWidth: 2, tension: .3, pointRadius: data.length > 60 ? 0 : 3, pointHoverRadius: 5, fill: false, ...extra,
    });

    const doughnut = (id, data, colors) => {
        const labels = Object.keys(data), values = Object.values(data);
        if (!values.some(v => v > 0)) return emptyChart(id, 'Aucune donnée');
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    return ` ${ctx.label} : ${nf.format(ctx.parsed)} (${total ? Math.round(ctx.parsed / total * 100) : 0} %)`;
                } } },
            } },
        });
    };

    const bar = (id, labels, values, color, horizontal = false) => new Chart(document.getElementById(id), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Annonces', data: values, backgroundColor: color, borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, indexAxis: horizontal ? 'y' : 'x', plugins: { legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${nf.format(horizontal ? ctx.parsed.x : ctx.parsed.y)}` } } },
            scales: { x: { beginAtZero: true, grid: { display: horizontal }, ticks: { precision: 0 } }, y: { beginAtZero: true, grid: { display: !horizontal }, ticks: { precision: 0 } } } },
    });

    const panelCharts = {
        courbes() {
            timeCharts.push(trackDrag(new Chart(document.getElementById('chartActivity'), {
                type: 'line',
                data: { labels: s.labels, datasets: [
                    line('Inscriptions', s.inscriptions, '#6366f1'),
                    line('Annonces publiées', s.annonces, '#f97316'),
                    line('Likes', s.likes, '#ef4444'),
                ] },
                options: timeOptions(),
            })));

            timeCharts.push(trackDrag(new Chart(document.getElementById('chartUsers'), {
                data: { labels: s.labels, datasets: [
                    { type: 'bar', label: 'Nouveaux inscrits', data: s.inscriptions, backgroundColor: '#6366f1', borderRadius: 4, yAxisID: 'y' },
                    { type: 'line', label: 'Total inscrits', data: s.inscrits_cumules, borderColor: '#10b981', backgroundColor: '#10b98122', fill: true, tension: .3, pointRadius: 0, borderWidth: 2, yAxisID: 'y1' },
                ] },
                options: timeOptions({
                    y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Nouveaux' } },
                    y1: { position: 'right', grid: { display: false }, ticks: { precision: 0 }, title: { display: true, text: 'Total' } },
                }),
            })));

            timeCharts.push(trackDrag(new Chart(document.getElementById('chartAudience'), {
                type: 'line',
                data: { labels: s.labels, datasets: [
                    line('Vues d\'annonces', s.vues, '#3b82f6', { fill: true }),
                    line('Visites de boutiques', s.visites_boutiques, '#8b5cf6'),
                    line('Recherches', s.recherches, '#0ea5e9'),
                ] },
                options: timeOptions(),
            })));

            if (!finePointer) {
                document.getElementById('zoomHintText').textContent = 'Écartez deux doigts sur une courbe pour zoomer, ou touchez un point.';
            }
            if (!window.ChartZoom) {
                document.getElementById('zoomHint').innerHTML = '<i class="fas fa-info-circle"></i> Cliquez sur un point pour sélectionner cette période.';
            }
        },
        annonces() {
            doughnut('chartStatus', r.statut, ['#10b981', '#f59e0b', '#ef4444']);
            doughnut('chartEtat', r.etat, ['#6366f1', '#a5b4fc']);
            doughnut('chartLivraison', r.livraison, ['#0ea5e9', '#cbd5e1']);
            bar('chartPrix', Object.keys(r.tranches_prix), Object.values(r.tranches_prix), '#eab308');
        },
        villes() {
            if (document.getElementById('chartVilles')) {
                const villes = stats.villes.slice(0, 10);
                bar('chartVilles', villes.map(v => v.nom), villes.map(v => v.annonces), '#f97316', true);
            }
        },
        recherches() {
            if (!Object.keys(r.sources_recherche).length) return emptyChart('chartSources', 'Pas encore de recherches enregistrées.');
            new Chart(document.getElementById('chartSources'), {
                type: 'bar',
                data: { labels: Object.keys(r.sources_recherche), datasets: [{ label: 'Recherches', data: Object.values(r.sources_recherche), backgroundColor: ['#0ea5e9', '#6366f1', '#14b8a6'], borderRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${nf.format(ctx.parsed.x)}` } } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } },
            });
        },
    };
    const initialised = new Set();

    function initPanel(group) {
        if (initialised.has(group)) return;
        initialised.add(group);
        if (!panelCharts[group]) return;
        if (!hasChart) {
            document.querySelectorAll(`[data-panel="${group}"] .st-chart`).forEach(el =>
                el.innerHTML = '<div class="st-empty"><i class="fas fa-triangle-exclamation"></i>Graphiques indisponibles (bibliothèque non chargée).</div>');
            return;
        }
        panelCharts[group]();
    }

    // ================= Onglets et listes déroulantes =================
    const groups = Array.from(document.querySelectorAll('.st-tab-btn')).map(b => b.dataset.group);
    const tabsWrap = document.getElementById('statsTabs');

    function closeMenus() {
        document.querySelectorAll('.st-menu').forEach(m => m.hidden = true);
        document.querySelectorAll('.st-tab-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
    }

    document.querySelectorAll('.st-tab-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const menu = document.getElementById('st-menu-' + btn.dataset.group);
            const open = !menu.hidden;
            closeMenus();
            if (open) return;
            // Aligne la liste sous le bouton (pleine largeur sur téléphone, via CSS)
            menu.style.left = Math.max(0, btn.offsetLeft - btn.parentNode.scrollLeft) + 'px';
            menu.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            menu.querySelector('a')?.focus({ preventScroll: true });
        });
    });
    document.addEventListener('click', e => { if (!tabsWrap.contains(e.target)) closeMenus(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });
    document.querySelectorAll('.st-menu a').forEach(a => a.addEventListener('click', () => closeMenus()));

    function show(hash, scroll) {
        const [rawGroup, item] = (hash || '').replace(/^#/, '').split('/');
        const group = groups.includes(rawGroup) ? rawGroup : 'ensemble';

        document.querySelectorAll('.st-panel').forEach(p => p.hidden = p.dataset.panel !== group);
        document.querySelectorAll('.st-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.group === group));
        document.querySelector(`.st-tab-btn[data-group="${group}"]`)?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        initPanel(group);

        if (!scroll) return;
        const target = item && document.getElementById(`st-${group}-${item}`);
        if (target) {
            requestAnimationFrame(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        } else {
            const top = tabsWrap.getBoundingClientRect().top + window.scrollY - (topbar ? topbar.offsetHeight : 0);
            if (window.scrollY > top) window.scrollTo({ top, behavior: 'smooth' });
        }
    }

    window.addEventListener('hashchange', () => show(window.location.hash, true));
    show(window.location.hash, Boolean(window.location.hash.includes('/')));
})();
</script>
@endpush
