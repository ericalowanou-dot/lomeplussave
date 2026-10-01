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
    .st-cloud a { text-decoration: none; transition: filter .12s; }
    .st-cloud a:hover { filter: brightness(.94); }
    .st-cloud span, .st-cloud a { background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 3px 12px; font-weight: 600; line-height: 1.5; }
    .st-cloud span small, .st-cloud a small { opacity: .6; font-weight: 500; margin-left: 4px; }
    .st-cloud.alt span, .st-cloud.alt a { background: #fff7ed; color: #9a3412; }

    .st-heat { width: 100%; border-collapse: separate; border-spacing: 2px; font-size: .7rem; }
    .st-heat th { color: var(--text-secondary); font-weight: 600; text-align: center; padding: 2px; }
    .st-heat td { height: 24px; border-radius: 3px; text-align: center; color: transparent; cursor: default; min-width: 18px; }
    .st-heat td:hover { outline: 2px solid var(--dark-color); color: var(--dark-color); }
    .st-heat-legend { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: var(--text-secondary); margin-top: 8px; }
    .st-heat-legend i { display: inline-block; width: 60px; height: 8px; border-radius: 4px; background: linear-gradient(90deg, #eef2ff, #4338ca); }

    /* ---- Éléments cliquables ---- */
    tr[data-href] { cursor: pointer; transition: background .12s; }
    tr[data-href]:hover td { background: #f5f7ff; }
    tr[data-href]:focus-visible td { background: #eef2ff; outline: none; }
    .st-see-all { font-size: .8rem; font-weight: 600; color: var(--primary-color); text-decoration: none; white-space: nowrap; }
    .st-see-all:hover { text-decoration: underline; }
    .st-card-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    a.admin-card-title, .admin-card-title a { color: inherit; text-decoration: none; }
    .admin-card-title a:hover { color: var(--primary-color); }
    .st-chart.clickable canvas { cursor: pointer; }
    .st-click-hint { font-size: .75rem; color: var(--text-secondary); font-weight: 400; }

    /* ---- Champions ---- */
    .st-champions { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 14px; margin-bottom: 22px; }
    .st-champion { display: flex; align-items: center; gap: 12px; background: #fff; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.08); text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s; min-width: 0; }
    a.st-champion:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }
    .st-champion-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; background: var(--chip-bg, #eef2ff); color: var(--chip, #4338ca); overflow: hidden; }
    .st-champion-icon img { width: 100%; height: 100%; object-fit: cover; }
    .st-champion-body { min-width: 0; flex: 1; }
    .st-champion-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 700; color: var(--text-secondary); }
    .st-champion-name { font-weight: 700; color: var(--dark-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .st-champion-value { font-size: .8rem; color: var(--text-secondary); }
    .st-champion.empty { opacity: .65; }

    /* ---- Pages de détail et fiches ---- */
    .st-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: .85rem; margin-bottom: 12px; color: var(--text-secondary); }
    .st-crumb a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
    .st-crumb a:hover { text-decoration: underline; }
    .st-detail-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .st-detail-head h3 { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .st-detail-head p { margin: 4px 0 0; color: var(--text-secondary); font-size: .88rem; max-width: 760px; }
    .st-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .st-toolbar form { display: flex; gap: 8px; flex-wrap: nowrap; align-items: center; }
    .st-toolbar .form-control { min-width: 240px; }
    .st-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .st-chip { display: inline-flex; align-items: center; gap: 6px; background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 4px 10px; font-size: .8rem; font-weight: 600; text-decoration: none; }
    .st-chip:hover { background: #e0e7ff; color: #312e81; }
    .st-chip.active { background: var(--primary-color); color: #fff; }
    .st-total { font-size: .85rem; color: var(--text-secondary); }
    .st-table th a { color: inherit; text-decoration: none; white-space: nowrap; }
    .st-table th a:hover { color: var(--primary-color); }
    .st-table th a i { font-size: .7rem; margin-left: 3px; opacity: .35; }
    .st-table th a.active { color: var(--primary-color); }
    .st-table th a.active i { opacity: 1; }
    .st-badge { display: inline-block; border-radius: 999px; padding: 2px 9px; font-size: .75rem; font-weight: 600; white-space: nowrap; }
    .st-badge.approved, .st-badge.yes { background: #d1fae5; color: #047857; }
    .st-badge.pending { background: #fef3c7; color: #92400e; }
    .st-badge.blocked { background: #fee2e2; color: #b91c1c; }
    .st-badge.no { background: #f3f4f6; color: #6b7280; }
    .st-pagination { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-top: 14px; }
    .st-pagination .pagination { margin: 0; flex-wrap: wrap; }
    .st-fiche-head { display: flex; gap: 18px; align-items: flex-start; flex-wrap: wrap; }
    .st-fiche-photo { width: 110px; height: 110px; border-radius: 14px; object-fit: cover; background: #f3f4f6; flex-shrink: 0; }
    .st-fiche-photo.round { border-radius: 50%; }
    .st-fiche-info { flex: 1; min-width: 240px; }
    .st-fiche-info h3 { font-size: 1.3rem; font-weight: 700; margin: 0 0 6px; }
    .st-fiche-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; font-size: .86rem; color: var(--text-secondary); margin-bottom: 10px; }
    .st-fiche-meta strong { color: var(--dark-color); font-weight: 600; }
    .st-fiche-links { display: flex; flex-wrap: wrap; gap: 8px; }
    .st-compare { background: #f8fafc; border-radius: 10px; padding: 12px 14px; font-size: .88rem; }

    .st-notice { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: 10px; padding: 12px 16px; font-size: .88rem; display: flex; gap: 10px; align-items: flex-start; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .st-filters > div { flex: 1 1 140px; }
        .st-filters .st-date { flex-basis: 100%; min-width: 0; }
        .st-filters .form-select { min-width: 0; width: 100%; }
        .st-kpi-value { font-size: 1.45rem; }
        .st-chart { height: 260px; }
        .st-selection .st-sel-actions { margin-left: 0; width: 100%; }
        .st-selection .st-sel-actions .btn { flex: 1; }
        .st-toolbar .form-control { min-width: 0; flex: 1; }
        .st-toolbar form { width: 100%; }
        .st-fiche-photo { width: 76px; height: 76px; }
    }
</style>
@endpush
