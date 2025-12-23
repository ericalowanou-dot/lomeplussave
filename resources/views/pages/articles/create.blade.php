{{-- filepath: resources/views/pages/articles/create.blade.php --}}
@extends('layouts.auth')

@section('title', 'Ajouter un article')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/article-create.css') }}">
    <style>
        .auth-card {
            max-width: 1080px;
        }
        .auth-header {
            padding: 28px 40px;
        }
        .auth-body {
            padding: 0 40px 40px;
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .auth-footer {
            display: none;
        }

        @media (max-width: 480px) {
            .auth-body {
                padding: 0 10px 20px;
                min-height: calc(100vh - 150px);
            }
        }

        /* Styles pour les toggles comme dans le filtre */
        .article-options-toggles {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: rgba(248, 250, 252, 0.9);
            border-radius: 14px;
            padding: 16px 18px;
            border: 1px solid rgba(226, 232, 240, 0.7);
        }

        .filter-toggle {
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #1f2937;
            margin: 0;
        }

        .filter-toggle input {
            display: none;
        }

        .filter-toggle-switch {
            position: relative;
            width: 46px;
            height: 26px;
            border-radius: 999px;
            background: rgba(209, 213, 219, 0.85);
            transition: background 0.2s ease;
        }

        .filter-toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
            transition: transform 0.2s ease;
        }

        .filter-toggle input:checked + .filter-toggle-switch {
            background: linear-gradient(135deg, #34d399, #059669);
        }

        .filter-toggle input:checked + .filter-toggle-switch::after {
            transform: translateX(20px);
        }

        .filter-toggle-label {
            font-weight: 600;
            color: #374151;
        }
    </style>
@endpush

@section('content')
    @php
        $cities = [
            'Lomé', 'Sokodé', 'Kara', 'Kpalimé', 'Atakpamé', 'Dapaong', 'Tsévié', 'Aného', 'Mango', 'Notsé',
            'Bafilo', 'Bassar', 'Blitta', 'Tchamba', 'Vogan', 'Badou', 'Afagnan', 'Tabligbo', 'Amlamé',
            'Sotouboua', 'Kétao', 'Niamtougou', 'Kanté', 'Tchitchao', 'Kabou', 'Hahotoé', 'Kouvé',
            'Agbodrafo', 'Wahala', 'Kpélé', 'Amou-Oblo', 'Sanguéra', 'Djarkpanga', 'Tandjouaré', 'Biankouri',
            'Nyékonakpoé', 'Avedji', 'Totsi', 'Adidogomé', 'Kégué', 'Ségbé', 'Klikamé', 'Agou-Gadzépé',
            'Gando', 'Elavagnon', 'Alédjo', 'Kamina', 'Kambolé',
        ];
        $sousCategoriesData = ($sousCategoriesGrouped ?? collect())->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nom' => $item->nom,
                ];
            })->values();
        });
    @endphp

    <div class="article-create">
        <div class="article-create__header">
            <div class="article-create__badge">
                <i class="bi bi-megaphone"></i>
            </div>
            <div class="article-create__headline">
                <h2>Publier une annonce</h2>
                <p>Présente ton produit avec un maximum de clarté. Nous t’accompagnons étape par étape pour une annonce irréprochable.</p>
            </div>
        </div>

        @if(session('error_solutions'))
            <x-error-alert 
                type="error" 
                title="Erreur lors de la création de l'annonce"
                message="Une erreur s'est produite. Voici des solutions pour résoudre le problème :"
                :solutions="session('error_solutions')"
            />
        @endif

        @if($errors->has('general'))
            <x-error-alert 
                type="error" 
                title="Erreur"
                :message="$errors->first('general')"
                :solutions="session('error_solutions', [])"
            />
        @endif

        <form id="articleCreateForm" class="article-create__form" method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="article-create__content">
                <div class="article-main">
                    <section class="article-section article-section--media">
                        <div class="article-section__header">
                            <div>
                                <h3 class="article-section__title">
                                    <i class="bi bi-images"></i>
                                    Galerie photos
                                </h3>
                                <p class="article-section__subtitle">Ajoute jusqu’à 6 visuels soignés. La première photo deviendra l’image principale de ton annonce.</p>
                            </div>
                            <span class="article-section__step">Étape 1</span>
                        </div>

                        <div class="article-media">
                            <div class="photo-grid">
                                @for ($i = 1; $i <= 6; $i++)
                                    <label class="photo-slot" id="photo-slot-{{ $i }}" for="file-input-{{ $i }}">
                                        <input
                                            type="file"
                                            class="file-input"
                                            accept="image/*"
                                            id="file-input-{{ $i }}"
                                            {{ $i === 1 ? 'required' : '' }}
                                            name="photos[]"
                                            hidden
                                        >
                                        <img src="{{ asset('images/photo_icon.png') }}" id="photo-preview-{{ $i }}" class="photo-preview" alt="Aperçu {{ $i }}">
                                        <span class="add-icon" id="add-icon-{{ $i }}">
                                            <i class="bi bi-plus-lg"></i>
                                        </span>
                                        <button type="button" class="remove-btn" id="remove-btn-{{ $i }}" aria-label="Retirer la photo">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </label>
                                @endfor
                            </div>

                            <p class="form-hint">Formats acceptés : JPG, PNG, GIF, WebP, BMP, HEIC/HEIF, AVIF, SVG – 6 images maximum (5 Mo par fichier).</p>

                            @error('photos')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @if ($errors->has('photos.*'))
                                @foreach ($errors->get('photos.*') as $messages)
                                    @foreach ($messages as $message)
                                        <div class="field-error">{{ $message }}</div>
                                    @endforeach
                                @endforeach
                            @endif
                            <div id="photos-error" class="field-error" style="display:none;"></div>
                        </div>
                    </section>

                    <section class="article-section">
                        <div class="article-section__header">
                            <div>
                                <h3 class="article-section__title">
                                    <i class="bi bi-tags"></i>
                                    Catégorisation
                                </h3>
                                <p class="article-section__subtitle">Classe ton annonce pour qu’elle soit trouvée facilement par les acheteurs.</p>
                            </div>
                            <span class="article-section__step">Étape 2</span>
                        </div>

                        <div class="article-grid article-grid--two">
                            <div class="form-item">
                                <label for="categorie-select" class="form-label">Catégorie</label>
                                <select id="categorie-select" class="form-control @error('categorie') is-invalid @enderror" name="categorie" required data-old="{{ old('categorie') }}">
                                    <option value="">Sélectionnez une catégorie</option>
                                    @foreach (($categories ?? collect()) as $category)
                                        <option value="{{ $category->id }}" {{ (string) old('categorie') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="categorie-error" class="field-error" style="display:none;"></div>
                                @error('categorie')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-item">
                                <label for="sous-categorie-select" class="form-label">Sous-catégorie</label>
                                <select id="sous-categorie-select" class="form-control @error('sous_categorie_id') is-invalid @enderror" name="sous_categorie_id" {{ old('categorie') ? '' : 'disabled' }} required data-old="{{ old('sous_categorie_id') }}">
                                    <option value="">Sélectionnez une sous-catégorie</option>
                                    @php
                                        $preselectedCategory = old('categorie');
                                    @endphp
                                    @if($preselectedCategory && isset($sousCategoriesData[$preselectedCategory]))
                                        @foreach ($sousCategoriesData[$preselectedCategory] as $subCategory)
                                            <option value="{{ $subCategory['id'] }}" {{ (string) old('sous_categorie_id') === (string) $subCategory['id'] ? 'selected' : '' }}>
                                                {{ $subCategory['nom'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div id="sous-categorie-error" class="field-error" style="display:none;"></div>
                                @error('sous_categorie_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="article-section">
                        <div class="article-section__header">
                            <div>
                                <h3 class="article-section__title">
                                    <i class="bi bi-info-circle"></i>
                                    Détails de l’annonce
                                </h3>
                                <p class="article-section__subtitle">Partage les informations clés pour rassurer les potentiels acheteurs.</p>
                            </div>
                            <span class="article-section__step">Étape 3</span>
                        </div>

                        <div class="article-grid article-grid--two">
                            <div class="form-item">
                                <label for="titre" class="form-label">Titre</label>
                                <input type="text" name="titre" id="titre" class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}" placeholder="Ex : iPhone 13 Pro 256 Go" required>
                                @error('titre')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-item">
                                <label for="prix_ht" class="form-label">Prix (CFA)</label>
                                <div class="form-input--prefixed">
                                    <span class="form-input__prefix">CFA</span>
                                    <input type="number" name="prix_ht" id="prix_ht" class="form-control @error('prix_ht') is-invalid @enderror" value="{{ old('prix_ht') }}" required min="0" step="1" placeholder="Ex : 150000">
                                </div>
                                @error('prix_ht')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-item form-item--full">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror article-textarea" rows="4" placeholder="Décris l’état, les spécificités, les accessoires inclus, la garantie, etc." required>{{ old('description') }}</textarea>
                            <p class="form-hint">Plus ta description est précise, plus ton annonce inspire confiance.</p>
                            @error('description')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>

                    <section class="article-section">
                        <div class="article-section__header">
                            <div>
                                <h3 class="article-section__title">
                                    <i class="bi bi-geo-alt"></i>
                                    Localisation & options
                                </h3>
                                <p class="article-section__subtitle">Indique la localisation et les options disponibles pour faciliter la prise de contact.</p>
                            </div>
                            <span class="article-section__step">Étape 4</span>
                        </div>

                        <div class="article-grid article-grid--two">
                            <div class="form-item">
                                <label for="ville" class="form-label">Ville</label>
                                <select name="lieu" id="ville" class="form-control @error('lieu') is-invalid @enderror" required>
                                    <option value="">Sélectionnez une ville</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city }}" {{ old('lieu') === $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                @error('lieu')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="article-options-toggles">
                            <label class="filter-toggle">
                                <input type="radio" name="etat" value="neuf" {{ old('etat', 'neuf') === 'neuf' ? 'checked' : '' }}>
                                <span class="filter-toggle-switch"></span>
                                <span class="filter-toggle-label">Neuf</span>
                            </label>
                            <label class="filter-toggle">
                                <input type="radio" name="etat" value="occasion" {{ old('etat') === 'occasion' ? 'checked' : '' }}>
                                <span class="filter-toggle-switch"></span>
                                <span class="filter-toggle-label">Occasion</span>
                            </label>
                            <label class="filter-toggle">
                                <input type="checkbox" name="livraison" value="1" {{ old('livraison') ? 'checked' : '' }}>
                                <span class="filter-toggle-switch"></span>
                                <span class="filter-toggle-label">Livraison disponible</span>
                            </label>
                            @error('etat')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>

                    <div class="article-actions">
                        <button type="submit" class="btn btn-primary article-actions__submit">
                            <span>Soumettre l’annonce</span>
                            <i class="bi bi-upload"></i>
                        </button>
                        <p class="article-actions__note">
                            Ton annonce sera examinée avant publication. Tu recevras une notification dès qu’elle sera en ligne.
                        </p>
                    </div>
                </div>

                <aside class="article-aside">
                    <div class="article-aside__card article-aside__card--gradient">
                        <h4><i class="bi bi-lightning-charge"></i> Astuces express</h4>
                        <ul>
                            <li>Utilise des photos nettes, lumineuses et cadrées.</li>
                            <li>Précise les caractéristiques clés (taille, modèle, état).</li>
                            <li>Indique si les accessoires ou la garantie sont inclus.</li>
                            <li>Sois transparent sur les éventuels défauts.</li>
                        </ul>
                    </div>

                    <div class="article-aside__card">
                        <h4><i class="bi bi-clipboard-check"></i> Rappel des règles</h4>
                        <div class="article-checklist">
                            <div class="article-checklist__item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Photos sans filigrane promotionnel ou numéro de téléphone.</span>
                            </div>
                            <div class="article-checklist__item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Description claire, sans langage offensant ni coordonnées publiques.</span>
                            </div>
                            <div class="article-checklist__item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Respect des conditions générales de Lome+.</span>
                            </div>
                        </div>
                    </div>

                    <div class="article-aside__card article-aside__card--neutral">
                        <h4><i class="bi bi-clock-history"></i> Délai indicatif</h4>
                        <p>Les annonces sont généralement vérifiées en moins de 24 heures ouvrées. Tu peux suivre leur statut dans « Mes annonces ».</p>
                    </div>
                </aside>
            </div>
        </form>
    </div>

    <div id="loading-overlay" class="article-overlay">
        <div class="article-overlay__content">
            <div class="loader"></div>
            <p>Publication en cours...</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            const categorieSelect = $('#categorie-select');
            const sousCategorieSelect = $('#sous-categorie-select');
            const categorieError = $('#categorie-error');
            const photoError = $('#photos-error');
            const maxFileSize = 5 * 1024 * 1024;
            const rawOldCategory = categorieSelect.attr('data-old');
            const rawOldSubCategory = sousCategorieSelect.attr('data-old');
            const oldCategory = rawOldCategory && rawOldCategory !== 'null' && rawOldCategory !== 'undefined'
                ? String(rawOldCategory)
                : '';
            const oldSubCategory = rawOldSubCategory && rawOldSubCategory !== 'null' && rawOldSubCategory !== 'undefined'
                ? String(rawOldSubCategory)
                : '';
            const sousCategoriesData = @json($sousCategoriesData ?? []);
            const placeholderImage = @js(asset('images/photo_icon.png'));

            const resetSousCategorieSelect = (disable = true) => {
                sousCategorieSelect.html('<option value="">Sélectionnez une sous-catégorie</option>');
                sousCategorieSelect.prop('disabled', disable);
            };

            const showPhotoError = (message) => {
                if (!photoError.length) return;
                photoError.text(message).show();
            };

            const clearPhotoError = () => {
                if (!photoError.length) return;
                photoError.text('');
                photoError.hide();
            };

            const updatePrimaryRequirement = () => {
                const hasFile = $('.file-input').toArray().some(input => input.files && input.files.length > 0);
                const firstInput = $('#file-input-1');
                if (firstInput.length) {
                    firstInput.prop('required', !hasFile);
                }
            };

            const loadSousCategories = (categorieId, prefillValue = '') => {
                resetSousCategorieSelect(true);

                if (!categorieId) {
                    categorieError.hide();
                    return;
                }

                const list = sousCategoriesData[categorieId] || [];

                if (!Array.isArray(list) || list.length === 0) {
                    categorieError.text('Aucune sous-catégorie disponible pour cette catégorie.').show();
                    return;
                }

                categorieError.hide();

                list.forEach(function (sousCategorie) {
                    sousCategorieSelect.append(
                        $('<option>').val(sousCategorie.id).text(sousCategorie.nom)
                    );
                });

                sousCategorieSelect.prop('disabled', false);

                if (prefillValue) {
                    sousCategorieSelect.val(prefillValue);
                }
            };

            resetSousCategorieSelect(true);
            categorieError.hide();
            clearPhotoError();

            categorieSelect.on('change', function () {
                categorieError.hide();
                loadSousCategories($(this).val());
            });

            if (oldCategory) {
                loadSousCategories(oldCategory, oldSubCategory);
            }

            $('.photo-slot').each(function (index) {
                const slotIndex = index + 1;
                const slot = $(this);
                const fileInput = slot.find('.file-input');
                const preview = $('#photo-preview-' + slotIndex);
                const addIcon = $('#add-icon-' + slotIndex);
                const removeBtn = $('#remove-btn-' + slotIndex);

                fileInput.on('click', function () {
                    clearPhotoError();
                });

                fileInput.on('change', function () {
                    clearPhotoError();
                    const file = this.files[0];

                    const revokePreviewUrl = () => {
                        const currentUrl = preview.data('objectUrl');
                        if (currentUrl) {
                            URL.revokeObjectURL(currentUrl);
                            preview.removeData('objectUrl');
                        }
                    };

                    if (!file) {
                        revokePreviewUrl();
                        preview.hide().attr('src', placeholderImage);
                        addIcon.show();
                        removeBtn.hide();
                        updatePrimaryRequirement();
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        showPhotoError('Veuillez sélectionner un fichier image (jpg, png, gif, webp).');
                        this.value = '';
                        revokePreviewUrl();
                        preview.hide().attr('src', placeholderImage);
                        addIcon.show();
                        removeBtn.hide();
                        updatePrimaryRequirement();
                        return;
                    }

                    if (file.size > maxFileSize) {
                        showPhotoError('Chaque image doit faire moins de 5 Mo.');
                        this.value = '';
                        revokePreviewUrl();
                        preview.hide().attr('src', placeholderImage);
                        addIcon.show();
                        removeBtn.hide();
                        updatePrimaryRequirement();
                        return;
                    }

                    revokePreviewUrl();
                    const objectUrl = URL.createObjectURL(file);
                    preview.attr('src', objectUrl).data('objectUrl', objectUrl).show();
                    addIcon.hide();
                    removeBtn.show();
                    updatePrimaryRequirement();
                });

                removeBtn.on('click', function (event) {
                    event.stopPropagation();
                    event.preventDefault();
                    clearPhotoError();
                    const inputElement = fileInput.get(0);
                    if (inputElement) {
                        inputElement.value = '';
                    }
                    const currentUrl = preview.data('objectUrl');
                    if (currentUrl) {
                        URL.revokeObjectURL(currentUrl);
                        preview.removeData('objectUrl');
                    }
                    preview.hide().attr('src', placeholderImage);
                    addIcon.show();
                    removeBtn.hide();
                    updatePrimaryRequirement();
                });
            });

            updatePrimaryRequirement();
        });
    </script>
    <script>
        document.getElementById('articleCreateForm').addEventListener('submit', function(e) {
            let hasError = false;
            
            // Vérifier qu'au moins une photo est sélectionnée
            const fileInputs = document.querySelectorAll('input[type="file"].file-input');
            let hasFile = false;
            let fileCount = 0;
            
            // Parcourir tous les inputs de fichiers
            for (let i = 0; i < fileInputs.length; i++) {
                const input = fileInputs[i];
                if (input && input.files) {
                    // Vérifier chaque fichier dans l'input
                    for (let j = 0; j < input.files.length; j++) {
                        const file = input.files[j];
                        if (file && file.size > 0 && file.type.startsWith('image/')) {
                            hasFile = true;
                            fileCount++;
                            break; // Un seul fichier valide suffit pour cet input
                        }
                    }
                }
            }
            
            if (!hasFile || fileCount === 0) {
                e.preventDefault();
                const photoError = document.getElementById('photos-error');
                if (photoError) {
                    photoError.textContent = 'Au moins une photo est obligatoire. Veuillez sélectionner au moins une image.';
                    photoError.style.display = 'block';
                    photoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                hasError = true;
                document.getElementById('loading-overlay').style.display = 'none';
                return false;
            } else {
                // Masquer l'erreur des photos si tout est OK
                const photoError = document.getElementById('photos-error');
                if (photoError) {
                    photoError.style.display = 'none';
                }
            }
            
            // Vérifier la catégorie
            const categorieSelect = document.getElementById('categorie-select');
            if (!categorieSelect || !categorieSelect.value) {
                e.preventDefault();
                const categorieError = document.getElementById('categorie-error');
                if (categorieError) {
                    categorieError.textContent = 'La catégorie est obligatoire.';
                    categorieError.style.display = 'block';
                }
                if (!hasError) {
                    categorieSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    hasError = true;
                }
            }
            
            // Vérifier la sous-catégorie
            const sousCategorieSelect = document.getElementById('sous-categorie-select');
            if (!sousCategorieSelect || sousCategorieSelect.disabled || !sousCategorieSelect.value) {
                e.preventDefault();
                const sousCategorieError = document.getElementById('sous-categorie-error');
                if (sousCategorieError) {
                    sousCategorieError.textContent = 'La sous-catégorie est obligatoire.';
                    sousCategorieError.style.display = 'block';
                }
                if (!hasError) {
                    sousCategorieSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    hasError = true;
                }
            } else {
                const sousCategorieError = document.getElementById('sous-categorie-error');
                if (sousCategorieError) {
                    sousCategorieError.style.display = 'none';
                }
            }
            
            if (hasError) {
                document.getElementById('loading-overlay').style.display = 'none';
                return false;
            }
            
            // Si tout est OK, afficher le loader
            document.getElementById('loading-overlay').style.display = 'flex';
        });
    </script>
@endpush
