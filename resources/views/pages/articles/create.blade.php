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

        /* Styles pour la zone d'upload moderne */
        .upload-zone {
            border: 2px dashed rgba(102, 126, 234, 0.4);
            border-radius: 16px;
            padding: 48px 24px;
            text-align: center;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: rgba(102, 126, 234, 0.6);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        }

        .upload-zone.dragover {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
            transform: scale(1.02);
        }

        .upload-zone.upload-zone--max {
            pointer-events: none;
            opacity: 0.7;
        }

        .upload-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 16px;
        }

        .upload-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .upload-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            cursor: pointer;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .upload-btn i {
            margin-right: 8px;
        }

        .upload-hint {
            font-size: 13px;
            color: #6b7280;
            margin-top: 16px;
            margin-bottom: 0;
        }

        .error-container {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            color: #991b1b;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .photo-grid-container {
            animation: fadeIn 0.3s ease;
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

            <div id="general-error-ajax" class="error-container" style="display:none;" role="alert"></div>

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
                            
                            <!-- Zone de drop/upload moderne -->
                            <div class="upload-zone" id="upload-zone">
                                <div class="upload-icon">
                                    <i class="bi bi-cloud-upload"></i>
                                </div>
                                <div class="upload-title">Glissez-déposez vos images ici</div>
                                <div class="upload-subtitle">ou</div>
                                <button type="button" id="selectMultipleBtn" class="upload-btn">
                                    <i class="bi bi-images"></i> Sélectionner plusieurs images
                                </button>
                                <input type="file" id="multiple-file-input" accept="image/*" multiple hidden>
                                <p class="upload-hint">
                                    <i class="bi bi-info-circle"></i> 
                                    Jusqu'à 6 images (30 Mo par fichier). Formats : JPG, PNG, GIF, WebP, BMP, HEIC/HEIF, AVIF, SVG
                                </p>
                            </div>

                            <!-- Container des erreurs -->
                            <div id="photos-error-container" class="error-container" style="display:none;"></div>

                            <!-- Grille de photos (masquée par défaut) -->
                            <div id="photo-grid-container" class="photo-grid-container" style="display: none;">
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
                            </div>

                            <p class="form-hint">Formats acceptés : JPG, PNG, GIF, WebP, BMP, HEIC/HEIF, AVIF, SVG – 6 images maximum (30 Mo par fichier). L'application optimisera automatiquement vos images.</p>

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

                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror article-textarea" rows="4" placeholder="Décris l'état, les spécificités, les accessoires inclus, la garantie, etc." required minlength="20" maxlength="1500">{{ old('description') }}</textarea>

                            <p class="form-hint">Plus ta description est précise, plus ton annonce inspire confiance. <span id="description-counter" style="font-weight: 600; color: #6b7280;"></span></p>
                            <div id="description-error" class="field-error" style="display:none;"></div>
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

            const maxFileSize = 30 * 1024 * 1024; // 30 Mo

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



            const photoErrorContainer = $('#photos-error-container'); // En haut
            const photoError = $('#photos-error'); // En bas

            // Afficher erreur en haut (erreurs générales)
            const showPhotoErrorTop = (message) => {
                if (!photoErrorContainer.length) return;
                
                // Masquer l'erreur du bas si elle existe
                clearPhotoErrorBottom();
                
                photoErrorContainer.text(message).slideDown(300);
                
                // Scroll vers l'erreur
                $('html, body').animate({
                    scrollTop: photoErrorContainer.offset().top - 100
                }, 400);
            };

            // Afficher erreur en bas (erreurs spécifiques à un élément)
            const showPhotoErrorBottom = (message) => {
                if (!photoError.length) return;
                
                // Masquer l'erreur du haut si elle existe
                clearPhotoErrorTop();
                
                photoError.text(message).show();
                
                // Scroll vers l'erreur
                $('html, body').animate({
                    scrollTop: photoError.offset().top - 100
                }, 400);
            };

            // Fonction générique qui choisit automatiquement
            const showPhotoError = (message, isGeneral = true) => {
                if (isGeneral) {
                    showPhotoErrorTop(message);
                } else {
                    showPhotoErrorBottom(message);
                }
            };

            // Masquer erreur en haut
            const clearPhotoErrorTop = () => {
                if (!photoErrorContainer.length) return;
                photoErrorContainer.slideUp(300, function() {
                    $(this).text('').hide();
                });
            };

            // Masquer erreur en bas
            const clearPhotoErrorBottom = () => {
                if (!photoError.length) return;
                photoError.text('').hide();
            };

            // Masquer toutes les erreurs
            const clearPhotoError = () => {
                clearPhotoErrorTop();
                clearPhotoErrorBottom();
            };

            const showPhotoGrid = () => {

                $('#photo-grid-container').fadeIn(300);

                $('#upload-zone').fadeOut(300);

            };

            const hidePhotoGridIfEmpty = () => {

                const hasFiles = $('.file-input').toArray().some(input => input.files && input.files.length > 0);

                if (!hasFiles) {

                    $('#photo-grid-container').fadeOut(300);

                    $('#upload-zone').fadeIn(300);

                }

                updateUploadZoneMax6();

            };

            const photoCount = () => $('.file-input').toArray().filter(input => input.files && input.files.length > 0).length;

            const updateUploadZoneMax6 = () => {

                const n = photoCount();

                const zone = $('#upload-zone');

                const btn = $('#selectMultipleBtn');

                const atMax = n >= 6;

                if (btn.length) btn.prop('disabled', atMax).css('opacity', atMax ? 0.6 : 1);

                zone.toggleClass('upload-zone--max', atMax);

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

                        showPhotoErrorBottom('Veuillez sélectionner un fichier image (jpg, png, gif, webp).');

                        this.value = '';

                        revokePreviewUrl();

                        preview.hide().attr('src', placeholderImage);

                        addIcon.show();

                        removeBtn.hide();

                        updatePrimaryRequirement();

                        return;

                    }



                    if (file.size > maxFileSize) {

                        showPhotoErrorBottom('Chaque image doit faire moins de 30 Mo. L\'application optimisera automatiquement vos images.');

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

                    showPhotoGrid();

                    updateUploadZoneMax6();

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

                    hidePhotoGridIfEmpty();

                    updateUploadZoneMax6();

                });

            });

            updateUploadZoneMax6();

            const handleMultipleFiles = (files) => {

                if (files.length === 0) return;
                if (photoCount() >= 6) {
                    showPhotoErrorTop('Vous ne pouvez pas ajouter plus de 6 images.');
                    return;
                }

                const existingFiles = photoCount();
                const totalFiles = existingFiles + files.length;
                if (totalFiles > 6) {
                    showPhotoErrorTop('Vous ne pouvez pas avoir plus de 6 images au total. Vous avez déjà ' + existingFiles + ' image(s) et vous essayez d\'en ajouter ' + files.length + '.');
                    return;
                }

                // Vérifier la taille de chaque fichier
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    
                    if (!file.type.startsWith('image/')) {
                        showPhotoErrorTop('Le fichier "' + file.name + '" n\'est pas une image valide. Format requis : JPG, PNG, GIF, WebP.');
                        return;
                    }
                    
                    if (file.size > maxFileSize) {
                        showPhotoErrorTop('L\'image "' + file.name + '" est trop grande (' + (file.size / 1024 / 1024).toFixed(2) + ' Mo). Maximum : 30 Mo.');
                        return;
                    }
                }

                // Trouver les slots disponibles (vides)
                const availableSlots = [];
                for (let i = 1; i <= 6; i++) {
                    const input = $('#file-input-' + i);
                    if (input.length && (!input[0].files || input[0].files.length === 0)) {
                        availableSlots.push(i);
                    }
                }

                if (availableSlots.length < files.length) {
                    showPhotoErrorTop('Il n\'y a que ' + availableSlots.length + ' emplacement(s) disponible(s) pour ' + files.length + ' image(s).');
                    return;
                }

                // Distribuer les fichiers dans les slots disponibles
                files.forEach((file, index) => {
                    if (index < availableSlots.length) {
                        const slotIndex = availableSlots[index];
                        const input = $('#file-input-' + slotIndex)[0];
                        const preview = $('#photo-preview-' + slotIndex);
                        const addIcon = $('#add-icon-' + slotIndex);
                        const removeBtn = $('#remove-btn-' + slotIndex);

                        // Créer un DataTransfer pour assigner le fichier
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        input.files = dataTransfer.files;

                        // Afficher la prévisualisation
                        const objectUrl = URL.createObjectURL(file);
                        preview.attr('src', objectUrl).data('objectUrl', objectUrl).show();
                        addIcon.hide();
                        removeBtn.show();
                    }
                });

                updatePrimaryRequirement();
                showPhotoGrid();
                updateUploadZoneMax6();
            };

            // Gestion de la sélection multiple
            const selectMultipleBtn = $('#selectMultipleBtn');
            const multipleFileInput = $('#multiple-file-input');
            const uploadZone = $('#upload-zone');


            // Handler du bouton - utiliser délégation au niveau du document pour éviter les conflits
            $(document).on('click', '#selectMultipleBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const input = $('#multiple-file-input')[0];
                if (input) {
                    input.click();
                }
                return false;
            });

            multipleFileInput.on('change', function() {
                clearPhotoError();
                const files = Array.from(this.files);
                handleMultipleFiles(files);
                this.value = '';
            });

            // Gestion du drag & drop
            uploadZone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            uploadZone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            uploadZone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                clearPhotoError();
                const files = Array.from(e.originalEvent.dataTransfer.files);
                handleMultipleFiles(files);
            });

            // Clic sur la zone (mais pas sur le bouton) - désactivé pour éviter les conflits
            // Le bouton gère déjà le clic, pas besoin de handler sur la zone

            updatePrimaryRequirement();
            
            // Compteur de caractères pour la description
            const descriptionField = $('#description');
            const descriptionCounter = $('#description-counter');
            const minLength = 20;
            const maxLength = 1500;
            
            const updateDescriptionCounter = function() {
                const length = descriptionField.val().trim().length;
                let counterText = '(' + length + ' / ' + maxLength + ' caractères)';
                if (length < minLength) {
                    counterText += ' - Minimum ' + minLength + ' caractères requis';
                    descriptionCounter.css('color', '#dc2626');
                } else if (length > maxLength) {
                    counterText += ' - Maximum ' + maxLength + ' caractères';
                    descriptionCounter.css('color', '#dc2626');
                } else {
                    descriptionCounter.css('color', '#6b7280');
                }
                descriptionCounter.text(counterText);
            };
            
            descriptionField.on('input', updateDescriptionCounter);
            updateDescriptionCounter();

        });

    </script>

    <script>
        (function() {
            const form = document.getElementById('articleCreateForm');
            const overlay = document.getElementById('loading-overlay');
            const submitBtn = form && form.querySelector('button[type="submit"]');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            function clearAllErrors() {
                ['photos-error-container', 'photos-error', 'categorie-error', 'sous-categorie-error', 'description-error', 'general-error-ajax'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) { el.textContent = ''; el.style.display = 'none'; }
                });
            }

            function showFieldError(id, msg) {
                const el = document.getElementById(id);
                if (el) { el.textContent = msg; el.style.display = 'block'; return el; }
                return null;
            }

            function showGeneralError(msg, solutions) {
                const el = showFieldError('general-error-ajax', msg);
                if (el && Array.isArray(solutions) && solutions.length) {
                    el.innerHTML = msg + '<ul style="margin:8px 0 0;padding-left:18px;">'
                        + solutions.map(function(s) { return '<li>' + s + '</li>'; }).join('')
                        + '</ul>';
                }
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function compressImageFile(file, maxDim, quality) {
                maxDim = maxDim || 1280;
                quality = quality || 0.75;

                if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/gif') {
                    return Promise.resolve(file);
                }

                // Même les ~1 Mo sont recompressés : limite hébergeurs souvent 2–8 Mo pour tout le POST
                if (file.size < 350 * 1024 && maxDim >= 1280) {
                    return Promise.resolve(file);
                }

                return new Promise(function(resolve) {
                    const img = new Image();
                    const objectUrl = URL.createObjectURL(file);

                    img.onload = function() {
                        URL.revokeObjectURL(objectUrl);

                        let width = img.width;
                        let height = img.height;
                        const scale = Math.min(1, maxDim / Math.max(width, height));
                        width = Math.max(1, Math.round(width * scale));
                        height = Math.max(1, Math.round(height * scale));

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        if (!ctx) {
                            resolve(file);
                            return;
                        }

                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                resolve(file);
                                return;
                            }
                            if (blob.size >= file.size && file.type === 'image/jpeg') {
                                resolve(file);
                                return;
                            }
                            const name = (file.name || 'photo.jpg').replace(/\.[^.]+$/, '') + '.jpg';
                            resolve(new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() }));
                        }, 'image/jpeg', quality);
                    };

                    img.onerror = function() {
                        URL.revokeObjectURL(objectUrl);
                        resolve(file);
                    };

                    img.src = objectUrl;
                });
            }

            async function compressImageAggressively(file) {
                let out = await compressImageFile(file, 1280, 0.75);
                if (out.size > 520 * 1024) {
                    out = await compressImageFile(out, 1024, 0.65);
                }
                if (out.size > 400 * 1024) {
                    out = await compressImageFile(out, 900, 0.58);
                }
                if (out.size > 320 * 1024) {
                    out = await compressImageFile(out, 800, 0.52);
                }
                return out;
            }

            async function buildFormDataWithCompressedPhotos(sourceForm, aggressive) {
                const fd = new FormData(sourceForm);
                const fileInputs = sourceForm.querySelectorAll('input[type="file"].file-input');
                const compress = aggressive ? compressImageAggressively : async function(f) {
                    let out = await compressImageFile(f, 1280, 0.75);
                    if (out.size > 520 * 1024) {
                        out = await compressImageFile(out, 1024, 0.65);
                    }
                    return out;
                };

                for (let i = 0; i < fileInputs.length; i++) {
                    const input = fileInputs[i];
                    if (input.files && input.files[0]) {
                        const compressed = await compress(input.files[0]);
                        fd.set(input.name, compressed, compressed.name || 'photo.jpg');
                    }
                }

                return fd;
            }

            function messageForHttpStatus(status) {
                if (status === 419) {
                    return 'Votre session a expiré. Rechargez la page, reconnectez-vous si besoin, puis réessayez.';
                }
                if (status === 413) {
                    return 'Les photos sont trop volumineuses pour le serveur. Réessayez : compression renforcée.';
                }
                if (status === 408 || status === 504) {
                    return 'Le serveur met trop de temps à traiter les photos. Réessayez avec moins d\'images ou une meilleure connexion.';
                }
                if (status >= 500) {
                    return 'Erreur serveur temporaire. Réessayez dans quelques instants.';
                }
                if (status === 0) {
                    return 'Connexion interrompue pendant l\'envoi. Souvent dû à la taille des photos ou au réseau mobile.';
                }
                return 'Erreur lors de l\'envoi (code ' + status + '). Réessayez.';
            }

            async function parseArticleStoreResponse(response) {
                const contentType = response.headers.get('content-type') || '';
                let data = {};

                if (contentType.includes('application/json')) {
                    try {
                        data = await response.json();
                    } catch (e) {
                        data = {};
                    }
                }

                if (!response.ok && !data.message && !data.errors) {
                    data.message = messageForHttpStatus(response.status);
                }

                return { ok: response.ok, status: response.status, data: data };
            }

            function postArticleFormData(url, fd, csrf) {
                const controller = new AbortController();
                const timeoutId = setTimeout(function() {
                    controller.abort();
                }, 90000);

                return fetch(url, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf?.value || csrfMeta?.content || ''
                    }
                }).finally(function() {
                    clearTimeout(timeoutId);
                });
            }

            async function submitArticleForm() {
                clearAllErrors();
                if (overlay) overlay.style.display = 'flex';
                if (submitBtn) submitBtn.disabled = true;

                const csrf = form.querySelector('input[name="_token"]');
                const url = form.action;

                async function attempt(aggressive) {
                    const fd = await buildFormDataWithCompressedPhotos(form, aggressive);
                    const response = await postArticleFormData(url, fd, csrf);
                    return parseArticleStoreResponse(response);
                }

                try {
                    let res;
                    try {
                        res = await attempt(false);
                    } catch (firstErr) {
                        res = await attempt(true);
                    }

                    if (!res.ok && (res.status === 413 || res.status === 0 || res.status === 408 || res.status === 504)) {
                        try {
                            res = await attempt(true);
                        } catch (e) {
                            throw e;
                        }
                    }

                    if (overlay) overlay.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;

                    if (res.ok && res.data && res.data.success && res.data.redirect) {
                        window.location.href = res.data.redirect;
                        return;
                    }

                    if (res.status === 422 && res.data && res.data.errors) {
                        const err = res.data.errors;
                        let first = null;
                        if (err.photos && err.photos[0]) {
                            const el = showFieldError('photos-error-container', err.photos[0]);
                            if (el) first = first || el;
                        }
                        if (err.categorie && err.categorie[0]) {
                            const el = showFieldError('categorie-error', err.categorie[0]);
                            if (el) first = first || el;
                        }
                        if (err.sous_categorie_id && err.sous_categorie_id[0]) {
                            const el = showFieldError('sous-categorie-error', err.sous_categorie_id[0]);
                            if (el) first = first || el;
                        }
                        if (err.description && err.description[0]) {
                            const el = showFieldError('description-error', err.description[0]);
                            if (el) first = first || el;
                        }
                        if (err.general && err.general[0]) {
                            showGeneralError(err.general[0], res.data.error_solutions);
                            return;
                        }
                        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        else if (res.data.message) showGeneralError(res.data.message, res.data.error_solutions);
                        return;
                    }

                    showGeneralError(
                        (res.data && res.data.message) ? res.data.message : messageForHttpStatus(res.status),
                        res.data && res.data.error_solutions
                    );
                } catch (error) {
                    if (overlay) overlay.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                    showGeneralError(messageForHttpStatus(0), [
                        'Vérifiez votre connexion Internet',
                        'Réduisez le nombre ou la taille des photos',
                        'Réessayez dans quelques instants'
                    ]);
                }
            }
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const fileInputs = document.querySelectorAll('input[type="file"].file-input');
                let hasFile = false;
                for (let i = 0; i < fileInputs.length; i++) {
                    const input = fileInputs[i];
                    if (input && input.files && input.files.length > 0) {
                        for (let j = 0; j < input.files.length; j++) {
                            const file = input.files[j];
                            if (file && file.size > 0 && file.type.startsWith('image/')) { hasFile = true; break; }
                        }
                        if (hasFile) break;
                    }
                }
                if (!hasFile) {
                    clearAllErrors();
                    showFieldError('photos-error', 'Au moins une photo est obligatoire. Veuillez sélectionner au moins une image.');
                    document.getElementById('photos-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                const categorieSelect = document.getElementById('categorie-select');
                if (!categorieSelect || !categorieSelect.value) {
                    clearAllErrors();
                    const el = showFieldError('categorie-error', 'La catégorie est obligatoire.');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                const sousCategorieSelect = document.getElementById('sous-categorie-select');
                if (!sousCategorieSelect || sousCategorieSelect.disabled || !sousCategorieSelect.value) {
                    clearAllErrors();
                    const el = showFieldError('sous-categorie-error', 'La sous-catégorie est obligatoire.');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                const descriptionField = document.getElementById('description');
                const descVal = descriptionField ? descriptionField.value.trim() : '';
                const minL = 20, maxL = 1500;
                if (!descVal || descVal.length < minL) {
                    clearAllErrors();
                    showFieldError('description-error', 'La description doit contenir au moins ' + minL + ' caractères. Vous avez actuellement ' + descVal.length + ' caractère(s).');
                    descriptionField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    descriptionField.focus();
                    return;
                }
                if (descVal.length > maxL) {
                    clearAllErrors();
                    showFieldError('description-error', 'La description ne peut pas dépasser ' + maxL + ' caractères.');
                    descriptionField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                submitArticleForm();
            });
        })();
    </script>

@endpush

