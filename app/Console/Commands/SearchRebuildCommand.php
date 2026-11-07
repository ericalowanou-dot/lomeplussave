<?php

namespace App\Console\Commands;

use App\Services\Search\SearchIndexer;
use Illuminate\Console\Command;

class SearchRebuildCommand extends Command
{
    protected $signature = 'search:rebuild';

    protected $description = "Reconstruit l'index du moteur de recherche à partir des annonces approuvées";

    public function handle(SearchIndexer $indexer): int
    {
        $count = $indexer->rebuild(fn (int $done) => $this->output->write("\r{$done} annonces"));

        $this->newLine();
        $this->info("{$count} annonces indexées.");

        return self::SUCCESS;
    }
}
