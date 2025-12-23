<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxonomy = config('catalogue', []);

        if (empty($taxonomy)) {
            $this->command?->warn('Aucune catégorie chargée depuis la configuration catalogue.');
            return;
        }

        DB::table('categories')->delete();

        $payload = [];
        $now = now();

        foreach ($taxonomy as $category) {
            $payload[] = [
                'nom' => $category['nom'],
                'description' => $category['description'] ?? null,
                'image' => $category['image'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('categories')->insert($payload);
    }
}
