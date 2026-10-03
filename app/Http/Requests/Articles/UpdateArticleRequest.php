<?php

namespace App\Http\Requests\Articles;

use Illuminate\Contracts\Validation\Validator;

class UpdateArticleRequest extends ArticleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('article')) ?? false;
    }

    protected function photosRequired(): bool
    {
        return false;
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Vous n\'avez pas l\'autorisation de modifier cet article.');
    }

    protected function failedValidation(Validator $validator): void
    {
        if (! $this->expectsJson()) {
            session()->flash('error_solutions', [
                'Vérifiez que tous les champs obligatoires sont remplis',
                'Assurez-vous que les images sont au bon format',
                'Vérifiez que vous ne dépassez pas 6 images',
                'Vérifiez que le prix est un nombre valide',
            ]);
        }

        parent::failedValidation($validator);
    }
}
