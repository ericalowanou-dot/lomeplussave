# Correction de l'erreur "Class DOMDocument not found"

## Problème

L'erreur `Class "DOMDocument" not found` indique que l'extension PHP XML/DOM n'est pas installée.

## Solution

### Sur Ubuntu/Debian

Installez l'extension PHP XML :

```bash
sudo apt update
sudo apt install php8.3-xml
```

Ou si vous utilisez une autre version de PHP, remplacez `8.3` par votre version :
- PHP 8.2 : `sudo apt install php8.2-xml`
- PHP 8.1 : `sudo apt install php8.1-xml`

### Vérifier l'installation

Après l'installation, vérifiez que l'extension est bien chargée :

```bash
php -m | grep -i dom
```

Vous devriez voir `dom` dans la liste.

### Alternative : Installation de toutes les extensions XML

Si vous préférez installer toutes les extensions XML en une fois :

```bash
sudo apt install php-xml
```

## Après l'installation

Redémarrez le serveur PHP si nécessaire, puis testez :

```bash
php artisan serve
```

L'erreur devrait être résolue !

## Note

Cette extension est nécessaire pour que Laravel Pail (outil de logs) fonctionne correctement, ainsi que pour d'autres fonctionnalités de Laravel qui utilisent le DOM.
