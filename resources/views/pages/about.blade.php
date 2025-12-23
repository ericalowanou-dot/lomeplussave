@extends('layouts.app2')

@section('title', 'À propos de nous - Lome+')

@section('content')
<div class="about-page-container">
    <!-- Hero Section -->
    <div class="about-hero">
        <div class="container">
            <h1 class="about-hero-title">À propos de Lome+</h1>
            <p class="about-hero-subtitle">Votre marketplace locale de confiance au Togo</p>
        </div>
    </div>

    <div class="container about-content-wrapper">
        <!-- Section Mission -->
        <section class="about-section mission-section">
            <div class="section-icon">🎯</div>
            <h2 class="section-title">Notre Mission</h2>
            <p class="section-text">
                Lome+ est une plateforme marketplace conçue pour <strong>faciliter l'utilisation et la vente au Togo</strong>. 
                Notre objectif est de créer un marché en ligne accessible, simple et efficace qui connecte les vendeurs et les acheteurs 
                dans une zone locale restreinte, permettant des transactions rapides et sécurisées.
            </p>
            <p class="section-text">
                Que vous souhaitiez vendre vos articles ou trouver ce dont vous avez besoin, Lome+ vous offre une expérience 
                fluide et intuitive pour faciliter vos transactions quotidiennes.
            </p>
        </section>

        <!-- Section Valeurs -->
        <section class="about-section values-section">
            <h2 class="section-title">Nos Valeurs</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🔒</div>
                    <h3 class="value-title">Fiabilité & Sécurité</h3>
                    <p class="value-text">Nous garantissons des transactions sécurisées et une plateforme fiable pour tous nos utilisateurs.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">✨</div>
                    <h3 class="value-title">Simplicité</h3>
                    <p class="value-text">Une interface intuitive et facile à utiliser, que vous soyez vendeur ou acheteur.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">📍</div>
                    <h3 class="value-title">Accessibilité Locale</h3>
                    <p class="value-text">Connectez-vous avec votre communauté locale pour des échanges de proximité et rapides.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h3 class="value-title">Économie Locale</h3>
                    <p class="value-text">Nous soutenons les commerçants togolais et favorisons l'économie locale.</p>
                </div>
            </div>
        </section>

        <!-- Section Fonctionnalités -->
        <section class="about-section features-section">
            <h2 class="section-title">Fonctionnalités Principales</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3 class="feature-title">Système de Boost</h3>
                    <p class="feature-text">
                        Augmentez la visibilité de vos articles grâce à notre système de boost. 
                        Vos annonces apparaîtront en priorité dans les résultats de recherche pour toucher plus d'acheteurs potentiels.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3 class="feature-title">Certification des Boutiques</h3>
                    <p class="feature-text">
                        Certifiez votre boutique pour gagner la confiance des acheteurs. 
                        Les boutiques certifiées bénéficient d'une meilleure crédibilité et d'une visibilité renforcée sur la plateforme.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3 class="feature-title">Interface Simple</h3>
                    <p class="feature-text">
                        Publiez vos articles en quelques clics. Une interface claire et intuitive 
                        qui rend la vente et l'achat accessibles à tous, même sans compétences techniques.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3 class="feature-title">Communication Directe</h3>
                    <p class="feature-text">
                        Contactez facilement les vendeurs via téléphone ou WhatsApp. 
                        Des échanges rapides et directs pour finaliser vos transactions en toute simplicité.
                    </p>
                </div>
            </div>
        </section>

        <!-- Section Équipe -->
        <section class="about-section team-section">
            <h2 class="section-title">Qui Sommes-Nous ?</h2>
            <div class="team-card">
                <div class="team-avatar">
                    <div class="avatar-initial">A</div>
                </div>
                <div class="team-info">
                    <h3 class="team-name">Kodjo prince awu</h3>
                    <p class="team-role">Fondateur & Équipe Lome+</p>
                    <p class="team-description">
                        Lome+ a été créé par <strong>Kodjo prince awu</strong> avec le soutien d'une équipe passionnée. 
                        Notre mission est de démocratiser le commerce en ligne au Togo en offrant une plateforme 
                        accessible, sécurisée et efficace pour tous.
                    </p>
                    <p class="team-description">
                        Nous croyons en l'économie locale et nous sommes engagés à faciliter les échanges 
                        commerciaux au sein de notre communauté.
                    </p>
                </div>
            </div>
        </section>

        <!-- Section Contact -->
        <section class="about-section contact-section">
            <h2 class="section-title">Contactez-Nous</h2>
            <p class="section-text">Une question ? Un problème ? Nous sommes là pour vous aider !</p>
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <h4 class="contact-label">Email</h4>
                    <a href="mailto:lomeplus80@gmail.com" class="contact-value">lomeplus80@gmail.com</a>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <h4 class="contact-label">Téléphone</h4>
                    <a href="tel:+22892088853" class="contact-value">+228 92 08 88 53</a>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">💬</div>
                    <h4 class="contact-label">WhatsApp</h4>
                    <a href="https://wa.me/22892088853" target="_blank" class="contact-value">92 08 88 53</a>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <h4 class="contact-label">Adresse</h4>
                    <p class="contact-value">Lomé Agoe assiyeye</p>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <div class="about-cta">
            <h2 class="cta-title">Prêt à commencer ?</h2>
            <p class="cta-text">Rejoignez Lome+ dès aujourd'hui et découvrez une nouvelle façon de vendre et d'acheter au Togo.</p>
            <div class="cta-buttons">
                <a href="{{ route('articles.index') }}" class="btn-cta-primary">
                    <span>Découvrir les articles</span>
                    <span class="btn-icon">→</span>
                </a>
                <a href="{{ route('articles.create') }}" class="btn-cta-secondary">
                    <span>Publier un article</span>
                    <span class="btn-icon">+</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .about-page-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding-top: 180px; /* Pour compenser header + navigation */
    }

    /* Hero Section */
    .about-hero {
        background: linear-gradient(135deg, #f4751a 0%, #ff9800 100%);
        padding: 60px 20px;
        text-align: center;
        color: white;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(244, 117, 26, 0.3);
    }

    .about-hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        color: #ffffff;
    }

    .about-hero-subtitle {
        font-size: 1.3rem;
        font-weight: 400;
        opacity: 1;
        color: #ffffff;
        text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
    }

    .about-content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 60px;
    }

    /* Sections */
    .about-section {
        background: white;
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .section-icon {
        font-size: 3rem;
        text-align: center;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        text-align: center;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: linear-gradient(135deg, #f4751a 0%, #ff9800 100%);
        border-radius: 2px;
    }

    .section-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #666;
        text-align: center;
        margin-bottom: 15px;
    }

    /* Valeurs Grid */
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .value-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #f4751a;
    }

    .value-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .value-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 12px;
    }

    .value-text {
        font-size: 0.95rem;
        color: #666;
        line-height: 1.6;
    }

    /* Features Grid */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .feature-card {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 15px;
        padding: 30px;
        border-left: 4px solid #f4751a;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
    }

    .feature-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 12px;
    }

    .feature-text {
        font-size: 0.95rem;
        color: #666;
        line-height: 1.7;
    }

    /* Team Section */
    .team-card {
        display: flex;
        align-items: flex-start;
        gap: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 20px;
        padding: 40px;
        margin-top: 30px;
    }

    .team-avatar {
        flex-shrink: 0;
    }

    .avatar-initial {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f4751a 0%, #ff9800 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        box-shadow: 0 4px 20px rgba(244, 117, 26, 0.3);
    }

    .team-info {
        flex: 1;
    }

    .team-name {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }

    .team-role {
        font-size: 1.1rem;
        color: #f4751a;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .team-description {
        font-size: 1rem;
        line-height: 1.8;
        color: #666;
        margin-bottom: 15px;
    }

    /* Contact Section */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .contact-item {
        text-align: center;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .contact-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        background: white;
    }

    .contact-icon {
        font-size: 2.5rem;
        margin-bottom: 12px;
    }

    .contact-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .contact-value {
        font-size: 0.95rem;
        color: #333;
        text-decoration: none;
        font-weight: 500;
        word-break: break-word;
        display: block;
    }
    
    .contact-value a {
        color: #333;
    }

    .contact-value:hover,
    .contact-value a:hover {
        text-decoration: underline;
        color: #f4751a;
    }

    /* CTA Section */
    .about-cta {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 50px 30px;
        text-align: center;
        color: white;
        margin-top: 40px;
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
    }

    .cta-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .cta-text {
        font-size: 1.1rem;
        margin-bottom: 30px;
        opacity: 0.95;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-cta-primary,
    .btn-cta-secondary {
        padding: 16px 32px;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-cta-primary {
        background: white;
        color: #667eea;
    }

    .btn-cta-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        color: #5568d3;
    }

    .btn-cta-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid white;
    }

    .btn-cta-secondary:hover {
        background: white;
        color: #667eea;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-icon {
        font-size: 1.2rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .about-page-container {
            padding-top: 170px;
        }

        .about-hero-title {
            font-size: 2rem;
        }

        .about-hero-subtitle {
            font-size: 1.1rem;
        }

        .about-section {
            padding: 25px 20px;
        }

        .section-title {
            font-size: 1.6rem;
        }

        .values-grid,
        .features-grid {
            grid-template-columns: 1fr;
        }

        .team-card {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
        }

        .avatar-initial {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
        }

        .contact-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .cta-buttons {
            flex-direction: column;
        }

        .btn-cta-primary,
        .btn-cta-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
