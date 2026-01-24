@extends('layouts.auth')

@section('title', 'Confirmation du mot de passe')

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
                <i class="bi bi-shield-lock"></i>
            </div>
            <div class="forgot-pro__headline">
                <h2>Confirme ton mot de passe</h2>
                <p>Il s'agit d'une zone sécurisée de l'application. Confirme ton mot de passe pour continuer.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="forgot-pro__feedback forgot-pro__feedback--error">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}" class="forgot-pro__form">
            @csrf

            <div class="form-item">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="password-input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autofocus
                        autocomplete="current-password"
                        placeholder="Entre ton mot de passe">
                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary forgot-submit">
                <span>Confirmer</span>
                <i class="bi bi-check-circle"></i>
            </button>
        </form>

        <div class="forgot-pro__footer">
            <p><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></p>
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
