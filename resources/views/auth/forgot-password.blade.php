@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

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
    </style>
@endpush

@section('content')
    <div class="forgot-pro">
        <div class="forgot-pro__header">
            <div class="forgot-pro__badge">
                <i class="bi bi-unlock"></i>
            </div>
            <div class="forgot-pro__headline">
                <h2>Réinitialiser mon mot de passe</h2>
                <p>Entre ton adresse e-mail. Nous t’enverrons un lien sécurisé pour créer un nouveau mot de passe.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="forgot-pro__feedback forgot-pro__feedback--success">
                <i class="bi bi-check-circle"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="forgot-pro__form">
            @csrf

            <div class="form-item">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="exemple@mail.com">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary forgot-submit">
                <span>Envoyer le lien de réinitialisation</span>
                <i class="bi bi-send"></i>
            </button>
        </form>

        <div class="forgot-pro__footer">
            <p>Tu te souviens de ton mot de passe ? <a href="{{ route('login') }}">Retour à la connexion</a></p>
        </div>
    </div>
@endsection
