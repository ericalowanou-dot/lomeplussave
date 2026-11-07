<?php

namespace App\Console\Commands;

use App\Services\Search\SearchIndexer;
use Illuminate\Console\Command;

class SearchRefreshCommand extends Command
{
    protected $signature = 'search:refresh';

    protected $description = "Remet l'index de recherche d'accord avec les annonces et recalcule les poids et les prix médians";

    public function handle(SearchIndexer $indexer): int
    {
        $result = $indexer->refresh();

        $this->info("{$result['ajoutees']} annonces ajoutées, {$result['retirees']} retirées.");

        return self::SUCCESS;
    }
}
