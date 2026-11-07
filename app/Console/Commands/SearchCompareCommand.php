<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\Articles\ArticleSearch;
use App\Services\Search\SearchEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Compare l'ancien et le nouveau moteur sur les vraies recherches enregistrées.
 * Ne modifie rien, et ne change pas le moteur actif du site.
 */
class SearchCompareCommand extends Command
{
    protected $signature = 'search:compare {--limit=1000 : nombre de recherches distinctes à comparer}';

    protected $description = "Vérifie sur les vraies recherches que le nouveau moteur garde tout ce que trouvait l'ancien";

    public function handle(ArticleSearch $search, SearchEngine $engine): int
    {
        $terms = DB::table('stat_recherches')
            ->selectRaw('terme_normalise, MAX(terme) as terme, COUNT(*) as total')
            ->groupBy('terme_normalise')
            ->orderByDesc('total')
            ->limit((int) $this->option('limit'))
            ->get();

        $compared = $searches = $oldEmpty = $newEmpty = $regressions = 0;
        $slowest = 0.0;

        foreach ($terms as $term) {
            $old = $search->search($term->terme)->reorder()->pluck('id')->all();

            $start = hrtime(true);
            $outcome = $engine->run($term->terme, Article::query()->where('status', 'approved'));
            $slowest = max($slowest, (hrtime(true) - $start) / 1e6);

            $found = $outcome->realCount > 0 ? $outcome->ids : [];
            $missing = array_diff($old, $found);

            $compared++;
            $searches += $term->total;
            $oldEmpty += $old === [] ? $term->total : 0;
            $newEmpty += $found === [] ? $term->total : 0;

            if ($missing !== []) {
                $regressions++;
                $this->warn(sprintf('« %s » : %d annonces perdues', $term->terme, count($missing)));
            }
        }

        $percent = fn (int $value) => $searches > 0 ? round(100 * $value / $searches, 1) : 0;

        $this->table(['Mesure', 'Résultat'], [
            ['Recherches distinctes comparées', $compared],
            ['Recherches représentées', $searches],
            ['Sans résultat, ancien moteur', "{$oldEmpty} ({$percent($oldEmpty)} %)"],
            ['Sans résultat, nouveau moteur (hors secours)', "{$newEmpty} ({$percent($newEmpty)} %)"],
            ['Recherches avec des annonces perdues', $regressions],
            ['Recherche la plus lente', round($slowest) . ' ms'],
        ]);

        return $regressions === 0 ? self::SUCCESS : self::FAILURE;
    }
}
