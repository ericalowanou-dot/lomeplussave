<?php

/**
 * Réglages du moteur de recherche d'annonces. Voir MOTEUR_RECHERCHE.md.
 */
return [
    // legacy : anciennes requêtes LIKE. cascade : nouveau moteur.
    'engine' => env('SEARCH_ENGINE', 'legacy'),

    // La cascade s'arrête dès que les niveaux stricts ont trouvé ce nombre d'annonces.
    // Indépendant de la taille des pages.
    'threshold' => 30,

    // Niveau 3 : part minimale du poids des mots utiles à retrouver dans l'annonce.
    'min_weight_share' => 0.7,

    // Un mot présent dans une trop grande part du catalogue ne sert plus à trier.
    'frequent_word_ratio' => 0.35,
    'frequent_word_min_documents' => 200,

    // Au-delà de ces limites, le reste de la requête est ignoré.
    'max_query_words' => 40,
    'max_useful_words' => 15,

    // Niveau 5 : débuts de mot.
    'prefix_min_length' => 2,
    'prefix_max_terms' => 100,
    'fuzzy_prefix_min_df' => 2, // un début de mot avec faute ne vise que les mots présents dans N annonces
    'fuzzy_prefix_max_terms' => 20,

    // Niveau 6 : nombre de mots proches gardés pour un mot inconnu.
    'max_corrections' => 5,

    // Niveau 9 : secours quand rien ne correspond.
    'fallback_size' => 30,
    'popular_days' => 30,

    // Suggestions pendant la saisie.
    'suggestions_max' => 8,

    'cache_seconds' => 300,
    'cache_max_ids' => 20000,

    // Importance d'un mot selon le champ où il est trouvé.
    'field_weights' => [
        'titre' => 1.0,
        'categories' => 0.8,
        'lieu' => 0.5,
        'vendeur' => 0.5,
        'description' => 0.4,
    ],

    // Classement à l'intérieur d'un même niveau. Ne fait jamais changer une annonce de niveau.
    'ranking' => [
        'pertinence' => 1.0,
        'boost' => 0.5,
        'certifie' => 0.15,
        'fraicheur' => 0.2,
        'fraicheur_demi_vie_jours' => 30,
        'photos' => 0.05,
        'ville' => 0.6, // ville écrite dans la recherche
        'prix' => 0.4, // prix demandé, ou sous la médiane pour « pas cher »
        'ville_utilisateur' => 0.1, // ville du compte connecté
    ],
];
