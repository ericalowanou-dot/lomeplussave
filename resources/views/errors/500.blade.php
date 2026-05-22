@extends('layouts.app2')

@section('title', 'Erreur serveur - Lome+')

@section('content')
<div class="error-page-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="bi bi-x-circle"></i>
        </div>
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Erreur serveur</h2>
        <p class="error-message">
            Une erreur technique s'est produite. Notre équipe a été notifiée et travaille à résoudre le problème.
        </p>
        
        <div class="error-solutions">
            <h3>Que pouvez-vous faire ?</h3>
            <ul>
                <li><i class="bi bi-check-circle"></i> <strong>Rafraîchir la page</strong> - Cliquez sur le bouton de rafraîchissement de votre navigateur</li>
                <li><i class="bi bi-check-circle"></i> <strong>Attendre quelques instants</strong> - Le problème peut être temporaire</li>
                <li><i class="bi bi-check-circle"></i> <strong>Réessayer plus tard</strong> - Notre équipe travaille à résoudre le problème</li>
                <li><i class="bi bi-check-circle"></i> <strong>Vérifier votre connexion</strong> - Assurez-vous d'avoir une connexion Internet stable</li>
                <li><i class="bi bi-check-circle"></i> <strong>Nous contacter</strong> - Si le problème persiste, <a href="mailto:lomeplus80@gmail.com">contactez-nous</a></li>
            </ul>
        </div>

        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Rafraîchir la page
            </a>
            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<style>
.error-page-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    margin-top: 175px;
}

.error-content {
    text-align: center;
    max-width: 600px;
    width: 100%;
}

.error-icon {
    font-size: 80px;
    color: #dc3545;
    margin-bottom: 20px;
}

.error-code {
    font-size: 120px;
    font-weight: 700;
    color: #dc3545;
    margin: 0;
    line-height: 1;
}

.error-title {
    font-size: 32px;
    font-weight: 600;
    color: #333;
    margin: 20px 0 15px 0;
}

.error-message {
    font-size: 18px;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.error-solutions {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 25px;
    margin: 30px 0;
    text-align: left;
}

.error-solutions h3 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
}

.error-solutions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-solutions li {
    padding: 10px 0;
    color: #555;
    font-size: 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.error-solutions li i {
    color: #28a745;
    font-size: 18px;
    margin-top: 2px;
}

.error-solutions a {
    color: #ff7b00;
    text-decoration: none;
    font-weight: 500;
}

.error-solutions a:hover {
    text-decoration: underline;
}

.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.error-actions .btn {
    padding: 12px 24px;
    font-size: 16px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.error-actions .btn-primary {
    background: #ff7b00;
    border: none;
    color: white;
}

.error-actions .btn-primary:hover {
    background: #e06f00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 123, 0, 0.3);
}

.error-actions .btn-outline-secondary {
    background: white;
    border: 2px solid #ddd;
    color: #333;
}

.error-actions .btn-outline-secondary:hover {
    border-color: #ff7b00;
    color: #ff7b00;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 80px;
    }
    .error-title {
        font-size: 24px;
    }
    .error-message {
        font-size: 16px;
    }
    .error-actions {
        flex-direction: column;
    }
    .error-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection

