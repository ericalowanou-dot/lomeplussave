<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Sous_CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxonomy = config('catalogue', []);

        if (empty($taxonomy)) {
            $this->command?->warn('Aucune sous-catégorie chargée depuis la configuration catalogue.');
            return;
        }

        DB::table('sous_categories')->delete();

        $categoryIds = DB::table('categories')->pluck('id', 'nom');
        $payload = [];
        $now = now();

        foreach ($taxonomy as $category) {
            if (empty($category['sous_categories'])) {
                continue;
            }

            $categorieId = $categoryIds[$category['nom']] ?? null;

            if (!$categorieId) {
                $this->command?->warn("Catégorie manquante pour les sous-catégories : {$category['nom']}");
                continue;
            }

            foreach ($category['sous_categories'] as $subCategory) {
                $payload[] = [
                    'categorie_id' => $categorieId,
                    'nom' => $subCategory['nom'],
                    'description' => $subCategory['description'] ?? null,
                    'image' => $subCategory['image'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($payload)) {
            DB::table('sous_categories')->insert($payload);
        }
    }
}
