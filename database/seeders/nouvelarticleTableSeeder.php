<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class nouvelarticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('articles')->insert([
            
                
            ['sous_categorie_id' => 6,
            'user_id' => 3, 
            'titre' => 'pierres',
            'photo' => 'cailloux.jpg',
            'photo1' => 'cailloux2.jpg',
            'photo2' => 'cailloux3.jpg',
            'photo3' => null,  // Assigné à null
            'photo4' => null,  // Assigné à null
            'prix_ht' => 15000000,
            'description' => 'ce sont les cailloux pouvant être utilisés pour un déplacement',
        ],

        [
            'sous_categorie_id' => 6,
            'user_id' => 4, 
            'titre' => 'Véhicules',
            'photo' => 'voiture.jpg',
            'photo1' => null,  // Assigné à null
            'photo2' => null,  // Assigné à null
            'photo3' => null,  // Assigné à null
            'photo4' => null,  // Assigné à null
            'prix_ht' => 15000000,
            'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
        ],


        ['sous_categorie_id' => 7,
        'user_id' => 3, 
        'titre' => 'pierres',
        'photo' => 'cailloux.jpg',
        'photo1' => 'cailloux2.jpg',
        'photo2' => 'cailloux3.jpg',
        'photo3' => null,  // Assigné à null
        'photo4' => null,  // Assigné à null
        'prix_ht' => 15000000,
        'quantite_disponible' => 100,
        'description' => 'ce sont les cailloux pouvant être utilisés pour un déplacement',
    ],

    [
        'sous_categorie_id' => 7,
        'user_id' => 4, 
        'titre' => 'Véhicules',
        'photo' => 'voiture.jpg',
        'photo1' => null,  // Assigné à null
        'photo2' => null,  // Assigné à null
        'photo3' => null,  // Assigné à null
        'photo4' => null,  // Assigné à null
        'prix_ht' => 15000000,
        'quantite_disponible' => 100,
        'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
    ],

        ]);
        }
}
