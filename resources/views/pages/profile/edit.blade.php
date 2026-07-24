@extends('layouts.app2')

@section('title', 'Mon profil - Lome+')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<style>
    .profile-page {
        /* header fixe (45px) + barre nav (130px) + marge de respiration */
        --site-header-offset: calc(45px + 130px + 72px);
        margin-top: var(--site-header-offset);
        padding: 16px 16px 48px;
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
        overflow: visible;
    }

    .profile-page__header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .profile-page__icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #ff9900 0%, #f57c00 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(255, 153, 0, 0.25);
        color: #fff;
        font-size: 1.4rem;
    }

    .profile-page__title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .profile-page__subtitle {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .profile-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        padding: 24px;
        margin-bottom: 20px;
        overflow: visible;
    }

    .profile-card__title {
        margin: 0 0 6px;
        font-size: 1.05rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-card__title i {
        color: #ff9900;
    }

    .profile-card__desc {
        margin: 0 0 20px;
        font-size: 0.88rem;
        color: #6b7280;
    }

    .profile-photo {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .profile-photo__btn {
        position: relative;
        width: 110px;
        height: 110px;
        padding: 0;
        border: 3px solid #e2e8f0;
        border-radius: 50%;
        overflow: hidden;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease;
    }

    .profile-photo__btn:hover {
        border-color: #ff9900;
    }

    .profile-photo__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-photo__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: #ff9900;
        background: #fff7ed;
    }

    .profile-photo__hint {
        margin: 0;
        font-size: 0.82rem;
        color: #94a3b8;
        text-align: center;
    }

    .profile-field {
        margin-bottom: 18px;
        overflow: visible;
    }

    .profile-field .phone-field {
        position: relative;
        overflow: visible;
    }

    .profile-field .iti {
        width: 100%;
        display: block;
        position: relative;
        z-index: 1;
    }

    .profile-field .iti.iti--container-open,
    .profile-field .iti:focus-within {
        z-index: 10060;
    }

    .profile-field .iti__flag-container,
    .profile-field .iti__selected-flag {
        cursor: pointer;
        pointer-events: auto !important;
        z-index: 5;
    }

    .profile-field .iti__flag {
        background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/img/flags.png");
    }

    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .profile-field .iti__flag {
            background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/img/flags@2x.png");
        }
    }

    .profile-field .phone-input {
        width: 100% !important;
        height: 46px !important;
        border-radius: 10px !important;
        border: 1px solid #d1d5db !important;
        font-size: 0.95rem !important;
        position: relative;
        z-index: 1;
        /* Ne pas forcer padding-left : intl-tel-input le calcule (drapeau + indicatif) */
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        padding-right: 14px !important;
    }

    .profile-field .iti--separate-dial-code .phone-input {
        /* Espace min. pour drapeau + code pays (ex. +228) — évite la superposition */
        padding-left: 98px !important;
    }

    .profile-field .iti--separate-dial-code .iti__selected-dial-code {
        padding-left: 6px;
        padding-right: 6px;
        line-height: 44px;
        font-size: 0.9rem;
    }

    .profile-field .iti__flag-container {
        width: auto;
    }

    .profile-field .phone-input:focus {
        border-color: #ff9900 !important;
        box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.15) !important;
    }

    /* Liste pays — aussi quand appendée au body */
    .iti__country-list {
        z-index: 10070 !important;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        max-height: 260px !important;
        overflow-y: auto !important;
        background: #fff;
    }

    .iti__country-list.iti__hide {
        display: none !important;
    }

    .iti--container {
        z-index: 10070 !important;
        overflow: visible !important;
        max-width: none !important;
        width: auto !important;
    }

    body > .iti--container {
        overflow: visible !important;
        max-width: none !important;
    }

    body.iti-mobile .iti--container .iti__country-list {
        max-height: 280px !important;
    }

    main.flex-fill:has(.profile-page),
    .main-wrapper:has(.profile-page) {
        overflow: visible !important;
        overflow-x: visible !important;
    }

    .profile-field .iti__selected-flag {
        border-radius: 10px 0 0 10px;
    }

    .profile-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #374151;
    }

    .profile-field .form-control:not(.phone-input) {
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        font-size: 0.95rem;
    }

    .profile-field .form-control:focus {
        border-color: #ff9900;
        box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.15);
    }

    .profile-field .invalid-feedback {
        display: block;
        margin-top: 6px;
        font-size: 0.82rem;
        color: #dc3545;
    }

    .profile-alert {
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .profile-alert--success {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .profile-alert--error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .profile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 22px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }

    .profile-btn--primary {
        background: linear-gradient(135deg, #ff9900 0%, #f57c00 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(245, 124, 0, 0.3);
    }

    .profile-btn--primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(245, 124, 0, 0.35);
        color: #fff;
    }

    @media (max-width: 768px) {
        .profile-page {
            --site-header-offset: calc(45px + 130px + 88px);
            padding-bottom: 120px;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-page__header">
        <div class="profile-page__icon">
            <i class="bi bi-person-fill" aria-hidden="true"></i>
        </div>
        <div>
            <h1 class="profile-page__title">Mon profil</h1>
            <p class="profile-page__subtitle">Gérez vos informations personnelles et votre sécurité</p>
            @if(auth()->user()?->isAdmin())
                <p class="profile-page__subtitle" style="margin-top: 8px;">
                    <a href="{{ route('admin.dashboard') }}" style="color: #ff9900; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-speedometer2"></i> Retour au panneau admin
                    </a>
                </p>
            @endif
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="profile-alert profile-alert--success">
            <i class="bi bi-check-circle-fill me-1"></i> Vos informations ont été mises à jour.
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="profile-alert profile-alert--success">
            <i class="bi bi-check-circle-fill me-1"></i> Votre mot de passe a été modifié.
        </div>
    @endif

    @if (session('error'))
        <div class="profile-alert profile-alert--error">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any() && !$errors->updatePassword->any())
        <div class="profile-alert profile-alert--error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="profile-card">
        <h2 class="profile-card__title"><i class="bi bi-person-vcard"></i> Informations personnelles</h2>
        <p class="profile-card__desc">Modifiez votre nom, email, photo et numéros de contact.</p>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileInfoForm">
            @csrf
            @method('PATCH')

            <div class="profile-photo">
                <button type="button" class="profile-photo__btn" id="profilePhotoTrigger" aria-label="Changer la photo de profil">
                    <img src="{{ $user->getProfilPhotoUrl() }}"
                         alt="Photo de {{ $user->name }}"
                         class="profile-photo__img"
                         id="profilePhotoPreview"
                         onerror="this.style.display='none'; document.getElementById('profilePhotoPlaceholder').style.display='flex';">
                    <div class="profile-photo__placeholder" id="profilePhotoPlaceholder" style="display: none;">
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                </button>
                <p class="profile-photo__hint">Cliquez sur la photo pour la modifier (JPG, PNG, WEBP — max 2 Mo)</p>
                <input type="file" name="photo" id="profilePhotoInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="d-none">
            </div>

            <div class="profile-field">
                <label for="name">Nom complet <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="profile-field">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="profile-field">
                <label for="telephone">Téléphone <span class="text-danger">*</span></label>
                <div class="phone-field">
                    <input type="tel"
                           id="telephone"
                           class="form-control phone-input @error('telephone') is-invalid @enderror"
                           data-initial-full="{{ old('telephone', $user->telephone) }}"
                           autocomplete="tel">
                    <input type="hidden" name="telephone" id="telephone_full" value="{{ old('telephone', $user->telephone) }}">
                </div>
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="profile-field">
                <label for="whatsapp">WhatsApp</label>
                <div class="phone-field">
                    <input type="tel"
                           id="whatsapp"
                           class="form-control phone-input @error('whatsapp') is-invalid @enderror"
                           data-initial-full="{{ old('whatsapp', $user->whatsapp) }}"
                           autocomplete="tel">
                    <input type="hidden" name="whatsapp" id="whatsapp_full" value="{{ old('whatsapp', $user->whatsapp) }}">
                </div>
                @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="profile-btn profile-btn--primary">
                <i class="bi bi-check-lg"></i> Enregistrer les modifications
            </button>
        </form>
    </div>

    <div class="profile-card">
        <h2 class="profile-card__title"><i class="bi bi-shield-lock-fill"></i> Mot de passe</h2>
        <p class="profile-card__desc">Changez votre mot de passe pour sécuriser votre compte.</p>

        @if ($errors->updatePassword->any())
            <div class="profile-alert profile-alert--error mb-3">
                @foreach ($errors->updatePassword->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="profile-field">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       autocomplete="current-password">
                @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="profile-field">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       autocomplete="new-password">
                @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="profile-field">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" autocomplete="new-password">
            </div>

            <button type="submit" class="profile-btn profile-btn--primary">
                <i class="bi bi-key-fill"></i> Mettre à jour le mot de passe
            </button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var photoTrigger = document.getElementById('profilePhotoTrigger');
    var photoInput = document.getElementById('profilePhotoInput');
    var photoPreview = document.getElementById('profilePhotoPreview');
    var photoPlaceholder = document.getElementById('profilePhotoPlaceholder');

    if (photoTrigger && photoInput) {
        photoTrigger.addEventListener('click', function () {
            photoInput.click();
        });

        photoInput.addEventListener('change', function () {
            var file = photoInput.files && photoInput.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
                if (photoPlaceholder) photoPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }

    var itiOptions = {
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js',
        separateDialCode: true,
        initialCountry: 'tg',
        preferredCountries: ['tg', 'fr', 'bj', 'gh', 'ci'],
        formatOnDisplay: true,
        dropdownContainer: document.body,
        useFullscreenPopup: false,
        nationalMode: false,
        autoPlaceholder: 'aggressive'
    };

    function initProfilePhoneInput(input) {
        if (!input || !window.intlTelInput) {
            return null;
        }

        var iti = window.intlTelInput(input, itiOptions);
        var fullNumber = input.dataset.initialFull;

        if (fullNumber) {
            var applyNumber = function () {
                iti.setNumber(fullNumber);
            };

            if (window.intlTelInputUtils) {
                applyNumber();
            } else {
                var attempts = 0;
                var timer = setInterval(function () {
                    attempts++;
                    if (window.intlTelInputUtils || attempts > 30) {
                        clearInterval(timer);
                        applyNumber();
                    }
                }, 100);
            }
        }

        return iti;
    }

    var phoneInput = document.getElementById('telephone');
    var whatsappInput = document.getElementById('whatsapp');
    var phoneFull = document.getElementById('telephone_full');
    var whatsappFull = document.getElementById('whatsapp_full');
    var profileForm = document.getElementById('profileInfoForm');

    var phoneIti = initProfilePhoneInput(phoneInput);
    var whatsappIti = initProfilePhoneInput(whatsappInput);

    if (profileForm) {
        profileForm.addEventListener('submit', function () {
            if (phoneIti && phoneFull) {
                phoneFull.value = phoneIti.getNumber() || phoneInput.value.trim();
            }
            if (whatsappIti && whatsappFull) {
                whatsappFull.value = whatsappIti.getNumber() || whatsappInput.value.trim();
            }
        });
    }
});
</script>
@endsection
