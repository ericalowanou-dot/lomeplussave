@extends('layouts.auth')

@section('title', 'Inscription')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/register-pro.css') }}">
    <style>
        .auth-card {
            max-width: 1040px;
        }
        .auth-header {
            padding: 28px 40px;
        }
        .auth-body {
            padding: 0 40px 40px;
        }

        @media (max-width: 480px) {
            .auth-body {
                padding: 0 10px 20px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $placeholderPhone = old('phone_full', old('telephone'));
        $placeholderWhats = old('whatsapp_full', old('whatsapp'));
    @endphp

    <div class="register-pro">
        <div class="register-pro__header">
            <div class="register-pro__badge">
                <i class="bi bi-person-plus"></i>
            </div>
            <div class="register-pro__headline">
                <h2>Créer mon compte</h2>
                <p>Rejoins la communauté Lome+ et publie tes annonces en toute confiance.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="register-pro__feedback register-pro__feedback--error">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <div>{{ session('error') }}</div>
                    @if (session('error_solutions'))
                        <div class="mt-2" style="margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                            <strong>Suggestions :</strong>
                            <ul style="margin-top: 4px; padding-left: 20px;">
                                @foreach (session('error_solutions') as $solution)
                                    <li>{{ $solution }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($errors->any() && !session('error'))
            <div class="register-pro__feedback register-pro__feedback--error">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    @if (session('error_solutions'))
                        <div class="mt-2" style="margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                            <strong>Suggestions :</strong>
                            <ul style="margin-top: 4px; padding-left: 20px;">
                                @foreach (session('error_solutions') as $solution)
                                    <li>{{ $solution }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm" class="register-pro__form">
            @csrf

            <div class="register-pro__content">
                <div class="register-pro__main">
                    <section class="register-section">
                        <div class="register-section__header">
                            <div>
                                <h3 class="register-section__title"><i class="bi bi-card-text"></i> Identité</h3>
                                <p class="register-section__subtitle">Ces informations permettent d’afficher un profil professionnel.</p>
                            </div>
                            <span class="register-section__step">Étape 1</span>
                        </div>

                        <div class="register-grid register-grid--two">
                            <div class="form-item">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus autocomplete="name">
                                @error('name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-item">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="register-section">
                        <div class="register-section__header">
                            <div>
                                <h3 class="register-section__title"><i class="bi bi-telephone"></i> Coordonnées</h3>
                                <p class="register-section__subtitle">Renseigne tes numéros pour te contacter rapidement.</p>
                            </div>
                            <span class="register-section__step">Étape 2</span>
                        </div>

                        <div class="register-grid register-grid--two">
                            <div class="form-item">
                                <label for="telephone" class="form-label">Numéro de téléphone</label>
                                <div class="phone-field">
                                    <input
                                        type="tel"
                                        id="telephone"
                                        name="telephone"
                                        class="form-control phone-input @error('telephone') is-invalid @enderror"
                                        value="{{ $placeholderPhone }}"
                                        data-initial-full="{{ $placeholderPhone }}"
                                        required
                                        autocomplete="tel">
                                    <input type="hidden" name="phone_country_code" id="phone_country_code" value="{{ old('phone_country_code') }}">
                                    <input type="hidden" name="phone_full" id="phone_full" value="{{ old('phone_full') }}">
                                </div>
                                <div class="field-hint phone-validation-message"></div>
                                @error('telephone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-item">
                                <label for="whatsapp" class="form-label">Numéro WhatsApp </label>
                                <div class="phone-field">
                                    <input
                                        type="tel"
                                        id="whatsapp"
                                        name="whatsapp"
                                        class="form-control phone-input @error('whatsapp') is-invalid @enderror"
                                        value="{{ $placeholderWhats }}"
                                        data-initial-full="{{ $placeholderWhats }}"
                                        autocomplete="tel">
                                    <input type="hidden" name="whatsapp_country_code" id="whatsapp_country_code" value="{{ old('whatsapp_country_code') }}">
                                    <input type="hidden" name="whatsapp_full" id="whatsapp_full" value="{{ old('whatsapp_full') }}">
                                </div>
                                <div class="field-hint whatsapp-validation-message"></div>
                                @error('whatsapp')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror

                                <label class="match-toggle">
                                    <input type="checkbox" id="same_as_phone" name="same_as_phone" {{ old('same_as_phone') ? 'checked' : '' }}>
                                    <span class="match-toggle__indicator"><i class="bi bi-link-45deg"></i></span>
                                    <span class="match-toggle__label">Utiliser le même numéro que le téléphone</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="register-section">
                        <div class="register-section__header">
                            <div>
                                <h3 class="register-section__title"><i class="bi bi-shield-check"></i> Sécurité</h3>
                                <p class="register-section__subtitle">Crée un mot de passe robuste pour sécuriser ton compte.</p>
                            </div>
                            <span class="register-section__step">Étape 3</span>
                        </div>

                        <div class="form-item">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-icon-group">
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="field-error">
                                    <strong>{{ $message }}</strong>
                                    <p style="margin-top: 8px; font-size: 14px; color: #6b7280;">
                                        💡 <strong>Solution :</strong> Ajoutez des caractères pour atteindre 6 caractères minimum.
                                    </p>
                                </div>
                            @enderror
                            <ul class="password-rules">
                                <li id="cond-length">• Au moins 6 caractères</li>
                                {{-- Autres critères de mot de passe fort commentés - à réactiver plus tard
                                <small class="password-strength" id="passwordStrength"></small>
                                <li id="cond-upper">• Une majuscule</li>
                                <li id="cond-lower">• Une minuscule</li>
                                <li id="cond-digit">• Un chiffre</li>
                                <li id="cond-special">• Un caractère spécial</li>
                                --}}
                            </ul>
                        </div>
                    </section>

                    <div class="register-actions">
                        <button type="submit" class="btn btn-primary register-actions__submit">
                            <span>Créer mon compte</span>
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        <p class="register-actions__note">En créant un compte, tu acceptes nos conditions d’utilisation et notre politique de confidentialité.</p>
                    </div>
                </div>

                <aside class="register-pro__aside">
                    <div class="register-aside__card register-aside__card--gradient">
                        <h4><i class="bi bi-lightbulb"></i> Conseils Express</h4>
                        <ul>
                            <li>Utilise ton vrai nom pour inspirer confiance.</li>
                            <li>Vérifie tes coordonnées pour ne louper aucun contact.</li>
                            <li>Choisis un mot de passe unique et complexe.</li>
                        </ul>
                    </div>

                    <div class="register-aside__card">
                        <h4><i class="bi bi-shield-lock"></i> Pourquoi se créer un compte&nbsp;?</h4>
                        <p>• Publie et gère tes annonces depuis un espace unique.<br>
                           • Discute avec les acheteurs en toute sécurité.<br>
                           • Bénéficie d’un suivi personnalisé.</p>
                    </div>

                    <div class="register-aside__card register-aside__card--neutral">
                        <h4><i class="bi bi-headset"></i> Besoin d’aide&nbsp;?</h4>
                        <p>Notre équipe support est disponible pour t’aider à compléter ton inscription ou vérifier ton compte.</p>
                    </div>
                </aside>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <p>Déjà membre ? <a href="{{ route('login') }}">Connecte-toi</a></p>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('registerForm');
            const phoneInput = document.getElementById('telephone');
            const whatsappInput = document.getElementById('whatsapp');
            const sameAsPhoneCheckbox = document.getElementById('same_as_phone');
            const phoneCountryField = document.getElementById('phone_country_code');
            const phoneFullField = document.getElementById('phone_full');
            const whatsappCountryField = document.getElementById('whatsapp_country_code');
            const whatsappFullField = document.getElementById('whatsapp_full');
            const phoneMessage = document.querySelector('.phone-validation-message');
            const whatsappMessage = document.querySelector('.whatsapp-validation-message');

            const loaderOptions = {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
                separateDialCode: true,
                initialCountry: "auto",
                preferredCountries: ["TG", "FR", "BJ", "GH", "CI"],
                formatOnDisplay: true,
                autoPlaceholder: "aggressive",
                geoIpLookup: callback => {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data && data.country_code ? data.country_code : "TG"))
                        .catch(() => callback("TG"));
                }
            };

            const setCountryByDialCode = (itiInstance, dialCode) => {
                if (!dialCode) return;
                const match = itiInstance.getCountryData().find(country => country.dialCode === String(dialCode));
                if (match) {
                    itiInstance.setCountry(match.iso2);
                }
            };

            const initIntlTelInput = (input) => {
                if (!input || !window.intlTelInput) {
                    return null;
                }
                return window.intlTelInput(input, loaderOptions);
            };

            const phoneIti = initIntlTelInput(phoneInput);
            const whatsappIti = initIntlTelInput(whatsappInput);

            const validatePhoneNumber = (itiInstance, input, messageContainer, isOptional = false) => {
                if (!input || !itiInstance) return true;

                const rawValue = input.value.trim();

                if (!rawValue) {
                    input.classList.remove('valid', 'invalid');
                    if (messageContainer) messageContainer.textContent = '';
                    return isOptional;
                }

                const isValid = itiInstance.isValidNumber();
                input.classList.toggle('valid', isValid);
                input.classList.toggle('invalid', !isValid);

                if (messageContainer) {
                    messageContainer.textContent = isValid ? '✓ Numéro valide' : '⚠ Numéro invalide';
                    messageContainer.style.color = isValid ? '#4caf50' : '#f44336';
                }

                return isValid;
            };

            const syncWhatsappWithPhone = () => {
                if (!phoneIti || !whatsappIti) return;
                whatsappIti.setNumber(phoneIti.getNumber());
                whatsappIti.setCountry(phoneIti.getSelectedCountryData().iso2);
                whatsappInput.value = phoneIti.getNumber();
                whatsappMessage.textContent = '';
                whatsappInput.classList.remove('valid', 'invalid');
            };

            if (phoneIti) {
                const initialPhoneFull = phoneInput.dataset.initialFull;
                if (initialPhoneFull) {
                    phoneIti.setNumber(initialPhoneFull);
                }
                setCountryByDialCode(phoneIti, phoneCountryField.value);
            }

            if (whatsappIti) {
                const initialWhatsappFull = whatsappInput.dataset.initialFull;
                if (initialWhatsappFull) {
                    whatsappIti.setNumber(initialWhatsappFull);
                }
                setCountryByDialCode(whatsappIti, whatsappCountryField.value);
            }

            if (sameAsPhoneCheckbox && sameAsPhoneCheckbox.checked) {
                whatsappInput.readOnly = true;
                syncWhatsappWithPhone();
            }

            if (phoneInput && phoneIti) {
                phoneInput.addEventListener('input', () => {
                    validatePhoneNumber(phoneIti, phoneInput, phoneMessage, false);
                    if (sameAsPhoneCheckbox.checked) {
                        syncWhatsappWithPhone();
                    }
                });
            }

            if (whatsappInput && whatsappIti) {
                whatsappInput.addEventListener('input', () => {
                    if (sameAsPhoneCheckbox.checked) return;
                    validatePhoneNumber(whatsappIti, whatsappInput, whatsappMessage, true);
                });
            }

            if (sameAsPhoneCheckbox) {
                sameAsPhoneCheckbox.addEventListener('change', (event) => {
                    if (!whatsappInput || !whatsappIti || !phoneIti) return;
                    if (event.target.checked) {
                        whatsappInput.readOnly = true;
                        syncWhatsappWithPhone();
                    } else {
                        whatsappInput.readOnly = false;
                        whatsappInput.value = '';
                        whatsappInput.classList.remove('valid', 'invalid');
                        whatsappMessage.textContent = '';
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', (e) => {
                    const phoneValid = validatePhoneNumber(phoneIti, phoneInput, phoneMessage, false);
                    const whatsappValid = sameAsPhoneCheckbox.checked
                        ? phoneValid
                        : validatePhoneNumber(whatsappIti, whatsappInput, whatsappMessage, true);

                    if (!phoneValid || !whatsappValid) {
                        e.preventDefault();
                        return;
                    }

                    if (phoneIti) {
                        phoneCountryField.value = phoneIti.getSelectedCountryData().dialCode;
                        phoneFullField.value = phoneIti.getNumber();
                    }

                    if (sameAsPhoneCheckbox.checked) {
                        if (phoneIti) {
                            whatsappCountryField.value = phoneIti.getSelectedCountryData().dialCode;
                            whatsappFullField.value = phoneIti.getNumber();
                        }
                    } else if (whatsappIti && whatsappInput.value.trim()) {
                        whatsappCountryField.value = whatsappIti.getSelectedCountryData().dialCode;
                        whatsappFullField.value = whatsappIti.getNumber();
                    } else {
                        whatsappCountryField.value = '';
                        whatsappFullField.value = '';
                    }
                });
            }

            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    button.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                    button.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                });
            });

            // Validation simplifiée du mot de passe - seulement 6 caractères minimum
            const passwordInput = document.getElementById('password');
            const lengthRule = document.getElementById('cond-length');
            
            if (passwordInput) {
                passwordInput.addEventListener('input', () => {
                    const value = passwordInput.value || '';
                    const minLength = 6;
                    const isValid = value.length >= minLength;
                    
                    // Mise à jour visuelle de la règle "Au moins 6 caractères"
                    if (lengthRule) {
                        lengthRule.classList.toggle('valid', isValid);
                    }
                    
                    // Validation en temps réel côté client (optionnel)
                    if (value.length > 0 && value.length < minLength) {
                        passwordInput.setCustomValidity(`Le mot de passe doit contenir au moins ${minLength} caractères. Il vous manque ${minLength - value.length} caractère(s).`);
                    } else {
                        passwordInput.setCustomValidity('');
                    }
                });
            }
            
            {{-- Code pour indicateurs de mot de passe fort commenté - à réactiver plus tard
            const passwordStrength = document.getElementById('passwordStrength');
            const rules = {
                length: document.getElementById('cond-length'),
                upper: document.getElementById('cond-upper'),
                lower: document.getElementById('cond-lower'),
                digit: document.getElementById('cond-digit'),
                special: document.getElementById('cond-special')
            };

            const updateRuleState = (element, isValid) => {
                if (!element) return;
                element.classList.toggle('valid', isValid);
            };

            if (passwordInput) {
                passwordInput.addEventListener('input', () => {
                    const value = passwordInput.value || '';
                    const checks = {
                        length: value.length >= 6,
                        upper: /[A-Z]/.test(value),
                        lower: /[a-z]/.test(value),
                        digit: /\d/.test(value),
                        special: /[^A-Za-z0-9]/.test(value)
                    };

                    Object.entries(checks).forEach(([key, isValid]) => updateRuleState(rules[key], isValid));

                    const score = Object.values(checks).filter(Boolean).length;
                    if (!passwordStrength) return;

                    if (value.length === 0) {
                        passwordStrength.textContent = '';
                    } else if (score <= 2) {
                        passwordStrength.textContent = 'Mot de passe faible';
                        passwordStrength.style.color = '#f44336';
                    } else if (score === 3 || score === 4) {
                        passwordStrength.textContent = 'Mot de passe moyen';
                        passwordStrength.style.color = '#ff9800';
                    } else {
                        passwordStrength.textContent = 'Mot de passe fort';
                        passwordStrength.style.color = '#4caf50';
                    }
                });
            }
            --}}
        });
    </script>
@endpush
