<?php

namespace Database\Seeders;

use App\Models\Publicite;
use Illuminate\Database\Seeder;

class PublicitesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $demos = [
            [
                'titre' => 'Demo Promo Flash LOME+',
                'image' => 'advertisements/demo/pub1.svg',
                'lien_url' => 'https://lomeplus.com',
                'ordre' => 1,
                'notes' => 'Publicité de démonstration 1',
            ],
            [
                'titre' => 'Demo Boutique Ouverture',
                'image' => 'advertisements/demo/pub2.svg',
                'lien_url' => 'https://lomeplus.com',
                'ordre' => 2,
                'notes' => 'Publicité de démonstration 2',
            ],
            [
                'titre' => 'Demo Électronique -30%',
                'image' => 'advertisements/demo/pub3.svg',
                'lien_url' => 'https://lomeplus.com',
                'ordre' => 3,
                'notes' => 'Publicité de démonstration 3',
            ],
            [
                'titre' => 'Demo Mode & Style',
                'image' => 'advertisements/demo/pub4.svg',
                'lien_url' => 'https://lomeplus.com',
                'ordre' => 4,
                'notes' => 'Publicité de démonstration 4',
            ],
        ];

        foreach ($demos as $demo) {
            Publicite::updateOrCreate(
                [
                    'titre' => $demo['titre'],
                    'position' => 'entre_articles',
                ],
                [
                    'image' => $demo['image'],
                    'lien_url' => $demo['lien_url'],
                    'apres_n_articles' => null,
                    'date_debut' => null,
                    'date_fin' => null,
                    'is_active' => true,
                    'ordre' => $demo['ordre'],
                    'notes' => $demo['notes'],
                ]
            );
        }
    }
}
