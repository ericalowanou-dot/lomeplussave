@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/forgot-pro.css') }}">
    <style>
        .auth-card {
            max-width: 720px;
        }
        .auth-header {
            padding: 24px 32px;
        }
        .auth-body {
            padding: 0 32px 28px;
        }
        .password-input-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password:hover {
            color: #333;
        }
    </style>
@endpush

@section('content')
    <div class="forgot-pro">
        <div class="forgot-pro__header">
            <div class="forgot-pro__badge">
                <i class="bi bi-key"></i>
            </div>
            <div class="forgot-pro__headline">
                <h2>Créer un nouveau mot de passe</h2>
                <p>Entre ton nouveau mot de passe. Assure-toi qu'il est sécurisé et facile à retenir.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="forgot-pro__feedback forgot-pro__feedback--success">
                <i class="bi bi-check-circle"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any() || session('error_solutions'))
            <div class="forgot-pro__feedback forgot-pro__feedback--error">
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

        <form method="POST" action="{{ route('password.store') }}" class="forgot-pro__form">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-item">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="exemple@mail.com"
                    readonly>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-item">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <div class="password-input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Minimum 8 caractères">
                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-item">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <div class="password-input-wrapper">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Répète ton mot de passe">
                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" data-target="password_confirmation">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password_confirmation')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary forgot-submit">
                <span>Réinitialiser le mot de passe</span>
                <i class="bi bi-check-circle"></i>
            </button>
        </form>

        <div class="forgot-pro__footer">
            <p>Tu te souviens de ton mot de passe ? <a href="{{ route('login') }}">Retour à la connexion</a></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    const input = document.getElementById(target);
                    if (input) {
                        const isPassword = input.getAttribute('type') === 'password';
                        input.setAttribute('type', isPassword ? 'text' : 'password');
                        const icon = this.querySelector('i');
                        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                        this.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                    }
                });
            });
        });
    </script>
@endsection
