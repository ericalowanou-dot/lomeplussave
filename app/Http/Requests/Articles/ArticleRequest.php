<?php

namespace App\Http\Requests\Articles;

use App\Models\SousCategorie;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

/**
 * Règles communes à la publication et à la modification d'une annonce,
 * partagées par le site et l'API mobile.
 */
abstract class ArticleRequest extends FormRequest
{
    public const MAX_PHOTOS = 6;

    /** Une annonce publiée sans photo est refusée ; en modification, les photos sont facultatives. */
    abstract protected function photosRequired(): bool;

    public function rules(): array
    {
        return [
            'categorie' => 'required|exists:categories,id',
            'sous_categorie_id' => 'required|exists:sous_categories,id',
            'titre' => 'required|string|max:255',
            'prix_ht' => 'required|numeric|min:0|max:999999999',
            'lieu' => 'required|string|max:255',
            'description' => 'required|string|min:20|max:1500',
            'etat' => 'required|in:neuf,occasion',
            'livraison' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'sous_categorie_id.required' => 'La sous-catégorie est obligatoire.',
            'sous_categorie_id.exists' => 'La sous-catégorie sélectionnée n\'existe pas.',
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'prix_ht.required' => 'Le prix est obligatoire.',
            'prix_ht.numeric' => 'Le prix doit être un nombre.',
            'prix_ht.min' => 'Le prix doit être supérieur ou égal à 0.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'description.min' => 'La description doit contenir au moins 20 caractères.',
            'description.max' => 'La description ne peut pas dépasser 1500 caractères.',
            'etat.required' => 'L\'état du produit est obligatoire.',
            'etat.in' => 'L\'état doit être "neuf" ou "occasion".',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->checkPhotos($validator);
                $this->checkSubcategory($validator);
            },
        ];
    }

    /**
     * Photos réellement reçues (les envois ratés par le navigateur sont ignorés).
     *
     * @return list<UploadedFile>
     */
    public function photos(): array
    {
        $photos = $this->file('photos', []);
        if (! is_array($photos)) {
            $photos = $photos ? [$photos] : [];
        }

        return array_values(array_filter(
            $photos,
            fn ($photo) => $photo instanceof UploadedFile && $photo->isValid() && $photo->getError() === UPLOAD_ERR_OK
        ));
    }

    /** Champs de l'annonce prêts à enregistrer. */
    public function articleAttributes(): array
    {
        $data = $this->validated();

        return [
            'titre' => $data['titre'],
            'prix_ht' => $data['prix_ht'],
            'lieu' => $data['lieu'],
            'description' => $data['description'],
            'sous_categorie_id' => (int) $data['sous_categorie_id'],
            'neuf' => $data['etat'] === 'neuf',
            'livraison' => $this->boolean('livraison'),
        ];
    }

    private function checkPhotos(Validator $validator): void
    {
        $photos = $this->photos();

        if ($photos === []) {
            if ($this->photosRequired()) {
                $validator->errors()->add('photos', 'Au moins une photo est obligatoire. Veuillez sélectionner au moins une image.');
            }

            return;
        }

        if (count($photos) > self::MAX_PHOTOS) {
            $validator->errors()->add('photos', 'Vous ne pouvez pas télécharger plus de 6 photos.');

            return;
        }

        foreach ($photos as $photo) {
            if ($problem = ImageOptimizer::articlePhotoProblem($photo)) {
                $validator->errors()->add('photos', $problem);

                return;
            }
        }
    }

    private function checkSubcategory(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['categorie', 'sous_categorie_id'])) {
            return;
        }

        $categorieId = SousCategorie::whereKey($this->input('sous_categorie_id'))->value('categorie_id');
        if ((int) $categorieId !== (int) $this->input('categorie')) {
            $validator->errors()->add('sous_categorie_id', 'La sous-catégorie sélectionnée n\'appartient pas à la catégorie choisie.');
        }
    }
}
