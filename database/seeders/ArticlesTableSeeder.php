<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('articles')->insert([
            [
                'sous_categorie_id' => 1,
                'user_id' => 3, 
                'titre' => 'voiture toyota',
                'photo' => 'bellecaisse.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 2,
                'user_id' => 3,
                'titre' => 'Véhicules',
                'photo' => 'voiture.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 3,
                'user_id' => 3,
                'titre' => 'voiture toyota',
                'photo' => 'voiture.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 4,
                'user_id' => 3,
                'titre' => 'Véhicules',
                'photo' => 'pc.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 5,
                'user_id' => 4, 
                'titre' => 'voiture toyota',
                'photo' => 'pc2.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 6,
                'user_id' => 4,
                'titre' => 'Véhicules',
                'photo' => 'bellecaisse.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 7,
                'user_id' => 4, 
                'titre' => 'voiture toyota',
                'photo' => 'voiture.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 4,
                'titre' => 'Véhicules',
                'photo' => 'pc.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 4, 
                'titre' => 'Véhicules',
                'photo' => 'pc2.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 4,
                'titre' => 'Véhicules',
                'photo' => 'pc.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 3,
                'titre' => 'Véhicules',
                'photo' => 'voiture.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 3, 
                'titre' => 'pneu',
                'photo' => 'pneu.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 150000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 4, 
                'titre' => 'valise',
                'photo' => 'valise.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],

            ['sous_categorie_id' => 2,
            'user_id' => 3, 
            'titre' => 'pierres',
            'photo' => 'cailloux.jpg',
            'photo1' => 'cailloux2.jpg',
            'photo2' => 'cailloux3.jpg',
            'photo3' => null,  // Assigné à null
            'photo4' => null,  // Assigné à null
            'prix_ht' => 15000000,
            'quantite_disponible' => 100,
            'description' => 'ce sont les cailloux pouvant être utilisés pour un déplacement'
        ],

        ]);
    }
}
