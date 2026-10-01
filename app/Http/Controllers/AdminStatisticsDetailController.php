<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Services\AdminStatistics;
use App\Services\StatisticsDetails;
use App\Services\StatisticsFiches;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pages de détail derrière chaque statistique, et fiches d'une annonce / d'un vendeur.
 */
class AdminStatisticsDetailController extends Controller
{
    public function details(Request $request, string $type): View
    {
        $statistics = AdminStatistics::fromRequest($request);
        $detail = (new StatisticsDetails($statistics, $request))->build($type);

        return view('admin.statistics.details', $this->filterData($statistics) + [
            'detail' => $detail,
            'perPageOptions' => StatisticsDetails::PER_PAGE,
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $statistics = AdminStatistics::fromRequest($request);
        $detail = (new StatisticsDetails($statistics, $request))->build($type, all: true);
        $host = $request->getSchemeAndHttpHost();

        $filename = sprintf('lomeplus-%s-%s-au-%s.csv', $type, $statistics->from->format('Y-m-d'), $statistics->to->format('Y-m-d'));

        return response()->streamDownload(function () use ($detail, $statistics, $host) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM : accents corrects dans Excel

            $filtre = $statistics->filterLabel();
            fputcsv($out, [$detail['titre'] . ' — du ' . $statistics->from->format('d/m/Y') . ' au ' . $statistics->to->format('d/m/Y')
                . ($filtre ? ' — filtre : ' . $filtre : '')
                . ($detail['filtres'] ? ' — ' . implode(', ', $detail['filtres']) : '')], ';');

            $columns = array_filter($detail['colonnes'], fn ($c) => $c['format'] !== 'annonce_mini');
            fputcsv($out, array_merge(array_column($columns, 'label'), ['Lien']), ';');

            foreach ($detail['lignes'] as $row) {
                $line = [];
                foreach ($columns as $key => $column) {
                    $value = StatisticsDetails::plainValue($row[$key] ?? null, $column['format']);
                    // Neutralise les formules Excel dans le texte saisi par les utilisateurs
                    if (preg_match('/^[=+\-@\t\r]/', $value)) {
                        $value = "'" . $value;
                    }
                    $line[] = $value;
                }
                $link = $row['annonce']['url'] ?? $row['_href'] ?? '';
                $line[] = str_starts_with($link, '/') ? $host . $link : $link;
                fputcsv($out, $line, ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function annonce(Request $request, Article $article): View
    {
        $statistics = AdminStatistics::fromRequest($request);
        $fiche = (new StatisticsFiches($statistics, $request))->annonce($article);

        return view('admin.statistics.annonce', $this->filterData($statistics, withArticleFilters: false) + ['fiche' => $fiche]);
    }

    public function vendeur(Request $request, User $user): View
    {
        $statistics = AdminStatistics::fromRequest($request);
        $fiche = (new StatisticsFiches($statistics, $request))->vendeur($user);

        return view('admin.statistics.vendeur', $this->filterData($statistics, withArticleFilters: false) + ['fiche' => $fiche]);
    }

    /**
     * Données communes à la barre de filtres (calendrier, ville, catégorie).
     */
    private function filterData(AdminStatistics $statistics, bool $withArticleFilters = true): array
    {
        return [
            'filters' => $statistics,
            'periods' => AdminStatistics::PERIODS,
            'granularities' => AdminStatistics::GRANULARITIES,
            'villeOptions' => $withArticleFilters ? AdminStatistics::villeOptions() : [],
            'categorieOptions' => $withArticleFilters ? AdminStatistics::categorieOptions() : [],
            'showArticleFilters' => $withArticleFilters,
        ];
    }
}
