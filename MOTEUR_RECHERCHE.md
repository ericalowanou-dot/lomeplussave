# Moteur de recherche

Tout tourne dans Laravel et MariaDB, sans service externe. Les réglages sont dans `config/search.php`.

## Mise en service

`SEARCH_ENGINE` dans `.env` : `legacy` (ancien moteur, par défaut) ou `cascade` (nouveau moteur).

Le déploiement seul ne change pas les résultats. Pour activer le moteur :

1. `php artisan migrate --force`
2. `php artisan search:rebuild`
3. `php artisan search:compare` : compare les deux moteurs sur les vraies recherches, sans rien modifier
4. `SEARCH_ENGINE=cascade` dans `.env`, puis `php artisan config:clear`

Retour arrière : remettre `legacy`, puis `php artisan config:clear`.

Le recalcul de nuit a besoin d'un cron `schedule:run` qui vise bien ce projet. Sans lui, la recherche fonctionne,
mais les prix médians ne sont calculés qu'au dernier `search:rebuild` et les poids d'une annonce supprimée restent à recaler.

## La cascade

Les annonces d'un niveau passent toujours devant celles des niveaux suivants.

| Niveau | Ce qu'il trouve | Exemple |
|---|---|---|
| 1 | L'expression exacte | « iphone 13 » |
| 2 | Tous les mots utiles, dans n'importe quel ordre | « 13 iphone » |
| 3 | Au moins 70 % du poids des mots | Un nombre seul ne suffit jamais |
| 4 | Pluriels, racines, mots collés ou coupés | « iphone13 », « i phone », « chaussureslanvin » |
| 5 | Début de mot, puis début de mot avec une faute | « batterie li », « ell » → électronique |
| 6 | Fautes et prononciation | « telefone », « ordinatuer », « refligilateur » |
| 7 | Phrases : mots inconnus ignorés, mots les moins importants retirés un à un | « je cherche un frigo pas cher à Lomé » |
| 8 | Filet : tout ce que trouvait l'ancien moteur, y compris en milieu de mot | « ell » dans « belle » |
| 9 | Secours : catégorie la plus proche, puis annonces populaires et récentes | Rien d'autre n'a marché |

Règles à retenir :

- La cascade s'arrête quand les niveaux 1 à 7 atteignent `threshold` annonces. Le filet s'ajoute toujours à la fin.
- Une correction s'ajoute au mot tapé, elle ne le remplace jamais.
- La ville et le prix écrits dans la recherche font monter une annonce dans son niveau, sans exclure les autres.
- Les filtres choisis par l'utilisateur restent stricts, et le tri par prix remplace l'ordre de la cascade.
- Une recherche servie par le secours reste comptée sans résultat (`resultats = 0`, `niveau = 9`).

## L'index

- `search_documents` : une ligne par annonce approuvée, avec ses textes normalisés.
- `search_terms` : le vocabulaire, déduit des annonces, avec racine, clé phonétique et nombre d'annonces.
- `search_postings` : quel mot se trouve dans quelle annonce et dans quel champ.
- `search_category_stats` : prix médian par sous-catégorie.

### Annonces futures

L'index se met à jour à l'enregistrement, sans file d'attente : approbation, blocage, modification, boost, suppression,
transfert, et changement de nom d'un vendeur ou d'une catégorie. Une annonce entre dans l'index quand elle est approuvée.
Un nouveau mot, une nouvelle ville ou une nouvelle catégorie sont reconnus sans toucher au code.

Une erreur d'indexation est journalisée et ne bloque jamais la publication. `search:refresh` la rattrape chaque nuit à 3 h :
il remet l'index d'accord avec les annonces, puis recalcule les poids et les prix médians.
`search:rebuild` reconstruit tout à la demande.

## Régler le moteur

Dans `config/search.php`, puis `php artisan config:clear` :

- `threshold` : nombre d'annonces à partir duquel la cascade s'arrête.
- `min_weight_share` : exigence du niveau 3.
- `field_weights` : importance d'un mot selon le champ.
- `ranking` : pertinence, boost, certification, fraîcheur, photos, ville et prix, à l'intérieur d'un même niveau.
- `fuzzy_prefix_min_df` : nombre d'annonces qu'un mot doit avoir pour être proposé sur un début de mot avec faute.
- `frequent_word_ratio` : part du catalogue à partir de laquelle un mot ne sert plus à décider d'un niveau.

## Performances mesurées

Mesures du 2026-11-07 sur un PC de développement, avec MariaDB 10.4 et des annonces simulées, sans cache sauf mention contraire :

| Catalogue | Recherche médiane | Pire cas | En cache | Suggestions |
|---|---|---|---|---|
| 785 annonces | 30 ms | 138 ms | 13 ms | 6 à 38 ms |
| 78 500 annonces | 708 ms | 3,9 s (mot présent dans la moitié du catalogue) | non mesuré | 190 ms |

À 78 500 annonces, le coût principal est le filet, qui parcourt tous les textes. Si le catalogue atteint cette taille,
c'est la première chose à optimiser. `search:rebuild` prend environ 25 ms par annonce.

## Limites connues

- `%` et `_` sont cherchés comme du texte normal : taper `%` ne renvoie plus toutes les annonces.

- Un produit absent du catalogue passe par le secours : aucun moteur ne peut le trouver.
- L'argot et les noms déformés (« godas », « googpixel ») ne sont pas reconnus sans dictionnaire écrit à la main.

## Tester

`php vendor/bin/phpunit` teste le moteur par défaut. `tests/Feature/SearchEngineTest.php` et
`tests/Feature/SearchUnderstandingTest.php` forcent le mode `cascade`.
