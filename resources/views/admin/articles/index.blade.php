@extends('admin.layout')

@section('title', 'Modération des articles')
@section('page-title', 'Modération des articles')

@push('styles')
<link href="{{ asset('css/admin-articles-queue.css') }}" rel="stylesheet">
@endpush

@php
    use App\Http\Controllers\AdminController;

    $tabs = [
        'pending' => 'En attente',
        'to_modify' => 'À modifier',
        'approved' => 'Approuvées',
        'blocked' => 'Bloquées',
    ];

    $formatPrice = fn ($article) => number_format((float) $article->prix_ht, 0, ',', ' ') . ' CFA';
    $categoryLabel = function ($article) {
        $sous = $article->sousCategorie;
        if (! $sous) {
            return '—';
        }
        $parent = $sous->categorie->nom ?? null;

        return $parent ? $parent . ' › ' . $sous->nom : $sous->nom;
    };
@endphp

@section('content')
<div class="aq-page-head">
    <h1>Modération des annonces</h1>
    <p>Chaque annonce est vérifiée avant d’apparaître sur la plateforme.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="aq-filters-bar">
    <div class="aq-filters">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.articles.index', array_filter(['tab' => $key, 'search' => request('search')])) }}"
               class="aq-chip {{ $tab === $key ? 'is-active' : '' }}">
                {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.articles.index') }}" class="aq-search d-flex gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Rechercher…">
        <button type="submit" class="btn btn-sm btn-outline-secondary">OK</button>
    </form>
</div>

@if($tab === 'pending')
    @if($current)
        <form id="approve-many" method="POST" action="{{ route('admin.articles.bulk-approve') }}">@csrf</form>

        <div class="aq-review">
            <div class="aq-review-card">
                <div class="aq-review-photo">
                    <img src="{{ $current->photo_url }}"
                         alt="{{ $current->titre }}"
                         onerror="this.src='{{ asset('images/placeholder.png') }}';">
                    <span class="aq-tag">{{ $current->photosCount() }} photo(s)</span>
                </div>

                <div class="aq-review-body">
                    <div class="aq-cell-stack" style="gap:4px">
                        <span class="aq-sub">
                            Soumise {{ $current->updated_at->diffForHumans() }}
                            @if($current->lieu) · {{ $current->lieu }}@endif
                        </span>
                        <span class="aq-title">{{ $current->titre }}</span>
                        <span class="aq-price aq-review-price">{{ $formatPrice($current) }}</span>
                    </div>

                    <div class="aq-facts">
                        <div class="aq-fact">
                            <span>Catégorie</span>
                            <strong>{{ $categoryLabel($current) }}</strong>
                        </div>
                        <div class="aq-fact">
                            <span>Déjà publiée</span>
                            <strong>{{ $current->approved_at ? 'Oui, modification' : 'Non, nouvelle' }}</strong>
                        </div>
                    </div>

                    <div class="aq-description">{{ $current->description }}</div>

                    <div class="aq-seller">
                        <span class="aq-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($current->user->name ?? '?', 0, 1)) }}</span>
                        <div class="aq-cell-stack">
                            <span class="aq-strong">{{ $current->user->name ?? 'Utilisateur' }}</span>
                            <span class="aq-sub">
                                Membre depuis {{ optional($current->user?->created_at)->translatedFormat('F Y') ?? '—' }}
                            </span>
                        </div>
                        <a href="{{ route('admin.articles.show', $current) }}" class="btn btn-sm btn-outline-secondary ms-auto">Détail</a>
                    </div>

                    <div class="aq-decisions">
                        <form method="POST" action="{{ route('admin.articles.approve', $current) }}">
                            @csrf
                            <button type="submit" class="aq-btn aq-btn-success">Approuver</button>
                        </form>
                        <button type="button"
                                class="aq-btn aq-btn-soft"
                                data-bs-toggle="modal"
                                data-bs-target="#modifyModal"
                                data-action="{{ route('admin.articles.request-modification', $current) }}"
                                data-title="{{ $current->titre }}">
                            Modifier
                        </button>
                        <button type="button"
                                class="aq-btn aq-btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#blockModal"
                                data-action="{{ route('admin.articles.block', $current) }}"
                                data-title="{{ $current->titre }}">
                            Bloquer
                        </button>
                    </div>

                    @if($next)
                        <a class="aq-skip" href="{{ route('admin.articles.index', ['tab' => 'pending', 'article' => $next->id, 'page' => $articles->currentPage(), 'search' => request('search')]) }}">
                            Passer, décider plus tard
                        </a>
                    @endif
                </div>
            </div>

            <div class="aq-queue">
                <span class="aq-strong" style="font-size:13px">En attente ({{ $articles->total() }})</span>
                <span class="aq-sub">Cochez pour approuver en une fois.</span>

                @foreach($articles as $article)
                    <div class="aq-queue-item {{ $article->is($current) ? 'is-active' : '' }}">
                        <input type="checkbox"
                               name="article_ids[]"
                               value="{{ $article->id }}"
                               form="approve-many"
                               class="queue-check"
                               aria-label="Sélectionner « {{ $article->titre }} »">
                        <a href="{{ route('admin.articles.index', ['tab' => 'pending', 'article' => $article->id, 'page' => $articles->currentPage(), 'search' => request('search')]) }}"
                           class="aq-queue-item"
                           style="flex:1;min-width:0;margin:0;padding:0">
                            <img class="aq-thumb"
                                 src="{{ $article->photo_url }}"
                                 alt=""
                                 onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            <span class="aq-cell-stack">
                                <span class="aq-strong aq-truncate" style="font-size:12.5px">{{ $article->titre }}</span>
                                <span class="aq-price" style="font-size:12px">{{ $formatPrice($article) }}</span>
                            </span>
                        </a>
                    </div>
                @endforeach

                <button type="submit"
                        form="approve-many"
                        id="approveSelectedBtn"
                        class="aq-btn aq-btn-success"
                        style="display:none">
                    Approuver la sélection (<span id="selectedCount">0</span>)
                </button>

                <div class="mt-2">
                    {{ $articles->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="aq-empty">
            <strong>Toutes les annonces ont été traitées</strong><br>
            Les nouvelles annonces apparaîtront ici automatiquement.
        </div>
    @endif
@else
    <div class="aq-table">
        <div class="aq-row aq-row-head">
            <span></span>
            <span>Annonce</span>
            <span>Prix</span>
            <span>Statut</span>
            <span></span>
        </div>

        @forelse($articles as $article)
            <div class="aq-row">
                <img class="aq-thumb"
                     src="{{ $article->photo_url }}"
                     alt=""
                     style="width:58px;height:58px;border-radius:10px"
                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                <div class="aq-cell-stack">
                    <span class="aq-strong aq-truncate">{{ $article->titre }}</span>
                    <span class="aq-sub aq-truncate">
                        {{ $article->user->name ?? '—' }} · {{ $categoryLabel($article) }} · {{ $article->updated_at->diffForHumans() }}
                    </span>
                    @if(in_array($tab, ['to_modify', 'blocked'], true) && $article->block_reason)
                        <span class="aq-reason aq-truncate">{{ Str::limit($article->block_reason, 80) }}</span>
                    @endif
                </div>
                <span class="aq-price">{{ $formatPrice($article) }}</span>
                <span class="aq-badge aq-badge-{{ $tab }}">{{ $tabs[$tab] }}</span>
                <div class="d-flex gap-1 justify-content-end">
                    <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                    @if($tab !== 'blocked')
                        <button type="button"
                                class="aq-btn aq-btn-danger aq-btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#blockModal"
                                data-action="{{ route('admin.articles.block', $article) }}"
                                data-title="{{ $article->titre }}">
                            Bloquer
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="aq-empty" style="border:0;border-radius:0">Rien dans cette liste.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $articles->links() }}
    </div>
@endif

{{-- Modal demande de modification --}}
<div class="modal fade" id="modifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="modifyForm">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Demander une modification</h5>
                    <small class="text-muted" id="modifyTitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold mb-2">Ce qu’il faut corriger</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach(AdminController::ARTICLE_FIXABLE as $item)
                        <label class="btn btn-outline-secondary btn-sm">
                            <input type="checkbox" name="items[]" value="{{ $item }}" class="form-check-input me-1"> {{ $item }}
                        </label>
                    @endforeach
                </div>
                <label class="form-label">Modification à faire (obligatoire)</label>
                <textarea name="notes" class="form-control" rows="4" minlength="5" maxlength="2000" required
                          placeholder="Ex. Ajoutez 2 photos nettes de face et indiquez l’état exact du produit."></textarea>
                <p class="text-muted small mt-2 mb-0">
                    L’annonce passe en « À modifier ». Le vendeur reçoit ces consignes ; après correction elle revient en attente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal blocage --}}
<div class="modal fade" id="blockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="blockForm">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title text-danger">Bloquer l’annonce</h5>
                    <small class="text-muted" id="blockTitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold mb-2">Raison du blocage</p>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach(AdminController::ARTICLE_BLOCK_REASONS as $reason)
                        <label class="form-check">
                            <input type="radio" class="form-check-input" name="reason" value="{{ $reason }}" required>
                            <span class="form-check-label">{{ $reason }}</span>
                        </label>
                    @endforeach
                </div>
                <label class="form-label">Message au vendeur (facultatif, obligatoire si « Autre raison »)</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="500"
                          placeholder="Expliquez ce que le vendeur doit savoir…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger">Confirmer le blocage</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const checks = document.querySelectorAll('.queue-check');
    const btn = document.getElementById('approveSelectedBtn');
    const countEl = document.getElementById('selectedCount');

    function refreshSelection() {
        if (!btn || !countEl) return;
        const n = document.querySelectorAll('.queue-check:checked').length;
        countEl.textContent = String(n);
        btn.style.display = n > 0 ? 'block' : 'none';
    }

    checks.forEach((el) => el.addEventListener('change', refreshSelection));
    refreshSelection();

    const modifyModal = document.getElementById('modifyModal');
    if (modifyModal) {
        modifyModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = document.getElementById('modifyForm');
            form.action = button.getAttribute('data-action');
            document.getElementById('modifyTitle').textContent = button.getAttribute('data-title') || '';
        });
    }

    const blockModal = document.getElementById('blockModal');
    if (blockModal) {
        blockModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = document.getElementById('blockForm');
            form.action = button.getAttribute('data-action');
            document.getElementById('blockTitle').textContent = button.getAttribute('data-title') || '';
        });

        blockModal.addEventListener('submit', function (event) {
            const form = event.target;
            const reason = form.querySelector('input[name="reason"]:checked');
            const notes = form.querySelector('textarea[name="notes"]');
            if (reason && reason.value === 'Autre raison' && notes && notes.value.trim().length < 5) {
                event.preventDefault();
                notes.focus();
                alert('Précisez la raison (au moins 5 caractères).');
            }
        });
    }
})();
</script>
@endpush
