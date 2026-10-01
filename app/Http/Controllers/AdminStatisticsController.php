<?php

namespace App\Http\Controllers;

use App\Services\AdminStatistics;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStatisticsController extends Controller
{
    /**
     * Sections exportables en CSV : clé => [titre, colonnes [libellé => champ]].
     */
    private const EXPORTS = [
        'vendeurs' => ['Vendeurs qui publient le plus', [
            'Vendeur' => 'nom', 'Email' => 'email', 'Annonces (période)' => 'annonces', 'Approuvées' => 'approuvees',
            'Annonces (total)' => 'annonces_total', 'Vues reçues' => 'vues', 'Likes reçus' => 'likes', 'Inscrit le' => 'inscrit_le',
        ]],
        'categories' => ['Catégories', ['Catégorie' => 'nom', 'Annonces' => 'annonces', 'Part (%)' => 'part', 'Vues' => 'vues']],
        'sous_categories' => ['Sous-catégories', [
            'Sous-catégorie' => 'nom', 'Catégorie' => 'categorie', 'Annonces' => 'annonces', 'Part (%)' => 'part', 'Vues' => 'vues',
        ]],
        'villes' => ['Villes', ['Ville' => 'nom', 'Annonces' => 'annonces', 'Part (%)' => 'part']],
        'mots' => ['Mots-clés des titres', ['Mot' => 'mot', 'Annonces' => 'annonces', 'Part (%)' => 'part']],
        'expressions' => ['Expressions des titres', ['Expression' => 'mot', 'Annonces' => 'annonces', 'Part (%)' => 'part']],
        'articles_vus' => ['Annonces les plus vues', [
            'Annonce' => 'titre', 'Vendeur' => 'vendeur', 'Catégorie' => 'categorie', 'Ville' => 'lieu', 'Prix' => 'prix',
            'Vues' => 'vues', 'Visiteurs uniques' => 'visiteurs', 'Likes' => 'likes', 'Lien' => 'url',
        ]],
        'boutiques' => ['Boutiques les plus visitées', [
            'Vendeur' => 'nom', 'Visites boutique' => 'visites_boutique', 'Vues des annonces' => 'vues_annonces',
            'Visiteurs uniques' => 'visiteurs', 'Annonces en ligne' => 'annonces_en_ligne',
            'Annonce la plus vue' => 'meilleure_annonce.titre', 'Vues de cette annonce' => 'meilleure_annonce.vues', 'Boutique' => 'boutique_url',
        ]],
        'articles_aimes' => ['Annonces les plus aimées', [
            'Annonce' => 'titre', 'Vendeur' => 'vendeur', 'Likes' => 'likes', 'Vues' => 'vues', 'Lien' => 'url',
        ]],
        'recherches' => ['Recherches les plus fréquentes', [
            'Recherche' => 'terme', 'Recherches' => 'recherches', 'Visiteurs' => 'chercheurs',
            'Résultats moyens' => 'resultats_moyens', 'Sans résultat' => 'sans_resultat',
        ]],
        'recherches_vides' => ['Recherches sans résultat', [
            'Recherche' => 'terme', 'Recherches' => 'recherches', 'Visiteurs' => 'chercheurs', 'Dernière' => 'derniere',
        ]],
        'evolution' => ['Évolution', []],
    ];

    public function index(Request $request): View
    {
        $statistics = AdminStatistics::fromRequest($request);

        return view('admin.statistics.index', [
            'stats' => $statistics->all($request->boolean('refresh')),
            'filters' => $statistics,
            'periods' => AdminStatistics::PERIODS,
            'granularities' => AdminStatistics::GRANULARITIES,
            'topSizes' => AdminStatistics::TOP_SIZES,
        ]);
    }

    public function export(Request $request, string $section): StreamedResponse
    {
        abort_unless(array_key_exists($section, self::EXPORTS), 404);

        $statistics = AdminStatistics::fromRequest($request);
        $stats = $statistics->all();
        [$title, $columns] = self::EXPORTS[$section];

        if ($section === 'evolution') {
            $columns = ['Période' => 'label', 'Inscriptions' => 'inscriptions', 'Annonces' => 'annonces', 'Likes' => 'likes',
                'Vues annonces' => 'vues', 'Visites boutiques' => 'visites_boutiques', 'Recherches' => 'recherches', 'Inscrits cumulés' => 'inscrits_cumules'];
            $rows = [];
            foreach ($stats['series']['labels'] as $i => $label) {
                $row = ['label' => $label];
                foreach (array_slice($columns, 1) as $field) {
                    $row[$field] = $stats['series'][$field][$i] ?? 0;
                }
                $rows[] = $row;
            }
        } else {
            $rows = match ($section) {
                'vendeurs' => $stats['top_vendeurs'],
                'categories' => $stats['top_categories'],
                'sous_categories' => $stats['top_sous_categories'],
                'villes' => $stats['top_villes'],
                'mots' => $stats['top_mots']['mots'],
                'expressions' => $stats['top_mots']['expressions'],
                'articles_vus' => $stats['top_articles_vus'],
                'boutiques' => $stats['top_boutiques'],
                'articles_aimes' => $stats['top_articles_aimes'],
                'recherches' => $stats['recherches']['top'],
                'recherches_vides' => $stats['recherches']['sans_resultat_top'],
            };
        }

        $filename = sprintf('lomeplus-stats-%s-%s-au-%s.csv', $section, $statistics->from->format('Y-m-d'), $statistics->to->format('Y-m-d'));

        $host = $request->getSchemeAndHttpHost();

        return response()->streamDownload(function () use ($title, $columns, $rows, $statistics, $host) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM : accents corrects dans Excel
            fputcsv($out, [$title . ' — du ' . $statistics->from->format('d/m/Y') . ' au ' . $statistics->to->format('d/m/Y')], ';');
            fputcsv($out, array_merge(['Rang'], array_keys($columns)), ';');

            foreach ($rows as $index => $row) {
                $line = [$index + 1];
                foreach ($columns as $field) {
                    $value = data_get($row, $field);
                    // Les liens sont stockés en chemins relatifs : on les rend complets pour le fichier
                    if (is_string($value) && str_ends_with($field, 'url') && str_starts_with($value, '/')) {
                        $value = $host . $value;
                    }
                    // Neutralise les formules Excel dans le texte saisi par les utilisateurs
                    if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
                        $value = "'" . $value;
                    }
                    $line[] = $value;
                }
                fputcsv($out, $line, ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
