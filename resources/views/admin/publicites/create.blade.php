@extends('admin.layout')

@section('title', 'Ajouter une publicité')
@section('page-title', 'Ajouter une publicité')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">
            <i class="fas fa-plus-circle"></i>
            Nouvelle publicité
        </h5>
    </div>
    <div class="admin-card-body">
        <form method="POST" action="{{ route('admin.publicites.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre (optionnel)</label>
                        <input type="text" class="form-control @error('titre') is-invalid @enderror" 
                               id="titre" name="titre" value="{{ old('titre') }}" 
                               placeholder="Ex: Promotion spéciale">
                        @error('titre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Titre pour identifier la publicité (usage interne)</small>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Image *</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               id="image" name="image" accept="image/*" required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="card bg-light p-3 mt-2">
                            <small class="form-text d-block mb-2"><strong>📋 Formats acceptés:</strong> JPG, PNG, GIF, WEBP (max 5MB)</small>
                            <small class="form-text d-block mb-1"><strong>💡 Recommandations pour un meilleur affichage:</strong></small>
                            <ul class="small mb-0 ps-3">
                                <li><strong>Ratio recommandé:</strong> 16:9 (largeur/hauteur) pour un meilleur rendu</li>
                                <li><strong>Dimensions PC:</strong> 1200x675px ou 1920x1080px</li>
                                <li><strong>Dimensions Mobile:</strong> 800x450px minimum</li>
                                <li><strong>Format optimal:</strong> PNG avec transparence ou JPG haute qualité</li>
                                <li><strong>Poids:</strong> Optimiser l'image (< 500KB pour un chargement rapide)</li>
                            </ul>
                            <small class="form-text text-muted mt-2 d-block">
                                <strong>ℹ️ Note:</strong> Les images sont automatiquement redimensionnées selon la position. 
                                Pour les positions horizontales (header, footer, homepage), privilégiez un format paysage.
                            </small>
                        </div>
                        <div id="imagePreview" class="mt-2" style="display:none;">
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width:300px;max-height:200px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="lien_url" class="form-label">URL de redirection (optionnel)</label>
                        <input type="url" class="form-control @error('lien_url') is-invalid @enderror" 
                               id="lien_url" name="lien_url" value="{{ old('lien_url') }}" 
                               placeholder="https://example.com">
                        @error('lien_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Lien vers lequel rediriger lors du clic sur la publicité</small>
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label">Position d'affichage *</label>
                        <select class="form-select @error('position') is-invalid @enderror" 
                                id="position" name="position" required>
                            <option value="">Sélectionner une position</option>
                            <option value="header" {{ old('position') === 'header' ? 'selected' : '' }}>Header (En-tête)</option>
                            <option value="sidebar" {{ old('position') === 'sidebar' ? 'selected' : '' }}>Sidebar (Barre latérale)</option>
                            <option value="footer" {{ old('position') === 'footer' ? 'selected' : '' }}>Footer (Pied de page)</option>
                            <option value="entre_articles" {{ old('position') === 'entre_articles' ? 'selected' : '' }}>Section annonces (carrousel / scroll)</option>
                            <option value="homepage_top" {{ old('position') === 'homepage_top' ? 'selected' : '' }}>Page d'accueil - Haut</option>
                            <option value="homepage_bottom" {{ old('position') === 'homepage_bottom' ? 'selected' : '' }}>Page d'accueil - Bas</option>
                            <option value="popup" {{ old('position') === 'popup' ? 'selected' : '' }}>Popup (fenêtre flottante)</option>
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Champ "après N articles" retiré : section feed fixe --}}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut" class="form-label">Date de début (optionnel)</label>
                                <input type="date" class="form-control @error('date_debut') is-invalid @enderror" 
                                       id="date_debut" name="date_debut" value="{{ old('date_debut') }}">
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Laisser vide pour commencer immédiatement</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin" class="form-label">Date de fin (optionnel)</label>
                                <input type="date" class="form-control @error('date_fin') is-invalid @enderror" 
                                       id="date_fin" name="date_fin" value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Laisser vide pour affichage permanent</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control @error('ordre') is-invalid @enderror" 
                               id="ordre" name="ordre" value="{{ old('ordre', 0) }}" min="0">
                        @error('ordre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ordre d'affichage (0 = premier, plus élevé = plus bas)</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes internes (optionnel)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Notes pour l'équipe...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <div class="card bg-light p-3 mb-3 border border-warning">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>✅ Publicité active</strong>
                                </label>
                            </div>
                            <div class="alert alert-info mb-0 py-2">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Important :</strong> Cette case doit être cochée pour que la publicité s'affiche sur le site. 
                                Par défaut, elle est cochée.
                            </div>
                        </div>

                    <div class="card bg-light p-3">
                        <h6 class="card-title">Aperçu des positions</h6>
                        <small class="text-muted">
                            <strong>Header:</strong> Bandeau sous le titre Annonces (format paysage)<br>
                            <strong>Sidebar:</strong> Colonne filtres desktop (format portrait / carré)<br>
                            <strong>Footer:</strong> Pied de page (toutes pages)<br>
                            <strong>Section annonces:</strong> Carrousel (mobile) / scroll (PC) après 3 lignes d’annonces — images carrées<br>
                            <strong>Homepage Top:</strong> Tout en haut de l’accueil, mobile + PC (paysage)<br>
                            <strong>Homepage Bottom:</strong> Bas de la liste d’annonces (paysage)<br>
                            <strong>Popup:</strong> Fenêtre flottante fermable (image verticale recommandée ~3:4)<br>
                            <em>Astuce : carré 1:1 pour la section annonces ; paysage ~16:5 pour les bandeaux.</em>
                        </small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.publicites.index') }}" class="btn btn-secondary" id="cancelBtn">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="btn-content">
                        <i class="fas fa-save"></i> Enregistrer la publicité
                    </span>
                    <span class="btn-loader" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Enregistrement en cours...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Overlay de chargement -->
<div id="loadingOverlay" style="display:none;">
    <div class="loading-content">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="text-white">Enregistrement de la publicité en cours...</p>
    </div>
</div>

<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .loading-content {
        text-align: center;
        color: white;
    }

    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const imageInput = document.getElementById('image');

    if (!form || !submitBtn || !imageInput) {
        console.error('Éléments du formulaire non trouvés');
        return;
    }

    // Fonction pour cacher le loader et réactiver les boutons
    function hideLoader() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            const btnContent = submitBtn.querySelector('.btn-content');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            if (btnContent) btnContent.style.display = 'inline-block';
            if (btnLoader) btnLoader.style.display = 'none';
        }
        if (cancelBtn) {
            cancelBtn.style.pointerEvents = 'auto';
            cancelBtn.style.opacity = '1';
        }
    }

    // Cacher le loader au chargement de la page (au cas où on revient avec des erreurs)
    hideLoader();

    // Timeout de sécurité : cacher le loader après 30 secondes maximum
    let loaderTimeout;
    
    // Aperçu de l'image
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imagePreview').style.display = 'none';
        }
    });

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', function(e) {
        // Validation côté client basique
        const imageInput = document.getElementById('image');
        const positionInput = document.getElementById('position');

        if (!imageInput.files.length) {
            e.preventDefault();
            alert('Veuillez sélectionner une image');
            return false;
        }

        if (!positionInput.value) {
            e.preventDefault();
            alert('Veuillez sélectionner une position');
            return false;
        }

        // Afficher le loader
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
            loadingOverlay.classList.add('fade-in');
        }
        
        // Désactiver les boutons
        if (submitBtn) {
            submitBtn.disabled = true;
            const btnContent = submitBtn.querySelector('.btn-content');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            if (btnContent) btnContent.style.display = 'none';
            if (btnLoader) btnLoader.style.display = 'inline-block';
        }
        
        if (cancelBtn) {
            cancelBtn.style.pointerEvents = 'none';
            cancelBtn.style.opacity = '0.5';
        }
        
        // Timeout de sécurité : cacher le loader après 30 secondes
        clearTimeout(loaderTimeout);
        loaderTimeout = setTimeout(function() {
            console.warn('Timeout: Le chargement prend trop de temps, masquage du loader');
            hideLoader();
        }, 30000);
        
        // Le formulaire continuera à s'envoyer normalement
        // Le loader sera caché automatiquement lors de la redirection
    });

    // Écouter les événements de navigation pour cacher le loader
    // (au cas où la page se recharge ou redirige)
    window.addEventListener('beforeunload', function() {
        hideLoader();
    });

    // Cacher le loader si on détecte un changement de page (via Turbo/SPA si utilisé)
    document.addEventListener('turbo:before-visit', hideLoader);
    document.addEventListener('turbo:visit', hideLoader);
});
</script>
@endpush
@endsection

