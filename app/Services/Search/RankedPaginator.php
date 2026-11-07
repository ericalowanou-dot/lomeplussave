<?php

namespace App\Services\Search;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Page de résultats du moteur : un paginateur ordinaire, plus ce que le moteur a compris.
 */
class RankedPaginator extends LengthAwarePaginator
{
    public ?SearchOutcome $outcome = null;
}
