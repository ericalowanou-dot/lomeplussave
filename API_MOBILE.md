# API de l'appli mobile Lome+ — v1

L'appli et le site partagent **la même base, les mêmes règles et le même backoffice**.
Une annonce publiée depuis l'appli arrive dans la file de modération de l'admin du site,
un message envoyé depuis l'appli arrive dans la messagerie admin, etc.

- Base : `https://<domaine>/api/v1`
- Toujours envoyer `Accept: application/json`
- Réponses en JSON, textes en UTF-8, dates au format ISO 8601
- Listes paginées : `?page=2&per_page=20` (50 maximum), avec `meta.total` et `links.next`

## Connexion

| Méthode | Route | Corps | Réponse |
|---|---|---|---|
| POST | `/auth/register` | `name, email, password (6+), telephone, whatsapp, device_name?` | 201 `{token, user}` |
| POST | `/auth/login` | `email, password, device_name?` | `{token, user}` |
| POST | `/auth/forgot-password` | `email` | même email que le site |
| POST | `/auth/logout` | — | supprime le jeton de cet appareil |
| POST | `/auth/logout-all` | — | déconnecte tous les appareils |

Ensuite, envoyer `Authorization: Bearer <token>` à chaque requête.
Limites : 10 tentatives de connexion par minute et 120 requêtes par minute (réponse 429 au-delà).

## Annonces

| Méthode | Route | Notes |
|---|---|---|
| GET | `/articles` | filtres du site : `q, categorie, sous_categorie, prix_min, prix_max, ville, etat (neuf/occasion), pro_only, livraison_only, order_by (recent/pro/prix_asc/prix_desc)` |
| GET | `/articles/search?q=` | recherche large : titre, description, lieu, vendeur, catégories (2 caractères minimum) |
| GET | `/articles/{id}` | détail complet (photos, description, vendeur, téléphone, lien WhatsApp) |
| POST | `/articles` 🔒 | **multipart** : `categorie, sous_categorie_id, titre, prix_ht, lieu, description (20-1500), etat, livraison?, photos[]` (1 à 6 images, 30 Mo maximum, pas de SVG). L'annonce part en validation (`status: pending`). |
| POST | `/articles/{id}` 🔒 | modification en **multipart** (PHP ne lit pas les fichiers d'un PUT). Une annonce validée repasse en validation (`en_validation: true`). |
| PUT/PATCH | `/articles/{id}` 🔒 | modification sans photo |
| DELETE | `/articles/{id}` 🔒 | suppression (photos effacées) |
| POST | `/articles/{id}/like` 🔒 | bascule like/unlike → `{liked, likeCount}` |
| POST | `/articles/{id}/boost` 🔒 | `days` : 1 coin = 1 jour |

🔒 = jeton obligatoire. Les routes publiques acceptent aussi le jeton : `liked` est alors renseigné,
et le vendeur voit ses propres annonces non validées.

Une annonce en attente ou bloquée renvoie **404** à tout autre utilisateur que son vendeur et l'admin.

## Compte 🔒

| Méthode | Route | Notes |
|---|---|---|
| GET | `/me` | profil, coins, certification, `messages_non_lus` |
| POST/PATCH | `/me` | `name, telephone, whatsapp, photo` (multipart pour la photo). L'email, les coins et le rôle ne sont pas modifiables. |
| DELETE | `/me` | `password` : suppression du compte (obligatoire sur l'App Store et Google Play) |
| GET | `/me/articles?status=` | mes annonces, tous statuts, avec `stats` et `block_reason` |
| GET | `/me/favorites` | annonces likées |
| POST | `/me/certification` | `days` : 1 coin = 1 jour |

## Commentaires, messages, boutiques

| Méthode | Route | Notes |
|---|---|---|
| GET | `/articles/{id}/comments` | |
| POST | `/articles/{id}/comments` 🔒 | `content` (3-1000) |
| PUT/PATCH, DELETE | `/comments/{id}` 🔒 | auteur seulement (l'admin peut supprimer) |
| POST | `/comments/{id}/report` 🔒 | `reason?` — 409 si déjà signalé |
| GET | `/messages` 🔒 | conversation avec l'équipe Lome+ (envoyés et reçus) |
| GET | `/messages/{id}` 🔒 | marque le message comme lu |
| POST | `/messages` 🔒 | `body, parent_message_id?` → envoyé à l'admin |
| GET | `/categories` | avec sous-catégories |
| GET | `/shops/{userId}` | boutique : `vendeur` + annonces en ligne + `motifs_signalement` |
| POST | `/shops/{userId}/report` 🔒 | `reason` (une clé de `motifs_signalement`), `message?` |
| POST | `/reports` | signaler un problème : `message (10+), subject?` |

## Erreurs

| Code | Sens |
|---|---|
| 401 | jeton absent ou invalide → renvoyer vers l'écran de connexion |
| 403 | action interdite ; `code: account_blocked` = compte bloqué (jetons révoqués) |
| 404 | introuvable (ou annonce non validée d'un autre vendeur) |
| 409 | déjà fait (`code: already_reported`) |
| 422 | validation : `{message, errors: {champ: [messages]}}` ; `code: insufficient_coins` pour les coins |
| 429 | trop de requêtes, réessayer plus tard |

## Règle d'évolution

Ne **jamais** casser une route `v1` publiée : les anciennes versions de l'appli restent installées
sur les téléphones. Ajouter des champs est sans risque ; pour changer ou retirer quelque chose,
créer `/api/v2`.

## Mise en production

1. `composer install --no-dev` (nouveau paquet : `laravel/sanctum`)
2. `php artisan migrate --force` : crée la table `personal_access_tokens` et l'index unique des likes
3. `php artisan config:cache && php artisan route:cache`
4. Facultatif : `SANCTUM_TOKEN_EXPIRATION` (en minutes) pour limiter la durée des jetons

## Photos : passer sur un stockage cloud plus tard

Toutes les photos passent par le disque `uploads` (`config/filesystems.php`), aujourd'hui `public/`.
Les chemins en base (`articles/xxx.jpg`) ne changent pas. Pour migrer :

1. `composer require league/flysystem-aws-s3-v3` (S3, Cloudflare R2, Bunny… compatibles S3)
2. Copier les dossiers `articles`, `users/profil`, `categories/images`, `souscategories/images`,
   `media/spotlight` vers le bucket, en gardant la même arborescence
3. Dans `.env` : `MEDIA_DISK=s3` et les variables `AWS_*` (dont `AWS_URL` = l'adresse du CDN)

Aucune ligne de code à modifier : un test (`MediaStorageTest`) simule déjà ce basculement.
