@extends('admin.layout')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $pct = fn ($n) => number_format((float) $n, 1, ',', ' ') . ' %';
    $money = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' F';
    $query = array_filter(request()->only(['periode', 'du', 'au', 'par', 'top', 'ville', 'categorie']), fn ($v) => $v !== null && $v !== '');
    $exportUrl = fn (string $section) => route('admin.statistics.export', ['section' => $section] + $query);
    $params = $filters->queryParams();
    $detailUrl = fn (string $type, array $extra = []) => route('admin.statistics.details', ['type' => $type] + $extra + $params);
    $ficheAnnonce = fn ($id) => route('admin.statistics.annonce', ['article' => $id] + $params);
    $ficheVendeur = fn ($id) => route('admin.statistics.vendeur', ['user' => $id] + $params);
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
        'links' => [
            'statut' => [
                'Approuvées' => $detailUrl('annonces', ['statut' => 'approved']),
                'En attente' => $detailUrl('annonces', ['statut' => 'pending']),
                'Bloquées' => $detailUrl('annonces', ['statut' => 'blocked']),
            ],
            'etat' => ['Neuf' => $detailUrl('annonces', ['etat' => 'neuf']), 'Occasion' => $detailUrl('annonces', ['etat' => 'occasion'])],
            'livraison' => ['Avec livraison' => $detailUrl('annonces', ['livraison' => 1]), 'Sans livraison' => $detailUrl('annonces', ['livraison' => 0])],
            'prix' => collect(\App\Services\AdminStatistics::PRICE_RANGES)->mapWithKeys(fn ($r, $key) => [$r[0] => $detailUrl('annonces', ['prix' => $key])])->all(),
            'villes' => collect($stats['top_villes'])->mapWithKeys(fn ($v) => [$v['nom'] => route('admin.statistics.index', ['ville' => $v['nom']] + $params)])->all(),
            'sources' => [
                "Barre d'accueil" => $detailUrl('recherches', ['source' => 'accueil']),
                'Page de recherche' => $detailUrl('recherches', ['source' => 'recherche']),
                'Recherche en direct' => $detailUrl('recherches', ['source' => 'recherche_directe']),
            ],
        ],
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

@include('admin.statistics.partials._styles')

@section('content')

@include('admin.statistics.partials._filters', ['showGrouping' => true, 'meta' => 'calculé le ' . $stats['generated_at']])

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

        {{-- Champions de la période --}}
        @php
            $topBoutique = $stats['top_boutiques'][0] ?? null;
            $topVue = $stats['top_articles_vus'][0] ?? null;
            $topAimee = $stats['top_articles_aimes'][0] ?? null;
            $topVendeur = $stats['top_vendeurs'][0] ?? null;
            $topCategorie = $stats['top_categories'][0] ?? null;
            $topVille = $stats['top_villes'][0] ?? null;
            $topRecherche = $r['top'][0] ?? null;
            $champions = [
                ['Boutique la plus visitée', 'fa-shop', '#ede9fe', '#6d28d9', $topBoutique['photo'] ?? null, $topBoutique['nom'] ?? null,
                    $topBoutique ? $fmt($topBoutique['visites_boutique']) . ' visites de la boutique' : null,
                    $topBoutique ? $ficheVendeur($topBoutique['id']) : null],
                ['Annonce la plus vue', 'fa-eye', '#dbeafe', '#1d4ed8', $topVue['photo'] ?? null, $topVue['titre'] ?? null,
                    $topVue ? $fmt($topVue['vues']) . ' vues · ' . $fmt($topVue['visiteurs']) . ' visiteurs' : null,
                    $topVue ? $ficheAnnonce($topVue['id']) : null],
                ['Annonce la plus aimée', 'fa-heart', '#fee2e2', '#b91c1c', $topAimee['photo'] ?? null, $topAimee['titre'] ?? null,
                    $topAimee ? $fmt($topAimee['likes']) . ' likes' : null,
                    $topAimee ? $ficheAnnonce($topAimee['id']) : null],
                ['Vendeur le plus actif', 'fa-trophy', '#fef3c7', '#92400e', $topVendeur['photo'] ?? null, $topVendeur['nom'] ?? null,
                    $topVendeur ? $fmt($topVendeur['annonces']) . ' annonces publiées' : null,
                    $topVendeur ? $ficheVendeur($topVendeur['id']) : null],
                ['Catégorie n°1', 'fa-tags', '#dcfce7', '#15803d', null, $topCategorie['nom'] ?? null,
                    $topCategorie ? $fmt($topCategorie['annonces']) . ' annonces · ' . $pct($topCategorie['part']) : null,
                    $topCategorie ? route('admin.statistics.index', ['categorie' => 'c' . $topCategorie['id']] + $params) : null],
                ['Ville n°1', 'fa-map-marker-alt', '#ffedd5', '#c2410c', null, $topVille['nom'] ?? null,
                    $topVille ? $fmt($topVille['annonces']) . ' annonces · ' . $pct($topVille['part']) : null,
                    $topVille ? route('admin.statistics.index', ['ville' => $topVille['nom']] + $params) : null],
                ['Recherche n°1', 'fa-search', '#e0f2fe', '#0369a1', null, $topRecherche['terme'] ?? null,
                    $topRecherche ? $fmt($topRecherche['recherches']) . ' recherches' : null,
                    $topRecherche ? route('admin.statistics.details', ['type' => 'annonces', 'recherche' => $topRecherche['terme'], 'periode' => 'tout']) : null],
            ];
        @endphp
        <div class="st-champions">
            @foreach($champions as [$label, $icon, $bg, $color, $photo, $name, $value, $href])
                @if($href)
                    <a class="st-champion" href="{{ $href }}" style="--chip-bg: {{ $bg }}; --chip: {{ $color }}">
                @else
                    <div class="st-champion empty" style="--chip-bg: {{ $bg }}; --chip: {{ $color }}">
                @endif
                    <div class="st-champion-icon">@if($photo)<img src="{{ $photo }}" alt="">@else<i class="fas {{ $icon }}"></i>@endif</div>
                    <div class="st-champion-body">
                        <div class="st-champion-label"><i class="fas {{ $icon }}"></i> {{ $label }}</div>
                        <div class="st-champion-name" title="{{ $name }}">{{ $name ?? 'Pas encore de données' }}</div>
                        <div class="st-champion-value">{{ $value ?? 'sur cette période' }}</div>
                    </div>
                    @if($href)<i class="fas fa-angle-right text-muted"></i>@endif
                @if($href)</a>@else</div>@endif
            @endforeach
        </div>

        @php
            $cards = [
                ['Nouveaux inscrits', 'fa-user-plus', '#6366f1', $kpis['inscriptions'], $fmt($kpis['utilisateurs_total']) . ' inscrits au total', $detailUrl('inscrits'), true],
                ['Annonces publiées', 'fa-newspaper', '#f97316', $kpis['annonces'], $fmt($kpis['annonces_en_ligne']) . ' en ligne · ' . $fmt($kpis['annonces_en_attente']) . ' en attente', $detailUrl('annonces'), false],
                ['Vendeurs actifs', 'fa-store', '#10b981', $kpis['vendeurs_actifs'], 'ont publié sur la période', $detailUrl('vendeurs'), false],
                ["Vues d'annonces", 'fa-eye', '#3b82f6', $kpis['vues_annonces'], $fmt($kpis['visiteurs_uniques']) . ' visiteurs uniques', $detailUrl('annonces-vues'), false],
                ['Visites de boutiques', 'fa-shop', '#8b5cf6', $kpis['visites_boutiques'], 'pages boutique consultées', $detailUrl('boutiques'), false],
                ['Recherches', 'fa-search', '#0ea5e9', $kpis['recherches'], $pct($r['sans_resultat_pct']) . ' sans résultat', $detailUrl('recherches'), true],
                ['Likes (favoris)', 'fa-heart', '#ef4444', $kpis['likes'], 'ajouts en favoris', $detailUrl('likes'), false],
            ];
        @endphp
        <div class="st-kpis">
            @foreach($cards as [$label, $icon, $color, $metric, $sub, $link, $isGlobal])
                <a class="st-kpi" href="{{ $link }}" style="--kpi: {{ $color }}" title="Voir le détail">
                    <div class="st-kpi-label"><span>{{ $label }}{!! $isGlobal ? $globalBadge : '' !!}</span><i class="fas {{ $icon }}"></i></div>
                    <div class="st-kpi-value">{{ $fmt($metric['valeur']) }}</div>
                    <div class="st-kpi-sub">{!! $trend($metric) !!} <span title="Période précédente">vs {{ $fmt($metric['precedent']) }}</span></div>
                    <div class="st-kpi-sub mt-1">{{ $sub }}</div>
                </a>
            @endforeach
            <a class="st-kpi" href="{{ $detailUrl('inscrits', ['vendeur' => 1]) }}" style="--kpi: #14b8a6" title="Voir le détail">
                <div class="st-kpi-label"><span>Inscrits devenus vendeurs{!! $globalBadge !!}</span><i class="fas fa-percentage"></i></div>
                <div class="st-kpi-value">{{ $pct($kpis['conversion_vendeurs_pct']) }}</div>
                <div class="st-kpi-sub">des nouveaux inscrits ont publié au moins une annonce</div>
            </a>
            <a class="st-kpi" href="{{ $detailUrl('annonces', ['tri' => 'prix']) }}" style="--kpi: #eab308" title="Voir le détail">
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('annonces-vues') }}">Les plus vues (cliquées)</a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('annonces-vues') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('articles_vus') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                <tr @if($a['url']) data-href="{{ $ficheAnnonce($a['id']) }}" tabindex="0" @endif>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $a['titre'] }}">
                                        <div class="st-person">
                                            @if($a['photo'])<img class="st-thumb" src="{{ $a['photo'] }}" alt="" loading="lazy">@endif
                                            <div>
                                                @if($a['url'])<a href="{{ $ficheAnnonce($a['id']) }}">{{ \Illuminate\Support\Str::limit($a['titre'], 50) }}</a>@else{{ $a['titre'] }}@endif
                                                <small>{{ $a['categorie'] ?? '' }} @if($a['url'])· <a href="{{ $a['url'] }}" target="_blank" class="fw-normal">voir sur le site</a>@endif</small>
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('annonces-aimees') }}">Les plus aimées <small class="text-muted fw-normal">— ajouts en favoris sur la période</small></a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('likes') }}"><i class="fas fa-heart"></i> Tous les likes</a><a class="st-see-all" href="{{ $detailUrl('annonces-aimees') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('articles_aimes') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                <tr @if($a['url']) data-href="{{ $ficheAnnonce($a['id']) }}" tabindex="0" @endif>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $a['titre'] }}">
                                        <div class="st-person">
                                            @if($a['photo'])<img class="st-thumb" src="{{ $a['photo'] }}" alt="" loading="lazy">@endif
                                            <div>@if($a['url'])<a href="{{ $ficheAnnonce($a['id']) }}">{{ \Illuminate\Support\Str::limit($a['titre'], 50) }}</a><small><a href="{{ $a['url'] }}" target="_blank" class="fw-normal">voir sur le site</a></small>@else{{ $a['titre'] }}@endif</div>
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
        <h4 class="st-block-title">Statut, neuf / occasion, livraison <small class="st-click-hint">— cliquez une part pour voir ces annonces</small></h4>
        <div class="st-grid-3">
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Statut</h5></div>
                <div class="admin-card-body"><div class="st-chart sm clickable"><canvas id="chartStatus"></canvas></div></div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Neuf / occasion</h5></div>
                <div class="admin-card-body"><div class="st-chart sm clickable"><canvas id="chartEtat"></canvas></div></div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Livraison</h5></div>
                <div class="admin-card-body"><div class="st-chart sm clickable"><canvas id="chartLivraison"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="st-block" id="st-annonces-prix">
        <h4 class="st-block-title">Prix</h4>
        <div class="st-grid-2">
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Tranches de prix (F CFA) <small class="st-click-hint">— cliquez une barre</small></h5></div>
                <div class="admin-card-body"><div class="st-chart sm clickable"><canvas id="chartPrix"></canvas></div></div>
            </div>
            <div class="st-kpis" style="align-content:start">
                <a class="st-kpi" href="{{ $detailUrl('annonces', ['tri' => 'prix', 'sens' => 'asc']) }}" style="--kpi:#eab308">
                    <div class="st-kpi-label"><span>Prix médian</span><i class="fas fa-coins"></i></div>
                    <div class="st-kpi-value">{{ $money($kpis['prix_median']) }}</div>
                    <div class="st-kpi-sub">la moitié des annonces sont moins chères</div>
                </a>
                <a class="st-kpi" href="{{ $detailUrl('annonces', ['tri' => 'prix']) }}" style="--kpi:#f59e0b">
                    <div class="st-kpi-label"><span>Prix moyen</span><i class="fas fa-calculator"></i></div>
                    <div class="st-kpi-value">{{ $money($kpis['prix_moyen']) }}</div>
                    <div class="st-kpi-sub">tiré vers le haut par les articles chers</div>
                </a>
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('mots-cles') }}">Mots-clés des titres</a> <small class="st-click-hint">— cliquez un mot pour voir ses annonces</small></h5>
                <span class="st-card-actions">
                    <a class="st-see-all" href="{{ $detailUrl('mots-cles') }}">Voir tout <i class="fas fa-arrow-right"></i></a>
                    <a class="st-export" href="{{ $exportUrl('mots') }}"><i class="fas fa-file-csv"></i> Mots</a>
                    <a class="st-export" href="{{ $exportUrl('expressions') }}"><i class="fas fa-file-csv"></i> Expressions</a>
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
                                    <a href="{{ $detailUrl('annonces', ['mot' => $m['mot']]) }}" style="font-size: {{ 0.8 + ($maxMot ? $m['annonces'] / $maxMot : 0) * 0.7 }}rem" title="Voir les {{ $m['annonces'] }} annonces">{{ $m['mot'] }}<small>{{ $m['annonces'] }}</small></a>
                                @endforeach
                            </div>
                            @if(! empty($stats['top_mots']['expressions']))
                                <h6 class="mt-4 mb-2 fw-bold"><a href="{{ $detailUrl('mots-cles', ['liste' => 'expressions']) }}" class="text-reset">Expressions fréquentes (2 mots)</a></h6>
                                <div class="st-cloud alt">
                                    @foreach($stats['top_mots']['expressions'] as $m)
                                        <a href="{{ $detailUrl('annonces', ['mot' => $m['mot']]) }}">{{ $m['mot'] }}<small>{{ $m['annonces'] }}</small></a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <table class="st-table" data-sortable>
                            <thead><tr><th data-sort="num">#</th><th data-sort="text">Mot</th><th data-sort="num" class="num">Annonces</th><th data-sort="num" class="num">% des titres</th></tr></thead>
                            <tbody>
                            @foreach($stats['top_mots']['mots'] as $i => $m)
                                <tr data-href="{{ $detailUrl('annonces', ['mot' => $m['mot']]) }}" tabindex="0">
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
            <a class="st-kpi" href="{{ $detailUrl('inscrits') }}" style="--kpi:#6366f1">
                <div class="st-kpi-label"><span>Nouveaux inscrits</span><i class="fas fa-user-plus"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['inscriptions']['valeur']) }}</div>
                <div class="st-kpi-sub">{!! $trend($kpis['inscriptions']) !!} vs {{ $fmt($kpis['inscriptions']['precedent']) }}</div>
            </a>
            <div class="st-kpi" style="--kpi:#4f46e5">
                <div class="st-kpi-label"><span>Inscrits au total</span><i class="fas fa-users"></i></div>
                <div class="st-kpi-value">{{ $fmt($kpis['utilisateurs_total']) }}</div>
                <div class="st-kpi-sub">{{ $pct($kpis['emails_verifies_pct']) }} d'emails vérifiés</div>
            </div>
            <a class="st-kpi" href="{{ $detailUrl('inscrits', ['vendeur' => 1]) }}" style="--kpi:#14b8a6">
                <div class="st-kpi-label"><span>Inscrits devenus vendeurs</span><i class="fas fa-percentage"></i></div>
                <div class="st-kpi-value">{{ $pct($kpis['conversion_vendeurs_pct']) }}</div>
                <div class="st-kpi-sub">des nouveaux inscrits ont publié</div>
            </a>
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('vendeurs') }}">Vendeurs qui publient le plus</a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('vendeurs') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('vendeurs') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                <tr @if($v['admin_url']) data-href="{{ $ficheVendeur($v['id']) }}" tabindex="0" @endif>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $v['nom'] }}">
                                        <div class="st-person">
                                            <img src="{{ $v['photo'] }}" alt="" loading="lazy">
                                            <div>
                                                @if($v['admin_url'])<a href="{{ $ficheVendeur($v['id']) }}">{{ $v['nom'] }}</a>@else{{ $v['nom'] }}@endif
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('boutiques') }}">Boutiques (profils vendeurs) les plus visitées</a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('boutiques') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('boutiques') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                <tr @if($b['admin_url']) data-href="{{ $ficheVendeur($b['id']) }}" tabindex="0" @endif>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $b['nom'] }}">
                                        <div class="st-person">
                                            <img src="{{ $b['photo'] }}" alt="" loading="lazy">
                                            <div>
                                                @if($b['admin_url'])<a href="{{ $ficheVendeur($b['id']) }}">{{ $b['nom'] }}</a>@else{{ $b['nom'] }}@endif
                                                @if($b['certifie'])<i class="fas fa-circle-check badge-cert" title="Certifié"></i>@endif
                                                <small>@if($b['boutique_url'])<a href="{{ $b['boutique_url'] }}" target="_blank" class="fw-normal">voir la boutique</a>@endif</small>
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
                                                        <a href="{{ $ficheAnnonce($b['meilleure_annonce']['id']) }}">{{ \Illuminate\Support\Str::limit($b['meilleure_annonce']['titre'], 40) }}</a>
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
            ['top_categories', 'categories', 'Catégories les plus publiées', false, 'categories', 'c'],
            ['top_sous_categories', 'sous_categories', 'Sous-catégories les plus publiées', true, 'sous-categories', 's'],
        ] as [$key, $section, $title, $withParent, $anchor, $prefix])
            <div class="st-block" id="st-categories-{{ $anchor }}">
                <div class="admin-card mb-0">
                    <div class="admin-card-header st-card-head">
                        <h5 class="admin-card-title"><a href="{{ $detailUrl($anchor) }}">{{ $title }}</a></h5>
                        <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl($anchor) }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl($section) }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                        <tr data-href="{{ route('admin.statistics.index', ['categorie' => $prefix . $c['id']] + $params) }}" tabindex="0" title="Filtrer toute la page sur {{ $c['nom'] }}">
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
                <h5 class="admin-card-title"><a href="{{ $detailUrl('villes') }}">Villes où l'on publie le plus <small class="st-click-hint">— cliquez une ville pour filtrer la page</small></a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('villes') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('villes') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
            </div>
            <div class="admin-card-body">
                @if(empty($stats['top_villes']))
                    <div class="st-empty"><i class="fas fa-map"></i>Aucune donnée sur cette période.</div>
                @else
                    <div class="st-grid-2">
                        <div class="st-chart clickable"><canvas id="chartVilles"></canvas></div>
                        <table class="st-table" data-sortable>
                            <thead><tr><th data-sort="num">#</th><th data-sort="text">Ville</th><th data-sort="num" class="num">Annonces</th><th data-sort="num" class="num">Part</th></tr></thead>
                            <tbody>
                            @foreach($stats['top_villes'] as $i => $v)
                                <tr @if(! $filters->ville) data-href="{{ route('admin.statistics.index', ['ville' => $v['nom']] + $params) }}" tabindex="0" title="Filtrer toute la page sur {{ $v['nom'] }}" @endif>
                                    <td data-value="{{ $i + 1 }}"><span class="st-rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                    <td data-value="{{ $v['nom'] }}">
                                        <strong>{{ $v['nom'] }}</strong>
                                        @if(! $filters->ville)
                                            <i class="fas fa-filter ms-1 small text-muted"></i>
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
        <a class="st-kpi" href="{{ $detailUrl('recherches') }}" style="--kpi:#0ea5e9">
            <div class="st-kpi-label"><span>Recherches</span><i class="fas fa-search"></i></div>
            <div class="st-kpi-value">{{ $fmt($r['total']) }}</div>
            <div class="st-kpi-sub">par {{ $fmt($r['chercheurs']) }} visiteurs</div>
        </a>
        <a class="st-kpi" href="{{ $detailUrl('recherches', ['sans_resultat' => 1]) }}" style="--kpi:#ef4444">
            <div class="st-kpi-label"><span>Sans résultat</span><i class="fas fa-circle-exclamation"></i></div>
            <div class="st-kpi-value">{{ $pct($r['sans_resultat_pct']) }}</div>
            <div class="st-kpi-sub">{{ $fmt($r['sans_resultat']) }} recherches n'ont rien trouvé</div>
        </a>
    </div>

    <div class="st-grid-2">
        <div class="st-block" id="st-recherches-frequentes">
            <div class="admin-card mb-0">
                <div class="admin-card-header st-card-head">
                    <h5 class="admin-card-title"><a href="{{ $detailUrl('recherches') }}">Les plus fréquentes</a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('recherches') }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('recherches') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                    <tr data-href="{{ route('admin.statistics.details', ['type' => 'annonces', 'recherche' => $t['terme'], 'periode' => 'tout']) }}" tabindex="0" title="Voir les annonces qui correspondent">
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
                    <h5 class="admin-card-title"><a href="{{ $detailUrl('recherches', ['sans_resultat' => 1]) }}">Sans résultat <small class="text-muted fw-normal">— produits demandés mais absents</small></a></h5>
                <span class="st-card-actions"><a class="st-see-all" href="{{ $detailUrl('recherches', ['sans_resultat' => 1]) }}">Voir tout <i class="fas fa-arrow-right"></i></a><a class="st-export" href="{{ $exportUrl('recherches_vides') }}"><i class="fas fa-file-csv"></i> Exporter</a></span>
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
                                    <tr data-href="{{ $detailUrl('recherches', ['sans_resultat' => 1, 'q' => $t['terme']]) }}" tabindex="0">
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
            <div class="admin-card-header"><h5 class="admin-card-title">D'où viennent les recherches <small class="st-click-hint">— cliquez une barre</small></h5></div>
            <div class="admin-card-body"><div class="st-chart sm clickable" style="height:180px"><canvas id="chartSources"></canvas></div></div>
        </div>
    </div>
</section>

@endsection

@include('admin.statistics.partials._filters_script')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/2.0.1/chartjs-plugin-zoom.min.js"></script>
<script>
(function () {
    const stats = @json($chartData);
    const jours = @json($jours);
    const nf = new Intl.NumberFormat('fr-FR');
    const form = document.getElementById('statsFilters');
    const finePointer = window.matchMedia('(pointer: fine)').matches;
    const frDate = iso => { const [y, m, d] = iso.split('-'); return `${d}/${m}/${y}`; };

    const { goTo, topbar } = window.StatsUI;
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

    // Clic sur une part / une barre : ouvre la liste correspondante
    const clickTo = map => ({
        onClick(evt, elements, chart) {
            if (!elements.length) return;
            const label = chart.data.labels[elements[0].index];
            if (map && map[label]) window.location.href = map[label];
        },
        onHover(evt, elements) { evt.native.target.style.cursor = elements.length ? 'pointer' : 'default'; },
    });

    const doughnut = (id, data, colors, map) => {
        const labels = Object.keys(data), values = Object.values(data);
        if (!values.some(v => v > 0)) return emptyChart(id, 'Aucune donnée');
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: { ...clickTo(map), responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    return ` ${ctx.label} : ${nf.format(ctx.parsed)} (${total ? Math.round(ctx.parsed / total * 100) : 0} %)`;
                } } },
            } },
        });
    };

    const bar = (id, labels, values, color, horizontal = false, map = null) => new Chart(document.getElementById(id), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Annonces', data: values, backgroundColor: color, borderRadius: 4 }] },
        options: { ...clickTo(map), responsive: true, maintainAspectRatio: false, indexAxis: horizontal ? 'y' : 'x', plugins: { legend: { display: false },
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
            doughnut('chartStatus', r.statut, ['#10b981', '#f59e0b', '#ef4444'], stats.links.statut);
            doughnut('chartEtat', r.etat, ['#6366f1', '#a5b4fc'], stats.links.etat);
            doughnut('chartLivraison', r.livraison, ['#0ea5e9', '#cbd5e1'], stats.links.livraison);
            bar('chartPrix', Object.keys(r.tranches_prix), Object.values(r.tranches_prix), '#eab308', false, stats.links.prix);
        },
        villes() {
            if (document.getElementById('chartVilles')) {
                const villes = stats.villes.slice(0, 10);
                bar('chartVilles', villes.map(v => v.nom), villes.map(v => v.annonces), '#f97316', true, stats.links.villes);
            }
        },
        recherches() {
            if (!Object.keys(r.sources_recherche).length) return emptyChart('chartSources', 'Pas encore de recherches enregistrées.');
            new Chart(document.getElementById('chartSources'), {
                type: 'bar',
                data: { labels: Object.keys(r.sources_recherche), datasets: [{ label: 'Recherches', data: Object.values(r.sources_recherche), backgroundColor: ['#0ea5e9', '#6366f1', '#14b8a6'], borderRadius: 4 }] },
                options: { ...clickTo(stats.links.sources), responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false },
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
