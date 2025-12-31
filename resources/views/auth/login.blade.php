@extends('layouts.auth')

@section('title', 'Connexion')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login-pro.css') }}">
    <style>
        .auth-card {
            max-width: 840px;
        }
        .auth-header {
            padding: 26px 36px;
        }
        .auth-body {
            padding: 0 36px 32px;
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 480px) {
            .auth-body {
                padding: 0 10px 20px;
                min-height: calc(100vh - 150px);
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-pro">
        <div class="login-pro__header">
            <div class="login-pro__badge">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div class="login-pro__headline">
                <h2>Bienvenue sur Lome+</h2>
                <p>Connecte-toi pour gérer tes annonces, suivre tes conversations et découvrir les nouveautés.</p>
            </div>
        </div>

        @if ($errors->has('email') || $errors->has('password'))
            <div class="login-pro__feedback login-pro__feedback--error">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Identifiants invalides. Vérifie ton e-mail et ton mot de passe.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-pro__form">
            @csrf

            <div class="login-section">
                <div class="form-item">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="email">
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-item">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-icon-group">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="login-options">
                    <label class="remember-toggle">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="remember-toggle__indicator">
                            <i class="bi bi-check"></i>
                        </span>
                        <span class="remember-toggle__label">Se souvenir de moi</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="login-options__link">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary login-submit">
                    <span>Se connecter</span>
                    <i class="bi bi-arrow-right-circle"></i>
                </button>
            </div>
        </form>

        <div class="login-pro__footer">
            <p>Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte Lome+</a></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;

                    const isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    button.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                    button.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                });
            });
        });
    </script>
@endpush
