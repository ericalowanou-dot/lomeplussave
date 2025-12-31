<footer class="main-footer">
    <div class="footer-container">
        <!-- Section principale du footer -->
        <div class="footer-main">
            <!-- Colonne 1: À propos -->
            <div class="footer-column">
                <h3 class="footer-title">À propos de Lome+</h3>
                <p class="footer-description">
                    Lome+ est une marketplace locale qui facilite la vente et l'achat au Togo. 
                    Une plateforme simple, accessible et sécurisée pour tous.
                </p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/share/18TxYF2WDD/?mibextid=wwXIfr" target="_blank" class="social-link facebook" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com/lomep.lus?igsh=MTM5b3F6MTVnbjN0cA%3D%3D&utm_source=qr" target="_blank" class="social-link instagram" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://youtube.com/@lomeplus?si=fp8VI3itFRGLHXy9" target="_blank" class="social-link youtube" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@lomeplus?_r=1&_t=ZS-91npd7hjNXI" target="_blank" class="social-link tiktok" title="TikTok">
                        <i class="bi bi-tiktok"></i>
                    </a>
                    <a href="https://whatsapp.com/channel/0029VatlBs06GcG5owxIlF0T" target="_blank" class="social-link whatsapp" title="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Colonne 2: Liens rapides -->
            <div class="footer-column">
                <h3 class="footer-title">Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('articles.index') }}">Accueil</a></li>
                    <li><a href="{{ route('about') }}">À propos</a></li>
                    <li><a href="{{ route('articles.create') }}">Publier un article</a></li>
                    <li><a href="{{ route('mes_annonces') }}">Ma boutique</a></li>
                    <li><a href="{{ route('mes_favoris') }}">Mes favoris</a></li>
                </ul>
            </div>

            <!-- Colonne 3: Support -->
            <div class="footer-column">
                <h3 class="footer-title">Support</h3>
                <ul class="footer-links">
                    <li><a href="mailto:lomeplus80@gmail.com">Contact</a></li>
                    <li><a href="tel:+22892088853">Appeler</a></li>
                    <li><a href="https://wa.me/22892088853" target="_blank">WhatsApp</a></li>
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/conditions">Conditions d'utilisation</a></li>
                </ul>
            </div>

            <!-- Colonne 4: Contact -->
            <div class="footer-column">
                <h3 class="footer-title">Contact</h3>
                <div class="footer-contact">
                    <div class="contact-item">
                        <i class="bi bi-envelope"></i>
                        <a href="mailto:lomeplus80@gmail.com">lomeplus80@gmail.com</a>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone"></i>
                        <a href="tel:+22892088853">+228 92 08 88 53</a>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-whatsapp"></i>
                        <a href="https://wa.me/22892088853" target="_blank">92 08 88 53</a>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-geo-alt"></i>
                        <span>Lomé Agoe assiyeye</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section infos légales -->
        <div class="footer-legal">
            <div class="legal-info">
                <p>&copy; {{ date('Y') }} <strong>Lome+</strong>. Tous droits réservés.</p>
                <div class="legal-details">
                    <span>Raison sociale : <strong>[RAISON_SOCIALE]</strong></span>
                    <span>•</span>
                    <span>Immatriculation : <strong>[IMMATRICULATION]</strong></span>
                    <span>•</span>
                    <span>Directeur de publication : <strong>Kodjo prince awu</strong></span>
                </div>
                <div class="legal-address">
                    <span>Siège social : <strong>[ADRESSE_SIEGE]</strong></span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .main-footer {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #fff;
        padding: 50px 0 20px;
        margin-top: 60px;
        position: relative;
        z-index: 10;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-main {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-column {
        display: flex;
        flex-direction: column;
    }

    .footer-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: #f4751a;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background: #f4751a;
        border-radius: 2px;
    }

    .footer-description {
        font-size: 0.95rem;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 20px;
    }

    /* Réseaux sociaux */
    .footer-social {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .social-link.facebook:hover {
        background: #1877f2;
    }

    .social-link.instagram:hover {
        background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .social-link.youtube:hover {
        background: #ff0000;
    }

    .social-link.tiktok:hover {
        background: #000000;
    }

    .social-link.whatsapp:hover {
        background: #25d366;
    }

    /* Liens */
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-links a:hover {
        color: #f4751a;
        transform: translateX(5px);
    }

    /* Contact */
    .footer-contact {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
    }

    .contact-item i {
        color: #f4751a;
        font-size: 1.1rem;
        width: 20px;
    }

    .contact-item a {
        color: rgba(255, 255, 255, 0.95);
        text-decoration: none;
        transition: color 0.3s ease;
        font-weight: 500;
    }

    .contact-item a:hover {
        color: #f4751a;
        text-decoration: underline;
    }
    
    .contact-item span {
        color: rgba(255, 255, 255, 0.95);
        font-weight: 500;
    }

    /* Section légale */
    .footer-legal {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 25px;
        text-align: center;
    }

    .legal-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .legal-info p {
        font-size: 1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .legal-details {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .legal-address {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-footer {
            padding: 40px 0 15px;
        }

        .footer-main {
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .footer-title {
            font-size: 1.1rem;
        }

        .legal-details {
            flex-direction: column;
            gap: 5px;
        }

        .legal-details span:nth-child(2),
        .legal-details span:nth-child(4) {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .footer-main {
            grid-template-columns: 1fr;
        }

        .footer-social {
            justify-content: center;
        }
    }
</style>
