@extends('layouts.app2')

@section('title', 'Page introuvable - Lome+')

@section('content')
<div class="error-page-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page introuvable</h2>
        <p class="error-message">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
        </p>
        
        <div class="error-solutions">
            <h3>Que pouvez-vous faire ?</h3>
            <ul>
                <li><i class="bi bi-check-circle"></i> Vérifiez l'URL dans la barre d'adresse</li>
                <li><i class="bi bi-check-circle"></i> Utilisez le menu de navigation pour accéder aux pages principales</li>
                <li><i class="bi bi-check-circle"></i> Retournez à la <a href="{{ route('articles.index') }}">page d'accueil</a></li>
                <li><i class="bi bi-check-circle"></i> Utilisez la barre de recherche pour trouver ce que vous cherchez</li>
            </ul>
        </div>

        <div class="error-actions">
            <a href="{{ route('articles.index') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Page précédente
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
    color: #ff7b00;
    margin-bottom: 20px;
}

.error-code {
    font-size: 120px;
    font-weight: 700;
    color: #ff7b00;
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
    background: #f8f9fa;
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
    align-items: center;
    gap: 10px;
}

.error-solutions li i {
    color: #28a745;
    font-size: 18px;
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

