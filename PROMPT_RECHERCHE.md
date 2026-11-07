Tu es un ingénieur senior spécialisé en moteurs de recherche. Tu dois améliorer le moteur de recherche de Lome+ (lomeplus.com), une marketplace togolaise de petites annonces (Lomé, Sokodé, Kara, Kpalimé…), utilisée surtout sur mobile, en français.

## Situation actuelle

Le projet est en Laravel 11 / PHP 8.2 avec MariaDB. La recherche repose sur des requêtes SQL `LIKE '%texte%'` qui cherchent l'expression entière, sans tri par pertinence ni index de texte :

- `ArticleSearch::browse()` : accueil et liste de l'API mobile. Cherche dans le titre, la description et le lieu, avec les filtres (catégorie, prix, ville, état, pro, livraison, tri).
- `ArticleSearch::search()` : page `/search` et recherche de l'API mobile. Cherche aussi dans le nom et la ville du vendeur, la sous-catégorie et la catégorie.
- `AnnonceController::searchArticles()` : route `/search/articles`, avec sa propre copie de la requête.

Les recherches sont déjà enregistrées dans `stat_recherches` avec leur nombre de résultats, et les statistiques admin affichent les recherches sans résultat. Vérifie dans ces données la part de recherches sans résultat, sur laquelle se base ce projet (annoncée à plus de la moitié).

Les requêtes sont souvent courtes, tronquées, avec des fautes (exemple : "ell" tapé pour "electronique"), ou écrites sous forme de phrases entières ("je cherche un téléphone pas cher à Lomé").

## Objectif

Faire le maximum pour que chaque recherche renvoie des résultats utiles, avec un moteur qui tourne entièrement dans notre programme : règles et algorithmes uniquement, aucune IA payante, aucun appel à une API externe, aucun service supplémentaire à faire tourner sur le serveur (pas de Meilisearch, Typesense ou Elasticsearch).

## Périmètre

À traiter :
- la recherche texte de l'accueil, de `/search`, de `/search/articles` et de l'API mobile, avec un seul moteur commun ;
- les trois requêtes actuelles remplacées par ce moteur, derrière l'interrupteur de configuration.

À conserver :
- une liste sans texte recherché garde exactement son comportement actuel (boostées d'abord, puis les plus récentes) ;
- les filtres choisis par l'utilisateur restent stricts et s'appliquent à tous les niveaux de la cascade ;
- un tri choisi par l'utilisateur (prix croissant ou décroissant) s'applique à l'ensemble des résultats à la place de l'ordre par niveau ;
- seules les annonces au statut `approved` sont cherchables ;
- l'API mobile reste compatible : uniquement des champs ajoutés, aucun champ retiré ou modifié, pour ne pas casser l'appli déjà installée.

Hors périmètre : `/search/favoris`, `/search/mesannonces` et les recherches de l'admin.

## Annonces présentes ET futures

Toutes les règles de ce document s'appliquent à TOUTES les annonces : celles qui existent aujourd'hui ET toutes celles qui seront publiées à l'avenir, sans aucune exception et sans intervention manuelle. Chaque nouvelle annonce est soumise exactement aux mêmes règles que les annonces existantes, dès qu'elle devient visible sur le site.

## Contraintes

- Pas de dictionnaire de synonymes ni de liens mots→catégories écrits à la main. Tout le vocabulaire est déduit automatiquement des annonces, présentes et futures (titres, catégories, descriptions, villes). La seule liste écrite à la main autorisée est la base française de mots vides et de verbes d'intention.
- Ne crée pas de nouveau journal ni de nouveau tableau de bord. Réutilise `stat_recherches` et les statistiques existantes.
- Rapide, même avec plusieurs milliers puis dizaines de milliers d'annonces, et léger pour des utilisateurs mobiles avec connexion limitée.
- Réutilise la stack et les conventions déjà présentes dans le projet. Il n'y a pas de champ "marque" : les marques sont déduites des titres et des descriptions, sans nouvelle saisie pour le vendeur.

## Définitions

- **Champs cherchés** : titre, sous-catégorie, catégorie, lieu, nom du vendeur, ville du vendeur, description.
- **Mot exact** : mot entier identique à celui de la requête, sans tenir compte des majuscules ni des accents.
- **Mot vide** : mot de la base française standard, verbe d'intention ("je cherche", "je veux", "vends", "besoin"), ou mot présent dans une très grande part des annonces (détecté automatiquement).
- **Mot utile** : tout mot qui n'est pas un mot vide. Les nombres et modèles ("11", "ps5", "8 go") sont toujours des mots utiles.
- **Poids d'un mot** : sa rareté dans le catalogue (type TF-IDF), calculée automatiquement.

## RÈGLE N°1 (prioritaire sur tout le reste) : les vraies annonces d'abord, puis on élargit progressivement

Le moteur ne doit jamais ignorer, exclure ou reléguer une annonce qui contient réellement les mots écrits par la personne, sous prétexte que ses propres règles (corrections, mots-clés extraits, mots vides, ville, prix, classement) ont interprété la recherche autrement. Les nouvelles règles servent à AJOUTER des résultats, jamais à en retirer.

Le moteur fonctionne en cascade, du plus fidèle à la requête jusqu'au plus souple :

- **Niveau 1** : l'expression exacte (mêmes mots entiers, même ordre) dans un champ cherché.
- **Niveau 2** : tous les mots utiles exacts, dans n'importe quel ordre.
- **Niveau 3** : une majorité du **poids** des mots utiles exacts (par exemple 70 %, réglable). Ce niveau se calcule sur le poids, pas sur le nombre de mots. Les mots vides ne permettent jamais à eux seuls d'atteindre un niveau.
- **Niveau 4** : après normalisation (ponctuation, tirets, apostrophes, pluriels, racines) et découpage des mots collés ou séparés : lettres et chiffres ("iphone13" → "iphone 13"), mots du vocabulaire collés ("chaussureslanvin" → "chaussures lanvin"), mots coupés ("i phone" → "iphone").
- **Niveau 5** : début de mot ("elec" → "electronique"), en particulier sur le dernier mot de la requête, en cours de frappe ("batterie li" → "batterie lithium", "canne a pech" → "canne à pêche"), puis début de mot avec une faute à partir de 3 lettres ("ell" → "ele…" → "electronique"). Cette seconde forme est limitée aux catégories et aux mots fréquents du vocabulaire, pour éviter le bruit.
- **Niveau 6** : tolérance aux fautes (distance d'édition, erreurs phonétiques).
- **Niveau 7** : compréhension de phrase (mots-clés extraits, mots vides retirés, ville, prix, mots inconnus ignorés), avec retrait progressif des mots les moins importants.
- **Niveau 8 — filet de l'ancien moteur** : tout ce que l'ancien moteur aurait renvoyé et qui n'est pas encore dans la liste, y compris les correspondances en milieu de mot ("ell" dans "belle"). Ce niveau est toujours ajouté à la fin, quel que soit le nombre de résultats des niveaux précédents.
- **Niveau 9 — secours** : uniquement si les niveaux 1 à 8 ne donnent rien. "Vous cherchiez peut-être…" : catégorie la plus proche, puis annonces populaires ou récentes. La page n'est jamais vide.

Règles de fonctionnement de la cascade :

- Les résultats d'un niveau passent toujours devant ceux des niveaux suivants. Ni un boost, ni la fraîcheur, ni la ville ne peuvent faire passer une annonce moins fidèle devant une correspondance plus stricte.
- Le moteur passe au niveau suivant seulement si le cumul des niveaux 1 à 7 est inférieur à un seuil configurable (30 par défaut), indépendant de la taille des pages, qui varie selon l'écran (120 sur l'accueil et `/search`, 30 sur `/search/articles`). Au-dessus, il s'arrête. Le filet (niveau 8) ne compte pas dans ce seuil, ne déclenche pas l'arrêt, et s'ajoute dans tous les cas.
- Si un niveau donne des résultats mais peu, il les garde ET complète avec les niveaux suivants, jusqu'au seuil.
- Une requête courte et claire (un mot bien écrit) s'arrête généralement aux premiers niveaux. Une phrase longue, qui ne correspond à aucune annonce telle quelle, descend naturellement vers le niveau 7.
- Une correction de faute ne remplace jamais la requête d'origine : on cherche avec la requête d'origine ET avec la version corrigée, on fusionne, l'original d'abord.
- Une annonce qui contient les mots tapés ne doit jamais être exclue par un filtre automatique (ville, prix, "pas cher"). Ces critères servent à classer plus haut, jamais à éliminer. Seul l'utilisateur peut exclure, en activant lui-même un filtre.
- Un mot vide présent dans une annonce peut départager deux annonces d'un même niveau. Il ne pénalise jamais celles qui ne le contiennent pas et ne fait jamais gagner de niveau.
- Le nouveau moteur retourne au minimum tout ce que l'ancien moteur retournait, pour la même requête et sur la même page d'origine.
- L'ordre des résultats est stable d'une page à l'autre : aucune annonce n'apparaît en double ni ne disparaît avec la pagination, sur le site comme dans l'API.

## Détail des fonctionnalités

1. **Tolérance aux fautes (niveau 6)** : distance d'édition (Levenshtein/Damerau) avec un seuil adapté à la longueur du mot (aucune faute pour 1-3 lettres, 1 faute pour 4-7, 2 fautes pour 8-11, 3 fautes à partir de 12 lettres si le début du mot est identique, pour "refligilateur" → "refrigerateur"). Gérer aussi les inversions de lettres et les erreurs phonétiques courantes en français (ph/f, k/qu/c, s/ss, etc.). La tolérance sur les débuts de mot est traitée au niveau 5.

2. **Recherche par début de mot (niveau 5)** : "elec" trouve "electronique", et "ell" le trouve aussi via la tolérance d'une faute sur le début du mot, avec un index construit sur le vocabulaire des annonces.

3. **Normalisation (niveau 4)** : ignorer majuscules, accents, tirets, apostrophes, ponctuation et espaces multiples ; gérer pluriels simples et racines (stemming léger en français).

4. **Recherche multi-champs pondérée** : titre > catégories > lieu et vendeur > description ; tous les mots utiles comptent, sans ordre imposé ; bonus quand tous les mots sont présents et quand ils sont proches.

5. **Recherche en phrases (niveau 7)** : les utilisateurs écrivent souvent des phrases complètes ("je cherche un téléphone pas cher à Lomé", "vends moto en bon état", "j'ai besoin d'un frigo"). Le moteur en extrait les mots utiles et propose les bonnes annonces :
   - Extraction des mots-clés : découpe la phrase et retire les mots vides.
   - Pondération par rareté : un mot rare ("iphone", "frigo") pèse plus qu'un mot courant ("bon", "état"). Poids calculés à partir de toutes les annonces et recalculés régulièrement.
   - Mots inconnus ignorés : un mot absent du vocabulaire, même après correction de fautes, est ignoré au lieu de bloquer la recherche.
   - Correspondance souple : classer selon la somme des poids des mots trouvés. Essayer d'abord avec tous les mots-clés, puis retirer progressivement les moins importants jusqu'à atteindre le seuil. Bonus pour les expressions présentes dans l'annonce ("iphone 11", "machine à laver").
   - Détection de la ville : si la phrase contient une ville présente dans les annonces, les annonces de cette ville passent en premier à l'intérieur de leur niveau, sans exclure les autres. La liste des villes vient des données et s'enrichit automatiquement. Vérifie d'abord si `lieu` est un texte libre et gère ses variantes.
   - Détection du prix : reconnaître "moins de 50000", "max 50 000 F", "entre 20000 et 40000", "50k" et appliquer un bonus de classement. Pour "pas cher" ou "bon marché", favoriser les annonces sous le prix médian de la catégorie trouvée (la médiane, jamais la moyenne). Les médianes sont recalculées automatiquement.
   - Nombres et modèles : conserver les chiffres liés à un mot ("iphone 11", "ps5", "8 go") comme un seul élément fort. Le retrait progressif enlève le nombre avant le mot auquel il est lié : "iphone 7" élargit vers "iphone", jamais vers "7" seul. Un nombre seul ne suffit jamais à classer une annonce.
   - Transparence : afficher ce que le moteur a compris ("Résultats pour : téléphone, Lomé, prix bas") et permettre de retirer un critère d'un clic.

6. **Classement intelligent** : à l'intérieur d'un même niveau, classer par pertinence texte combinée avec des signaux métier : annonces boostées, vendeurs certifiés (`certifie`), fraîcheur, présence de photos, ville de l'utilisateur connecté. Poids réglables dans un seul fichier de configuration, avec les seuils de la cascade.

7. **Jamais de page vide (niveau 9)** : si rien ne correspond, afficher la catégorie la plus proche, puis des annonces populaires ou récentes, avec un message du type "Vous cherchiez peut-être…".

8. **Suggestions et autocomplétion** pendant la saisie : titres et catégories les plus pertinents, avec correction proposée ("Vous voulez dire : …") quand la requête contient une faute probable.

9. **Performance** : index pré-calculé stocké dans des tables MariaDB, mise en cache des requêtes fréquentes, debounce côté interface. Le moteur doit fonctionner aussi sur la base de test du projet : évite tout SQL propre à MariaDB, comme FULLTEXT.

## Mesure et statistiques

- `stat_recherches.resultats` continue de compter les vraies correspondances (niveaux 1 à 8). Le secours (niveau 9) n'y est jamais compté : une recherche servie uniquement par le secours reste une recherche "sans résultat" dans les statistiques.
- Ajoute à `stat_recherches` le niveau atteint par la recherche, sans nouveau tableau de bord.
- Le taux de réussite se mesure hors secours. Sinon il vaudrait 100 % automatiquement.

## Évolution avec la croissance du site

- **Démarrage** : une commande indexe toutes les annonces déjà approuvées.
- **Quand une annonce change d'état** : l'index est mis à jour immédiatement, à l'enregistrement, dans tous ces cas : approbation, modification (l'annonce repasse en attente et sort des résultats jusqu'à sa nouvelle approbation), blocage, suppression, y compris par suppression du compte vendeur ou de la catégorie, et transfert à un autre vendeur. Une annonce est indexée quand elle devient visible, pas quand elle est créée.
- **Quand une donnée liée change** : renommer une catégorie ou une sous-catégorie, ou changer le nom ou la ville d'un vendeur, met à jour les annonces concernées.
- **Mises à jour de masse** : recense les `update()` groupés et autres chemins qui ne passent pas par les événements du modèle, et traite-les.
- **Panne d'indexation** : une erreur d'indexation ne doit jamais empêcher la publication d'une annonce. Elle est journalisée et rattrapée par la réconciliation.
- **Recalcul automatique** : poids de rareté, prix médians, villes et mots vides détectés sont recalculés chaque nuit, et l'index est réconcilié avec la table des annonces. Le code ne définit aucune tâche planifiée aujourd'hui : ajoute la planification Laravel et indique-moi la commande cron à mettre sur le serveur.
- **Nouveaux mots et catégories** : un mot, une marque, une ville ou une catégorie qui apparaît dans une nouvelle annonce est reconnu sans modifier le code.
- **Passage à l'échelle** : les seuils et les poids restent corrects de quelques centaines à plusieurs dizaines de milliers d'annonces. Teste avec un jeu de données simulé 100 fois plus grand que l'actuel.

## Méthode de travail

1. Lis d'abord le code de la recherche et le schéma des données, puis résume ce que tu as compris.
2. Vérifie les prérequis et dis-moi ce qui manque : cron `schedule:run` et worker sur le serveur, nombre d'annonces, base MariaDB locale utilisable pour les tests de volume.
3. Propose un plan court (algorithmes, structure de l'index, cascade, mise à jour, impact sur la base de données) et attends ma validation avant de coder.
4. Implémente en trois lots, chacun déployable seul, en gardant l'ancien moteur actif tant que le nouveau n'est pas validé (interrupteur de configuration) :
   - **Lot 1, socle** : index, mises à jour, moteur unique, niveaux 1 à 4, filet de l'ancien moteur, statistiques.
   - **Lot 2, tolérance** : niveaux 5, 6 et 7, recalculs de nuit, secours.
   - **Lot 3, interface** : suggestions, "ce que le moteur a compris", critères retirables, réglage du classement.
5. Écris des tests automatisés avec ces cas au minimum :
   - **Mots simples** : "ell", "elec", "telefone", "ordinatuer", "iphone 11", "Électronique", "TELEPHONE".
   - **Exemple "ell"** : une annonce "électronique" sort avant une annonce "belle vaisselle", et cette dernière reste dans les résultats.
   - **Vraies recherches sans résultat en production** : "iphone 7", "i phone", "iphone13", "iphonese2020", "chaussureslanvin", "chausure", "batterie li", "canne a pech", "lampe a", "ipo", "xia", "guit", "conge", "mini refligilateur", "cocombre de mer", "siege coffeur". Elles doivent donner un résultat utile dès qu'une annonce correspondante existe dans le jeu de test.
   - **Produit absent du catalogue** : "xiaomi" sans aucune annonce Xiaomi passe par le secours, propose des annonces proches et reste comptée sans résultat.
   - **Phrases** : "je cherche un téléphone pas cher à Lomé", "j'ai besoin d'un frigo en bon état", "vends moto yamaha moins de 300000", "je veux acheter un ordinatuer portable", une phrase de 20 mots avec un seul mot utile, une phrase où aucun mot n'est reconnu.
   - **Mots vides** : pour "je cherche un téléphone", une annonce sur un téléphone sort avant une annonce qui contient seulement "je", "cherche" et "un".
   - **Cas limites** : une requête sans aucun résultat, un seul caractère, une requête vide.
   - **Priorité des vraies annonces** : pour chaque requête de test, toute annonce contenant les mots exacts de la requête figure dans les résultats, et avant les résultats approchants.
   - **Filtres et tri** : les filtres de l'utilisateur restent stricts à tous les niveaux, et le tri par prix s'applique.
   - **Pagination** : aucune annonce en double ni manquante d'une page à l'autre, y compris à la frontière entre deux niveaux.
   - **Modération** : une annonce en attente ou bloquée n'est jamais trouvée, et une annonce devient trouvable dès son approbation.
   - **Annonces futures** : une nouvelle annonce approuvée, contenant un mot qui n'existait pas avant, est trouvable immédiatement avec la requête exacte, puis avec une faute, puis avec un début de mot, puis dans une phrase. Une annonce modifiée ou supprimée est bien mise à jour dans les résultats.
   - **Statistiques** : une recherche servie uniquement par le secours est enregistrée avec 0 résultat.
   - **API mobile** : les routes existantes gardent leur format, avec uniquement des champs ajoutés.
6. Compare l'ancien et le nouveau moteur sur un échantillon de requêtes réelles tirées de `stat_recherches` : toute annonce retournée par l'ancien moteur doit encore apparaître dans le nouveau. Aucune régression n'est acceptée.
7. Mesure le temps de réponse avant/après, y compris avec le jeu de données 100 fois plus grand, et le pourcentage de requêtes de test qui retournent au moins un résultat hors secours. Si une mesure est impossible, dis-le clairement au lieu de l'estimer.

## Conventions du projet

- Réponds en français.
- Ne committe jamais un `public/build/manifest.json` reconstruit : les assets compilés ne sont pas dans git et vivent uniquement sur le serveur.
- Ne pousse sur GitHub que si je le demande.
- Donne les commandes serveur une par bloc (chemin du projet sur le serveur : `~/htdocs/lomeplussave`).

## Livrables

Le code, les tests, un fichier de configuration des poids et des seuils de la cascade, et un court document expliquant comment le moteur fonctionne (y compris la mise à jour pour les annonces futures) et comment régler les paramètres.
