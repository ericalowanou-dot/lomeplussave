<?php

namespace App\Services\Articles;

/** Une photo n'a pas pu être enregistrée (disque plein, droits, stockage injoignable…). */
class PhotoStorageException extends \RuntimeException
{
}
