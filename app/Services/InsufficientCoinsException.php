<?php

namespace App\Services;

class InsufficientCoinsException extends \RuntimeException
{
    public function __construct(public readonly int $needed)
    {
        parent::__construct('Solde de coins insuffisant.');
    }
}
