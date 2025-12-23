<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class articlesCertified extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('articles')->insert([
            [
                'sous_categorie_id' => 1,
                'user_id' => 5, 
                'titre' => 'belle moto',
                'photo' => 'moto.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 23000000,
                'description' => 'moto ultra rapide',
            ],
            [
                'sous_categorie_id' => 2,
                'user_id' => 6,
                'titre' => 'cle roue',
                'photo' => 'cle_mecanic.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 4500,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 3,
                'user_id' => 7,
                'titre' => 'appareil photographique',
                'photo' => 'appareil_photo.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 20000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 4,
                'user_id' => 8,
                'titre' => 'machine à laver',
                'photo' => 'machine_lavage.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 130000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 5,
                'user_id' => 5, 
                'titre' => 'Iphone nouveau',
                'photo' => 'iphone.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 230000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 6,
                'user_id' => 8,
                'titre' => 'vêtement pour homme',
                'photo' => 'djacket.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 30000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 7,
                'user_id' => 6, 
                'titre' => 'appartement à lome, Togo',
                'photo' => 'appartement.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 30000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
            [
                'sous_categorie_id' => 8,
                'user_id' => 7,
                'titre' => 'gros pneu camion',
                'photo' => 'pneu_camion.jpg',
                'photo1' => null,  // Assigné à null
                'photo2' => null,  // Assigné à null
                'photo3' => null,  // Assigné à null
                'photo4' => null,  // Assigné à null
                'prix_ht' => 15000000,
                'description' => 'ce sont les engins pouvant être utilisés pour un déplacement',
            ],
        ]);
    }
}
