<?php

namespace App\Http\Requests\Articles;

use App\Models\Article;

class StoreArticleRequest extends ArticleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Article::class) ?? false;
    }

    protected function photosRequired(): bool
    {
        return true;
    }
}
