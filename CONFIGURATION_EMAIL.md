# Configuration Email pour Lome+

## Configuration à ajouter dans le fichier `.env` sur le serveur

Ajoutez ou modifiez ces lignes dans le fichier `htdocs/laravel-app/.env` :

```env
# Configuration Email - LWS Panel
MAIL_MAILER=smtp
MAIL_HOST=mail.lomeplus.com
MAIL_PORT=587
MAIL_USERNAME=since@lomeplus.com
MAIL_PASSWORD=votre-mot-de-passe-du-compte-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="since@lomeplus.com"
MAIL_FROM_NAME="Lome+"
```

## Paramètres LWS Panel (selon votre panneau)

### Configuration SMTP sécurisée (recommandé)

**Option 1 - Port 587 avec TLS (recommandé) :**
```env
MAIL_HOST=mail.lomeplus.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Option 2 - Port 465 avec SSL :**
```env
MAIL_HOST=mail.lomeplus.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

**Serveurs alternatifs LWS Panel :**
- `mail.lomeplus.com` (principal)
- `mail23.lwspanel.com` (alternatif)

**Authentification :**
- Username: `since@lomeplus.com`
- Password: Le mot de passe du compte de messagerie `since@lomeplus.com`
- Authentification requise: Oui (obligatoire)

## Informations importantes

- **Serveur SMTP principal :** `mail.lomeplus.com`
- **Serveur SMTP alternatif :** `mail23.lwspanel.com`
- **Ports disponibles :** 
  - 587 avec TLS (recommandé pour Laravel)
  - 465 avec SSL
- **Authentification :** Obligatoire
- **Compte email :** `since@lomeplus.com`

### Si vous utilisez un service externe (Gmail, Outlook, etc.)

**Pour Gmail (non recommandé pour production) :**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=since@lomeplus.com
MAIL_PASSWORD=mot-de-passe-app-gmail
MAIL_ENCRYPTION=tls
```

**Pour un serveur mail personnalisé :**
```env
MAIL_HOST=votre-serveur-smtp.com
MAIL_PORT=587
MAIL_USERNAME=since@lomeplus.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
```

## Vérification après configuration

1. Après avoir modifié le `.env`, vider le cache :
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Tester l'envoi d'email via la page de récupération de mot de passe (`/forgot-password`)

3. Vérifier les logs si problème : `storage/logs/laravel.log`

## Note importante

Le mot de passe doit être rempli dans le fichier `.env` sur le serveur. Ne commitez jamais le fichier `.env` dans Git !
