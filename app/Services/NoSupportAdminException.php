<?php

namespace App\Services;

class NoSupportAdminException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Impossible de trouver l\'administrateur.');
    }
}
