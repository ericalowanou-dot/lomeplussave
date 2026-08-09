{{-- Mes annonces : empty state dédié + cartes unifiées (statut / actions) --}}
@if($articles->isEmpty())
    <div class="mes-annonces-empty-filtered" style="background: linear-gradient(135deg, rgba(81, 95, 251, 0.08), rgba(81, 95, 251, 0)); border: 1px solid rgba(81, 95, 251, 0.15); border-radius: 18px; padding: 64px 32px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 18px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); margin: 32px 0;">
        <div style="width: 72px; height: 72px; border-radius: 18px; display: grid; place-items: center; background: rgba(81, 95, 251, 0.12); color: #515ffb; font-size: 32px;">
            <i class="bi bi-funnel-x"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 700; color: #111827; margin: 0;">Aucune annonce trouvée</h2>
        <p style="color: #475569; font-size: 1rem; max-width: 420px; margin: 0 auto; line-height: 1.6;">
            @if(request('status') || request('boosted') || request('categorie') || request('date_filter'))
                Aucune annonce ne correspond aux filtres sélectionnés.
                Essayez de modifier vos critères de recherche ou consultez toutes vos annonces.
            @else
                Vous n'avez pas encore d'annonces correspondant à ce critère.
            @endif
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
            <a href="{{ route('mes_annonces') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 999px; background: linear-gradient(135deg, #515ffb, #3b4fd8); color: #fff; font-weight: 600; text-decoration: none; box-shadow: 0 12px 30px rgba(81, 95, 251, 0.25); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <i class="bi bi-arrow-left"></i>
                <span>Voir toutes mes annonces</span>
            </a>
            @if(request('status') || request('boosted') || request('categorie') || request('date_filter'))
                <a href="{{ route('articles.create') }}" style="display: inline-flex; align-items: center; padding: 10px 16px; border-radius: 999px; border: 1px solid rgba(15, 23, 42, 0.12); color: #1f2937; font-weight: 500; text-decoration: none; transition: background 0.2s ease, border-color 0.2s ease;">
                    <i class="bi bi-plus-circle"></i>
                    <span>Créer une annonce</span>
                </a>
            @endif
        </div>
    </div>
@else
    @include('partials.articles-list', [
        'articles' => $articles,
        'showOwnerControls' => true,
        'showPromoFeed' => false,
    ])
@endif
