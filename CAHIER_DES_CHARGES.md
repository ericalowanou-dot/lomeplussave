# CAHIER DES CHARGES - LOME+ MARKETPLACE

**Date de création :** 20 Février 2026  
**Version :** 1.0  
**Projet :** Plateforme Marketplace Locale pour le Togo

---

## 1. PRÉSENTATION DU PROJET

### 1.1 Contexte
Lome+ est une plateforme marketplace locale conçue pour faciliter la vente et l'achat de produits au Togo. La plateforme vise à créer un marché en ligne accessible, simple et efficace qui connecte les vendeurs et les acheteurs dans une zone géographique restreinte, permettant des transactions rapides et sécurisées.

### 1.2 Objectifs
- **Objectif principal :** Créer une marketplace locale permettant aux utilisateurs de vendre et d'acheter des produits facilement au Togo
- **Objectifs secondaires :**
  - Faciliter les transactions locales entre vendeurs et acheteurs
  - Offrir une interface simple et intuitive accessible à tous
  - Promouvoir le commerce local togolais
  - Assurer la modération et la sécurité des transactions
  - Générer des revenus via le système de boost et de certification

### 1.3 Portée du projet
Le projet couvre le développement d'une application web complète avec :
- Interface utilisateur responsive (desktop et mobile)
- Système d'authentification et de gestion des utilisateurs
- Gestion des annonces et produits
- Système de communication entre utilisateurs
- Panel d'administration
- Système de monétisation (coins, boost, certification)

---

## 2. L'ARBORESCENCE

### 2.1 Structure fonctionnelle

```
LOME+ MARKETPLACE
│
├── ESPACE PUBLIC
│   ├── Page d'accueil (liste des articles)
│   ├── Recherche d'articles
│   ├── Filtrage par catégories/sous-catégories
│   ├── Détails d'un article
│   ├── Page "À propos"
│   └── Boutique utilisateur (vue publique)
│
├── ESPACE UTILISATEUR AUTHENTIFIÉ
│   ├── Gestion du profil
│   │   ├── Modification du profil
│   │   ├── Photo de profil
│   │   └── Informations de contact (téléphone, WhatsApp)
│   │
│   ├── Gestion des annonces
│   │   ├── Création d'annonce
│   │   ├── Modification d'annonce
│   │   ├── Suppression d'annonce
│   │   ├── Transfert d'annonce
│   │   └── Liste de mes annonces
│   │
│   ├── Interactions sociales
│   │   ├── Favoris (likes)
│   │   ├── Commentaires sur articles
│   │   └── Signalement de contenu
│   │
│   ├── Communication
│   │   ├── Messagerie interne
│   │   ├── Boîte de réception
│   │   └── Envoi de messages
│   │
│   └── Monétisation
│       ├── Système de coins
│       ├── Boost d'articles
│       └── Certification de boutique
│
└── ESPACE ADMINISTRATION
    ├── Dashboard statistiques
    ├── Gestion des utilisateurs
    │   ├── Liste des utilisateurs
    │   ├── Blocage/déblocage
    │   ├── Attribution de coins
    │   └── Suppression d'utilisateurs
    │
    ├── Modération des articles
    │   ├── Validation des articles (approbation)
    │   ├── Blocage d'articles
    │   ├── Suppression d'articles
    │   └── Actions en lot
    │
    ├── Gestion des catégories
    │   ├── Création/modification/suppression de catégories
    │   └── Gestion des sous-catégories
    │
    ├── Gestion des signalements
    │   ├── Consultation des signalements
    │   └── Traitement des signalements
    │
    ├── Gestion des publicités
    │   ├── Création de publicités
    │   ├── Suivi des vues et clics
    │   └── Activation/désactivation
    │
    └── Messagerie administrative
        └── Envoi de messages aux utilisateurs
```

### 2.2 Structure technique

```
Application Laravel 11
│
├── Frontend
│   ├── Blade Templates (vues)
│   ├── TailwindCSS (styles)
│   ├── AlpineJS (interactivité)
│   └── Vite (build tool)
│
├── Backend
│   ├── Controllers (logique métier)
│   ├── Models (modèles de données)
│   ├── Middleware (authentification, modération)
│   ├── Services (optimisation d'images)
│   └── Events/Listeners (notifications)
│
├── Base de données
│   ├── Users (utilisateurs)
│   ├── Articles (annonces)
│   ├── Categories / SousCategories
│   ├── Comments (commentaires)
│   ├── Messages (messagerie)
│   ├── ProblemReports (signalements)
│   ├── Publicites (publicités)
│   └── AdminNotifications (notifications admin)
│
└── Services externes
    ├── Intervention Image (traitement d'images)
    └── Système de queue Laravel
```

---

## 3. PÉRIMÈTRES

### 3.1 UTILISATEURS

#### 3.1.1 Types d'utilisateurs

**A. Visiteurs non authentifiés**
- Consultation de la page d'accueil
- Recherche d'articles
- Consultation des détails d'articles
- Consultation des boutiques publiques
- Accès à la page "À propos"

**B. Utilisateurs authentifiés (Vendeurs/Acheteurs)**
- Toutes les fonctionnalités des visiteurs
- Création et gestion de compte
- Publication d'annonces
- Gestion de sa boutique
- Ajout d'articles en favoris
- Commentaires sur les articles
- Messagerie interne
- Signalement de contenu inapproprié
- Boost d'articles (avec coins)
- Certification de boutique (avec coins)
- Consultation de ses notifications

**C. Administrateurs**
- Toutes les fonctionnalités des utilisateurs authentifiés
- Accès au panel d'administration
- Modération des articles (approbation/blocage)
- Gestion des utilisateurs (blocage/déblocage)
- Gestion des catégories et sous-catégories
- Gestion des signalements
- Gestion des publicités
- Attribution de coins aux utilisateurs
- Consultation des statistiques
- Envoi de messages administratifs

#### 3.1.2 Profils utilisateurs

**Informations du profil :**
- Nom complet
- Email (unique, vérifié)
- Téléphone
- WhatsApp (optionnel)
- Photo de profil
- Ville
- Statut de certification
- Nombre de coins
- Date de certification (si certifié)
- Statut de blocage

**Informations des articles :**
- Titre
- Description
- Prix HT
- Lieu de vente
- Statut (neuf/occasion)
- Option de livraison
- Photos (jusqu'à 6 photos)
- Catégorie et sous-catégorie
- Statut de modération (pending/approved/blocked)
- Date de boost (si boosté)

### 3.2 FONCTIONNALITÉS

#### 3.2.1 Fonctionnalités publiques

**Page d'accueil :**
- Affichage des articles approuvés
- Articles boostés en priorité
- Pagination
- Filtrage par catégories
- Recherche par mots-clés

**Recherche et filtrage :**
- Recherche textuelle (titre, description)
- Filtrage par catégorie
- Filtrage par sous-catégorie
- Filtrage par prix (à venir)
- Filtrage par localisation (à venir)

**Détails d'article :**
- Affichage complet des informations
- Galerie de photos (6 max)
- Informations du vendeur
- Bouton de contact (WhatsApp)
- Liste des commentaires
- Articles similaires

**Boutique utilisateur :**
- Affichage des articles du vendeur
- Informations du vendeur
- Badge de certification (si certifié)

#### 3.2.2 Fonctionnalités utilisateur authentifié

**Gestion de compte :**
- Inscription avec email
- Connexion/Déconnexion
- Vérification d'email
- Réinitialisation de mot de passe
- Modification du profil
- Upload de photo de profil
- Gestion des informations de contact

**Gestion des annonces :**
- Création d'annonce avec formulaire multi-étapes
- Upload multiple de photos (jusqu'à 6)
- Modification d'annonce existante
- Suppression d'annonce
- Transfert d'annonce à un autre utilisateur
- Consultation de ses annonces
- Statut de modération visible

**Interactions sociales :**
- Ajout/retrait d'articles en favoris
- Consultation de ses favoris
- Commentaires sur les articles
- Modification/suppression de ses commentaires
- Signalement d'articles ou commentaires inappropriés

**Communication :**
- Messagerie interne entre utilisateurs
- Boîte de réception
- Envoi de messages
- Réponse aux messages
- Consultation de l'historique des conversations

**Monétisation :**
- Consultation du solde de coins
- Boost d'articles (augmentation de visibilité)
- Certification de boutique (crédibilité renforcée)
- Durée limitée pour boost et certification

#### 3.2.3 Fonctionnalités administration

**Dashboard :**
- Statistiques globales (utilisateurs, articles, coins)
- Articles en attente de modération
- Utilisateurs récents
- Notifications administratives

**Gestion des utilisateurs :**
- Liste de tous les utilisateurs
- Recherche d'utilisateurs
- Consultation du profil détaillé
- Blocage/déblocage d'utilisateurs
- Attribution de coins
- Suppression d'utilisateurs
- Consultation des articles d'un utilisateur

**Modération des articles :**
- Liste des articles (tous statuts)
- Filtrage par statut (pending/approved/blocked)
- Recherche d'articles
- Approbation d'articles
- Blocage d'articles (avec raison)
- Suppression d'articles
- Actions en lot (approbation/blocage multiple)
- Consultation des détails complets

**Gestion des catégories :**
- Création de catégories
- Modification de catégories
- Suppression de catégories
- Gestion des sous-catégories
- Association catégorie/sous-catégorie

**Gestion des signalements :**
- Consultation des signalements
- Filtrage par type (article/commentaire)
- Mise à jour du statut de traitement
- Actions correctives associées

**Gestion des publicités :**
- Création de publicités
- Upload d'images publicitaires
- Configuration des liens
- Activation/désactivation
- Suivi des vues et clics
- Statistiques de performance

**Messagerie administrative :**
- Envoi de messages aux utilisateurs
- Consultation des conversations
- Notifications aux utilisateurs

#### 3.2.4 Fonctionnalités système

**Sécurité :**
- Authentification sécurisée
- Protection CSRF
- Validation des données
- Sanitisation des entrées
- Protection contre les injections SQL
- Gestion des permissions (middleware)

**Performance :**
- Cache des statistiques
- Optimisation des requêtes (eager loading)
- Pagination des listes
- Optimisation des images (Intervention Image)
- Indexation de la base de données

**Notifications :**
- Notifications par email
- Notifications en temps réel (à venir)
- Notifications administratives
- Notifications de modération

---

## 4. PRESTATIONS DE SERVICES

### 4.1 Développement

**4.1.1 Backend (Laravel 11)**
- Architecture MVC
- API RESTful
- Gestion des routes et middleware
- Modèles Eloquent avec relations
- Contrôleurs pour chaque module
- Services pour logique métier complexe
- Events et Listeners pour notifications
- Commandes Artisan personnalisées
- Migrations de base de données
- Seeders pour données de test

**4.1.2 Frontend**
- Templates Blade responsives
- Intégration TailwindCSS
- Interactivité AlpineJS
- Formulaires de validation
- Upload d'images avec prévisualisation
- Pagination dynamique
- Recherche en temps réel
- Modales et notifications

**4.1.3 Base de données**
- Conception du schéma de base de données
- Relations entre tables
- Indexes pour performance
- Migrations versionnées
- Seeders pour données initiales

**4.1.4 Fonctionnalités spécifiques**
- Système d'authentification complet
- Gestion des rôles et permissions
- Upload et optimisation d'images
- Système de modération
- Système de coins et monétisation
- Messagerie interne
- Système de notifications
- Gestion des publicités

### 4.2 Configuration et déploiement

- Configuration de l'environnement de développement
- Configuration de l'environnement de production
- Configuration des services (email, queue)
- Optimisation des performances
- Configuration de la sécurité
- Documentation de déploiement

### 4.3 Maintenance et support

- Correction des bugs
- Mises à jour de sécurité
- Optimisations de performance
- Support technique
- Documentation utilisateur

---

## 5. TECHNOLOGIES ET CONTRAINTES

### 5.1 Technologies utilisées

**Backend :**
- **Framework :** Laravel 11.31
- **Langage :** PHP 8.2+
- **Base de données :** SQLite (développement) / MySQL/PostgreSQL (production)
- **ORM :** Eloquent
- **Authentification :** Laravel Breeze
- **Queue :** Laravel Queue System
- **Traitement d'images :** Intervention Image 3.11

**Frontend :**
- **Templates :** Blade (Laravel)
- **CSS Framework :** TailwindCSS 3.1
- **JavaScript :** AlpineJS 3.14
- **Build Tool :** Vite 6.1
- **HTTP Client :** Axios 1.7

**Outils de développement :**
- **Gestionnaire de dépendances PHP :** Composer
- **Gestionnaire de dépendances JS :** npm
- **Versioning :** Git
- **Linting :** Laravel Pint
- **Tests :** PHPUnit

### 5.2 Contraintes techniques

**Performance :**
- Temps de chargement des pages < 3 secondes
- Support de 1000+ utilisateurs simultanés
- Pagination pour les listes importantes
- Cache des requêtes fréquentes
- Optimisation des images (compression, redimensionnement)

**Sécurité :**
- Authentification sécurisée (hashage des mots de passe)
- Protection CSRF sur tous les formulaires
- Validation stricte des données utilisateur
- Protection contre les injections SQL (requêtes préparées)
- Sanitisation des entrées utilisateur
- Gestion des permissions (middleware)
- Blocage des utilisateurs malveillants

**Compatibilité :**
- Navigateurs : Chrome, Firefox, Safari, Edge (versions récentes)
- Responsive design (mobile, tablette, desktop)
- Résolution minimale : 320px (mobile)

**Stockage :**
- Upload d'images limité à 6 par article
- Formats acceptés : JPG, PNG, GIF
- Taille maximale par image : 5 MB
- Stockage local (public/articles, public/users/profil)

### 5.3 Contraintes fonctionnelles

**Modération :**
- Tous les articles doivent être approuvés avant publication
- Système de signalement pour contenu inapproprié
- Blocage possible des utilisateurs et articles

**Monétisation :**
- Système de coins géré par l'administrateur
- Boost d'articles avec durée limitée
- Certification de boutique avec durée limitée

**Communication :**
- Messagerie interne uniquement (pas d'email externe)
- Pas de système de paiement intégré (contact direct)

### 5.4 Contraintes légales et réglementaires

- Conformité RGPD (si applicable)
- Respect des données personnelles
- Conditions d'utilisation
- Mentions légales
- Politique de confidentialité

---

## 6. LIVRABLES

### 6.1 Code source

**Application complète :**
- Code source Laravel structuré
- Templates Blade
- Assets frontend (CSS, JS)
- Migrations de base de données
- Seeders
- Configuration des environnements

**Structure des fichiers :**
```
/app (Controllers, Models, Services, etc.)
/database (migrations, seeders)
/resources/views (templates Blade)
/public (assets publics, images)
/routes (routes web et API)
/config (configurations)
```

### 6.2 Base de données

- Schéma complet de la base de données
- Migrations versionnées
- Seeders pour données de test
- Documentation du schéma

### 6.3 Documentation

**Documentation technique :**
- Guide d'installation et de configuration
- Documentation de l'architecture
- Guide de déploiement
- Documentation de l'API (si applicable)

**Documentation utilisateur :**
- Guide utilisateur (vendeur/acheteur)
- Guide administrateur
- FAQ

**Documentation de maintenance :**
- Procédures de sauvegarde
- Procédures de mise à jour
- Guide de dépannage

### 6.4 Tests

- Tests unitaires (si développés)
- Tests d'intégration (si développés)
- Tests de régression
- Rapport de tests

### 6.5 Configuration

- Fichiers de configuration (.env.example)
- Scripts de déploiement (si applicable)
- Configuration serveur recommandée

### 6.6 Formation

- Formation administrateur (si prévue)
- Support initial post-livraison

---

## 7. ÉVOLUTIONS FUTURES

### 7.1 Court terme (3-6 mois)

**Améliorations utilisateur :**
- Application mobile (iOS/Android)
- Notifications push
- Système de notation et avis
- Filtrage avancé (prix, localisation)
- Recherche par image
- Partage sur réseaux sociaux

**Améliorations fonctionnelles :**
- Système de paiement en ligne intégré
- Gestion des commandes
- Suivi des livraisons
- Système de facturation
- Historique des transactions

**Améliorations techniques :**
- API REST complète
- Intégration avec services de paiement
- Intégration avec services de livraison
- Système de cache avancé (Redis)
- CDN pour les images

### 7.2 Moyen terme (6-12 mois)

**Nouvelles fonctionnalités :**
- Chat en temps réel
- Vidéos de présentation de produits
- Système de recommandations
- Programme de fidélité
- Marketplace multi-vendeurs avec commissions
- Intégration avec réseaux sociaux
- Système de coupons et promotions

**Améliorations business :**
- Tableau de bord vendeur avancé
- Statistiques de vente
- Rapports analytiques
- Export de données
- Intégration avec outils comptables

### 7.3 Long terme (12+ mois)

**Expansion :**
- Extension à d'autres pays d'Afrique de l'Ouest
- Multi-langues (français, anglais, langues locales)
- Multi-devises
- Intégration avec marketplaces internationales

**Innovation :**
- Intelligence artificielle pour recommandations
- Chatbot d'assistance
- Analyse prédictive des ventes
- Système de crédit intégré
- Marketplace B2B

**Technologies émergentes :**
- Paiement mobile (Mobile Money)
- Blockchain pour traçabilité
- Réalité augmentée pour visualisation produits

---

## 8. PLANNING

### 8.1 Phase 1 : Analyse et conception (2 semaines)
- Analyse des besoins détaillée
- Conception de l'architecture
- Conception de la base de données
- Maquettage des interfaces
- Validation avec le client

### 8.2 Phase 2 : Développement backend (4-6 semaines)
- Configuration de l'environnement
- Développement des modèles et migrations
- Développement des contrôleurs
- Développement des services
- Système d'authentification
- API de base

### 8.3 Phase 3 : Développement frontend (4-6 semaines)
- Intégration des templates
- Développement des interfaces utilisateur
- Intégration des formulaires
- Système de recherche et filtrage
- Responsive design

### 8.4 Phase 4 : Fonctionnalités avancées (3-4 semaines)
- Système de modération
- Système de coins et monétisation
- Messagerie interne
- Système de notifications
- Gestion des publicités
- Panel d'administration

### 8.5 Phase 5 : Tests et corrections (2-3 semaines)
- Tests fonctionnels
- Tests de performance
- Tests de sécurité
- Corrections des bugs
- Optimisations

### 8.6 Phase 6 : Déploiement et formation (1-2 semaines)
- Configuration de l'environnement de production
- Déploiement
- Tests en production
- Formation des administrateurs
- Documentation finale

**Durée totale estimée :** 16-22 semaines (4-5.5 mois)

### 8.7 Jalons principaux

- **Jalon 1 :** Fin de la conception (Semaine 2)
- **Jalon 2 :** Backend fonctionnel (Semaine 8)
- **Jalon 3 :** Frontend fonctionnel (Semaine 14)
- **Jalon 4 :** Application complète (Semaine 18)
- **Jalon 5 :** Mise en production (Semaine 20)

---

## 9. BUDGET

### 9.1 Estimation des coûts

**Développement :**
- Analyse et conception : [À définir]
- Développement backend : [À définir]
- Développement frontend : [À définir]
- Fonctionnalités avancées : [À définir]
- Tests et corrections : [À définir]
- Déploiement : [À définir]

**Infrastructure :**
- Hébergement web : [À définir] / mois
- Base de données : [À définir] / mois
- Stockage (images) : [À définir] / mois
- Domaine : [À définir] / an
- SSL/HTTPS : [À définir] / an

**Services externes :**
- Service d'email (SMTP) : [À définir] / mois
- CDN (optionnel) : [À définir] / mois
- Monitoring (optionnel) : [À définir] / mois

**Maintenance :**
- Maintenance mensuelle : [À définir] / mois
- Support technique : [À définir] / mois
- Mises à jour de sécurité : [À définir] / an

**Formation et documentation :**
- Formation administrateur : [À définir]
- Documentation : [À définir]

### 9.2 Modalités de paiement

- [À définir selon accord client/prestataire]

### 9.3 Révisions budgétaires

- Les révisions budgétaires doivent être validées par écrit
- Les modifications de périmètre peuvent entraîner des ajustements budgétaires

---

## 10. CRITÈRES DE VALIDATION

### 10.1 Critères fonctionnels

**Authentification :**
- ✅ Inscription fonctionnelle avec validation email
- ✅ Connexion/déconnexion opérationnelle
- ✅ Réinitialisation de mot de passe fonctionnelle
- ✅ Gestion du profil utilisateur complète

**Gestion des articles :**
- ✅ Création d'annonce avec toutes les informations requises
- ✅ Upload de 6 photos maximum par article
- ✅ Modification et suppression d'articles fonctionnelles
- ✅ Affichage correct des articles sur la page d'accueil
- ✅ Recherche et filtrage opérationnels
- ✅ Détails d'article complets

**Interactions sociales :**
- ✅ Système de favoris fonctionnel
- ✅ Commentaires opérationnels
- ✅ Signalement de contenu fonctionnel

**Communication :**
- ✅ Messagerie interne opérationnelle
- ✅ Envoi et réception de messages fonctionnels

**Monétisation :**
- ✅ Système de coins opérationnel
- ✅ Boost d'articles fonctionnel
- ✅ Certification de boutique fonctionnelle

**Administration :**
- ✅ Panel d'administration accessible
- ✅ Modération des articles opérationnelle
- ✅ Gestion des utilisateurs fonctionnelle
- ✅ Gestion des catégories opérationnelle
- ✅ Gestion des signalements fonctionnelle
- ✅ Gestion des publicités opérationnelle

### 10.2 Critères techniques

**Performance :**
- ✅ Temps de chargement des pages < 3 secondes
- ✅ Pagination fonctionnelle pour les grandes listes
- ✅ Images optimisées (taille et format)
- ✅ Cache des requêtes fréquentes opérationnel

**Sécurité :**
- ✅ Authentification sécurisée
- ✅ Protection CSRF active
- ✅ Validation des données utilisateur
- ✅ Protection contre les injections SQL
- ✅ Gestion des permissions correcte
- ✅ Sanitisation des entrées utilisateur

**Compatibilité :**
- ✅ Responsive design (mobile, tablette, desktop)
- ✅ Compatibilité navigateurs récents
- ✅ Affichage correct sur différentes résolutions

**Base de données :**
- ✅ Schéma de base de données complet
- ✅ Relations entre tables correctes
- ✅ Indexes pour performance
- ✅ Migrations versionnées

### 10.3 Critères de qualité

**Code :**
- ✅ Code structuré et commenté
- ✅ Respect des standards Laravel
- ✅ Pas d'erreurs critiques
- ✅ Gestion des erreurs appropriée

**Interface utilisateur :**
- ✅ Interface intuitive et ergonomique
- ✅ Design cohérent et professionnel
- ✅ Messages d'erreur clairs
- ✅ Feedback utilisateur approprié

**Documentation :**
- ✅ Documentation technique complète
- ✅ Documentation utilisateur disponible
- ✅ Guide d'installation fourni
- ✅ Guide de déploiement fourni

### 10.4 Tests de validation

**Tests fonctionnels :**
- ✅ Tous les parcours utilisateur testés
- ✅ Tous les formulaires validés
- ✅ Toutes les fonctionnalités testées
- ✅ Cas limites testés

**Tests de performance :**
- ✅ Charge test (100+ utilisateurs simultanés)
- ✅ Temps de réponse acceptable
- ✅ Pas de fuites mémoire

**Tests de sécurité :**
- ✅ Tests de pénétration basiques
- ✅ Validation des entrées utilisateur
- ✅ Protection contre les attaques courantes

**Tests de compatibilité :**
- ✅ Tests sur différents navigateurs
- ✅ Tests sur différents appareils
- ✅ Tests sur différentes résolutions

### 10.5 Critères d'acceptation

**Acceptation fonctionnelle :**
- Toutes les fonctionnalités du périmètre sont opérationnelles
- Aucun bug bloquant identifié
- Performance conforme aux spécifications
- Sécurité validée

**Acceptation technique :**
- Code source livré et documenté
- Base de données déployée
- Application déployée en production
- Documentation complète fournie

**Acceptation utilisateur :**
- Formation administrateur effectuée
- Support initial fourni
- Retours utilisateurs positifs

### 10.6 Procédure de validation

1. **Tests internes :** L'équipe de développement effectue des tests complets
2. **Tests de recette :** Le client effectue des tests de validation
3. **Corrections :** Les bugs identifiés sont corrigés
4. **Validation finale :** Signature de la validation par le client
5. **Mise en production :** Déploiement final après validation

---

## ANNEXES

### A. Glossaire

- **Article/Annonce :** Produit mis en vente sur la plateforme
- **Boost :** Augmentation temporaire de la visibilité d'un article
- **Certification :** Statut accordé aux boutiques vérifiées
- **Coins :** Monnaie virtuelle de la plateforme
- **Modération :** Processus de validation des contenus par l'administrateur
- **Signalement :** Action de signaler un contenu inapproprié

### B. Références

- Documentation Laravel : https://laravel.com/docs
- Documentation TailwindCSS : https://tailwindcss.com/docs
- Documentation AlpineJS : https://alpinejs.dev

### C. Contacts

- **Client :** [À compléter]
- **Prestataire :** [À compléter]
- **Chef de projet :** [À compléter]

---

**Document approuvé le :** [Date]  
**Signatures :**

Client : _________________  
Prestataire : _________________
