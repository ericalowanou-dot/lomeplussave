{{-- Scripts partagés : barre collée, filtres, calendrier, tri local, lignes cliquables. --}}
@php
    $filtersJs = [
        'du' => $filters->from->toDateString(),
        'au' => $filters->to->toDateString(),
        'presets' => array_diff_key($periods, ['perso' => true]),
        'baseUrl' => url()->current(),
    ];
@endphp
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/fr.min.js"></script>
<script>
(function () {
    const F = @json($filtersJs);
    const form = document.getElementById('statsFilters');

    // ================= Barre collée sous l'en-tête de l'admin =================
    const topbar = document.querySelector('.top-navbar');
    const syncNavTop = () => document.documentElement.style.setProperty('--st-nav-top', (topbar ? topbar.offsetHeight : 0) + 'px');
    syncNavTop();
    window.addEventListener('resize', syncNavTop);

    // ================= Navigation vers une autre période / un autre filtre =================
    // Construit l'URL de la page en gardant les filtres, le tri et l'onglet ouvert.
    function goTo(changes, baseUrl) {
        const params = new URLSearchParams(window.location.search);
        ['refresh', 'page', 'fans_page', 'annonces_page'].forEach(k => params.delete(k));
        Object.entries(changes).forEach(([k, v]) => (v === null || v === '') ? params.delete(k) : params.set(k, v));
        window.location.href = (baseUrl || F.baseUrl) + (params.toString() ? '?' + params : '') + window.location.hash;
    }
    window.StatsUI = { goTo, topbar };

    if (form) {
        form.addEventListener('submit', function () {
            // Période prédéfinie : inutile d'envoyer les dates
            if (document.getElementById('periode').value !== 'perso') {
                document.getElementById('du').disabled = true;
                document.getElementById('au').disabled = true;
            }
            // Champs vides (« Toutes les villes »…) : URL plus propre
            form.querySelectorAll('select').forEach(el => { if (el.value === '') el.disabled = true; });
            form.action = F.baseUrl + window.location.hash;
        });
        form.querySelectorAll('.st-autosubmit').forEach(select => select.addEventListener('change', () => {
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }));
    }
    document.querySelectorAll('.st-keep-hash').forEach(a => a.addEventListener('click', () => {
        a.href = a.href.split('#')[0] + window.location.hash;
    }));

    // ================= Calendrier =================
    const dateBtn = document.getElementById('dateRangeBtn');
    if (dateBtn && window.flatpickr) {
        if (flatpickr.l10ns && flatpickr.l10ns.fr) {
            flatpickr.localize(flatpickr.l10ns.fr);
        }
        const fp = flatpickr(document.getElementById('dateRangeInput'), {
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: [F.du, F.au],
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
                Object.entries(F.presets).forEach(([key, label]) => {
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
                instance.jumpToDate(F.au);
                if (instance.config.showMonths > 1 && F.du.slice(0, 7) !== F.au.slice(0, 7)) {
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
    } else if (dateBtn) {
        // Repli : deux champs de date natifs
        document.getElementById('dateFallback').hidden = false;
        dateBtn.disabled = true;
        ['du', 'au'].forEach(id => document.getElementById(id).addEventListener('change', () => {
            document.getElementById('periode').value = 'perso';
        }));
    }

    // ================= Tri local des tableaux (page Statistiques) =================
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

    // ================= Lignes cliquables =================
    // Toute la ligne ouvre son détail ; les liens et boutons à l'intérieur gardent leur propre action.
    document.addEventListener('click', e => {
        const row = e.target.closest('tr[data-href]');
        if (!row || e.target.closest('a, button, input, select, label')) return;
        if (e.ctrlKey || e.metaKey || e.button === 1) {
            window.open(row.dataset.href, '_blank');
        } else {
            window.location.href = row.dataset.href;
        }
    });
    document.addEventListener('auxclick', e => {
        const row = e.target.closest('tr[data-href]');
        if (row && e.button === 1 && !e.target.closest('a')) window.open(row.dataset.href, '_blank');
    });
    document.addEventListener('keydown', e => {
        const row = e.target.closest && e.target.closest('tr[data-href]');
        if (row && e.key === 'Enter') window.location.href = row.dataset.href;
    });
})();
</script>
@endpush
