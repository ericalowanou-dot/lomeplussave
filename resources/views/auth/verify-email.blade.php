@extends('layouts.auth')

@section('title', 'Vérification de l\'email')

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
                <i class="bi bi-envelope-check"></i>
            </div>
            <div class="forgot-pro__headline">
                <h2>Vérifie ton adresse email</h2>
                <p>Avant de continuer, vérifie ton adresse email en cliquant sur le lien que nous t'avons envoyé. Si tu n'as pas reçu l'email, nous pouvons t'en renvoyer un.</p>
            </div>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="forgot-pro__feedback forgot-pro__feedback--success">
                <i class="bi bi-check-circle"></i>
                <span>Un nouveau lien de vérification a été envoyé à l'adresse email que tu as fournie lors de l'inscription. Vérifie ta boîte de réception (et les spams).</span>
            </div>
        @endif

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

        <div class="forgot-pro__form">
            <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom: 16px;">
                @csrf
                <button type="submit" class="btn btn-primary forgot-submit">
                    <span>Renvoyer l'email de vérification</span>
                    <i class="bi bi-send"></i>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary" style="width: 100%;">
                    <span>Se déconnecter</span>
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>

        <div class="forgot-pro__footer">
            <p>Tu as déjà vérifié ton email ? <a href="{{ route('articles.index') }}">Retour à l'accueil</a></p>
        </div>
    </div>
@endsection
