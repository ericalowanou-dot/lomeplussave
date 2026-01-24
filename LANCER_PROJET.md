# Guide pour lancer le projet Laravel en local sur Linux

## Prérequis

- PHP 8.2+ (vous avez PHP 8.3.6 ✓)
- Composer installé
- Node.js et npm installés (pour Vite et les assets frontend)
- SQLite (déjà configuré dans votre projet)

## Étapes pour lancer le projet

### 1. Vérifier la configuration

Assurez-vous que le fichier `.env` existe et est correctement configuré :

```bash
# Si .env n'existe pas, copiez .env.example
cp .env.example .env

# Générez la clé d'application si nécessaire
php artisan key:generate
```

### 2. Installer les dépendances (si pas déjà fait)

```bash
# Dépendances PHP (Composer)
composer install

# Dépendances JavaScript (npm)
npm install
```

### 3. Préparer la base de données

```bash
# Créer la base de données SQLite si elle n'existe pas
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate

# (Optionnel) Charger des données de test
php artisan db:seed
```

### 4. Lancer le projet

Vous avez **deux options** :

#### Option A : Lancer tout en une seule commande (recommandé)

Cette commande lance simultanément :
- Le serveur Laravel (port 8000)
- La queue Laravel
- Les logs (Pail)
- Vite pour les assets frontend (port 5173)

```bash
composer run dev
```

#### Option B : Lancer séparément (dans des terminaux différents)

**Terminal 1 - Serveur Laravel :**
```bash
php artisan serve
```
Le site sera accessible sur : http://127.0.0.1:8000

**Terminal 2 - Vite (assets frontend) :**
```bash
npm run dev
```
ou
```bash
npm run dev:watch
```

**Terminal 3 - Queue Laravel (si vous utilisez des jobs) :**
```bash
php artisan queue:work
```

### 5. Accéder au projet

- **Application principale** : http://127.0.0.1:8000
- **Vite HMR** : http://127.0.0.1:5173 (pour le hot reload des assets)

## Commandes utiles

### Vider le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Voir les routes
```bash
php artisan route:list
```

### Créer un utilisateur admin (si vous avez un seeder)
```bash
php artisan db:seed --class=AdminUserSeeder
```

## Dépannage

### Erreur "Class not found"
```bash
composer dump-autoload
```

### Erreur de permissions sur storage/
```bash
chmod -R 775 storage bootstrap/cache
```

### Erreur Vite "Connection refused"
Vérifiez que le port 5173 n'est pas déjà utilisé :
```bash
lsof -i :5173
```

### Reconstruire les assets pour la production
```bash
npm run build
```

## Notes

- Le projet utilise SQLite par défaut (fichier `database/database.sqlite`)
- Les logs sont dans `storage/logs/laravel.log`
- Le mode debug est activé en local (APP_DEBUG=true dans .env)
