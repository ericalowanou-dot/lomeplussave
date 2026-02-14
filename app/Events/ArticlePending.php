<?php

namespace App\Events;

use App\Models\Article;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArticlePending
{
    use Dispatchable, SerializesModels;

    public $article;

    /**
     * Create a new event instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }
}
