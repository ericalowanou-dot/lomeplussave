@php
    $villesList = $villesList ?? [
        'Lomé', 'Sokodé', 'Kara', 'Kpalimé', 'Atakpamé', 'Dapaong', 'Tsévié', 'Aného', 'Mango', 'Notsé',
        'Bafilo', 'Bassar', 'Blitta', 'Tchamba', 'Vogan', 'Badou', 'Afagnan', 'Tabligbo', 'Amlamé',
        'Sotouboua', 'Kétao', 'Niamtougou', 'Kanté', 'Tchitchao', 'Kabou', 'Hahotoé', 'Kouvé',
        'Agbodrafo', 'Wahala', 'Kpélé', 'Amou-Oblo', 'Sanguéra', 'Djarkpanga', 'Tandjouaré', 'Biankouri',
        'Nyékonakpoé', 'Avedji', 'Totsi', 'Adidogomé', 'Kégué', 'Ségbé', 'Klikamé', 'Agou-Gadzépé',
        'Gando', 'Elavagnon', 'Alédjo', 'Kamina', 'Kambolé',
    ];
@endphp

<div class="homepage-sidebar-card">
    <h5><i class="bi bi-sliders me-2"></i>Filtres</h5>

    <form id="filterFormSidebar" class="homepage-sidebar-form" action="{{ route('articles.index') }}" method="GET">
        <div class="filter-field">
            <label for="categorie_sidebar" class="form-label">Catégorie</label>
            <select name="categorie" id="categorie_sidebar" class="form-control" onchange="loadSousCategoriesSidebar(this.value)">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $categorie)
                    <option value="{{ $categorie->id }}" data-sous-categories='@json($categorie->sousCategories)' {{ request('categorie') == $categorie->id ? 'selected' : '' }}>{{ $categorie->nom }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label for="sous_categorie_sidebar" class="form-label">Sous-catégorie</label>
            <select name="sous_categorie" id="sous_categorie_sidebar" class="form-control">
                <option value="">Toutes les sous-catégories</option>
            </select>
        </div>

        <div class="filter-field">
            <label for="prix_min_sidebar" class="form-label">Prix minimum</label>
            <div class="input-with-prefix">
                <span>CFA</span>
                <input type="number" name="prix_min" id="prix_min_sidebar" class="form-control" placeholder="0" value="{{ request('prix_min') }}" min="0">
            </div>
        </div>

        <div class="filter-field">
            <label for="prix_max_sidebar" class="form-label">Prix maximum</label>
            <div class="input-with-prefix">
                <span>CFA</span>
                <input type="number" name="prix_max" id="prix_max_sidebar" class="form-control" placeholder="100 000" value="{{ request('prix_max') }}" min="0">
            </div>
        </div>

        <div class="filter-field">
            <label for="ville_sidebar" class="form-label">Ville</label>
            <select name="ville" id="ville_sidebar" class="form-control">
                <option value="">Toutes les villes</option>
                @foreach($villesList as $ville)
                    <option value="{{ $ville }}" {{ request('ville') == $ville ? 'selected' : '' }}>{{ $ville }}</option>
                @endforeach
            </select>
        </div>

        <label class="filter-toggle">
            <input type="checkbox" name="pro_only" value="1" {{ request('pro_only') ? 'checked' : '' }}>
            <span>Annonces Pro uniquement</span>
        </label>

        <label class="filter-toggle">
            <input type="checkbox" name="livraison_only" value="1" {{ request('livraison_only') ? 'checked' : '' }}>
            <span>Livraison disponible</span>
        </label>

        <div class="homepage-sidebar-actions">
            <button type="submit" class="btn-apply">
                <i class="bi bi-check2"></i>
                Appliquer
            </button>
            <button type="button" class="btn-reset" id="resetFiltersSidebar">
                <i class="bi bi-arrow-counterclockwise"></i>
                Réinitialiser
            </button>
        </div>
    </form>

    <div class="homepage-sidebar-ads">
        {{-- Format vertical / compact (desktop) — position admin "Sidebar" --}}
        @include('partials.publicites', ['position' => 'sidebar'])
        <div class="homepage-sidebar-ads-placeholder d-none">
            Espace publicitaire
        </div>
    </div>
</div>

<script>
    function loadSousCategoriesSidebar(categorieId) {
        const sousCategorieSelect = document.getElementById('sous_categorie_sidebar');
        const categorieSelect = document.getElementById('categorie_sidebar');
        const selectedSousCategorie = '{{ request('sous_categorie') }}';

        if (!sousCategorieSelect || !categorieSelect) return;

        sousCategorieSelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';

        if (!categorieId) return;

        const selectedOption = categorieSelect.querySelector(`option[value="${categorieId}"]`);
        if (selectedOption) {
            try {
                const sousCategories = JSON.parse(selectedOption.getAttribute('data-sous-categories') || '[]');
                sousCategories.forEach(function(sc) {
                    const option = document.createElement('option');
                    option.value = sc.id;
                    option.textContent = sc.nom;
                    if (selectedSousCategorie == sc.id) option.selected = true;
                    sousCategorieSelect.appendChild(option);
                });
            } catch (e) {
                console.error('Erreur sous-catégories:', e);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const categorieSelect = document.getElementById('categorie_sidebar');
        if (categorieSelect && categorieSelect.value) {
            loadSousCategoriesSidebar(categorieSelect.value);
        }

        document.getElementById('resetFiltersSidebar')?.addEventListener('click', function () {
            window.location.href = "{{ route('articles.index') }}";
        });
    });
</script>
