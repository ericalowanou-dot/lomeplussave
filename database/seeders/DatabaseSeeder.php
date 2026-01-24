<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Article;
use Illuminate\Support\Str;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\Sous_CategoriesTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les dossiers d'images si nécessaire
        $directories = [
            'users/profil',
            'articles',
            'categories/images',
            'souscategories/images',
            'advertisements'
        ];

        foreach ($directories as $dir) {
            $path = public_path($dir);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
        }

        // Créer l'utilisateur admin s'il n'existe pas
        $admin = User::firstOrCreate(
            ['email' => 'princeawu1999@gmail.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'photo_profil' => null, // Pas d'image par défaut
                'certifie' => 1,
                'coins' => 1000,
                'telephone' => '+22893343666',
                'whatsapp' => '+22893343666',
            ]
        );

        // Créer quelques utilisateurs de test
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => 'user' . $i . '@example.com'],
                [
                    'name' => 'Utilisateur ' . $i,
                    'password' => Hash::make('password'),
                    'photo_profil' => null,
                    'certifie' => $i <= 2 ? 1 : 0, // Les 2 premiers sont certifiés
                    'coins' => rand(100, 500),
                    'telephone' => '+22893343666',
                    'whatsapp' => '+22893343666',
                ]
            );
        }

        // Créer les catégories
        $this->call([
            CategoriesTableSeeder::class,
            Sous_CategoriesTableSeeder::class,
        ]);

        $createdSousCategories = \App\Models\SousCategorie::query()
            ->with('categorie')
            ->get()
            ->keyBy('nom');

        // Créer un catalogue d'articles de démonstration (20 entrées)
        $articlesData = [
            [
                'titre' => 'iPhone 15 Pro Max Titanium',
                'prix' => 950000,
                'description' => 'Dernière génération Apple avec triple capteur Pro, autonomie optimisée et finition titane.',
                'sous_cat' => 'Smartphones & Accessoires',
                'photo' => 'articles/20251112010331_UsBFVxDzmpITQphF.png',
                'photo1' => 'articles/20251112010331_fT8IvMhosSnC4PSC.png',
                'photo2' => 'articles/20251112010331_EVDOEPHzd4Q27bHd.png',
                'lieu' => 'Lomé',
                'quantite' => 5,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 2,
                'boost_days' => 14,
                'user_index' => 0,
            ],
            [
                'titre' => 'MacBook Air M3 15"',
                'prix' => 890000,
                'description' => 'Puce Apple M3, 16Go RAM, 512Go SSD, écran 15 pouces et autonomie record.',
                'sous_cat' => 'Ordinateurs & Tablettes',
                'photo' => 'articles/20251112010331_fT8IvMhosSnC4PSC.png',
                'photo1' => 'articles/20251112010331_UsBFVxDzmpITQphF.png',
                'lieu' => 'Cotonou',
                'quantite' => 3,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 4,
                'boost_days' => 10,
                'user_index' => 1,
            ],
            [
                'titre' => 'Casque sans fil Sony WH-1000XM5',
                'prix' => 245000,
                'description' => 'Réduction de bruit adaptative, autonomie 30h, idéal pour les créatifs mobiles.',
                'sous_cat' => 'Audio & Home Cinema',
                'photo' => 'articles/20251112010331_EVDOEPHzd4Q27bHd.png',
                'photo1' => 'articles/20251112010331_bBH2Gji7IRyzo1cw.png',
                'lieu' => 'Abidjan',
                'quantite' => 8,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 6,
                'user_index' => 2,
            ],
            [
                'titre' => 'Pack domotique maison connectée',
                'prix' => 185000,
                'description' => 'Hub central, capteurs multi-pièces et automatisations prêtes pour la maison intelligente.',
                'sous_cat' => 'Objets Connectés',
                'photo' => 'articles/20251112010331_bBH2Gji7IRyzo1cw.png',
                'lieu' => 'Accra',
                'quantite' => 10,
                'neuf' => true,
                'livraison' => true,
                'status' => 'pending',
                'user_index' => 3,
            ],
            [
                'titre' => 'Canapé modulable en lin premium',
                'prix' => 265000,
                'description' => 'Structure bois massif, revêtement lin lavable, modulable 4 places.',
                'sous_cat' => 'Mobilier Design',
                'photo' => 'articles/20251112010331_PNQfK3Akh30CzQpO.png',
                'photo1' => 'articles/20251112010331_UsBFVxDzmpITQphF.png',
                'lieu' => 'Lomé',
                'quantite' => 2,
                'neuf' => true,
                'livraison' => false,
                'status' => 'approved',
                'approved_days' => 5,
                'boost_days' => 21,
                'user_index' => 4,
            ],
            [
                'titre' => 'Suspension artisanale en raphia',
                'prix' => 68000,
                'description' => 'Pièce fabriquée à la main au Bénin, finition naturelle et lumière chaleureuse.',
                'sous_cat' => 'Décoration Intérieure',
                'photo' => 'articles/20251112010331_jiA9jNyDK1XoqUvo.png',
                'lieu' => 'Cotonou',
                'quantite' => 6,
                'neuf' => false,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 3,
                'user_index' => 0,
            ],
            [
                'titre' => 'Batterie de cuisine cuivre 10 pièces',
                'prix' => 198000,
                'description' => 'Cuivre martelé, compatibilité induction, idéale pour une cuisine gastronomique.',
                'sous_cat' => 'Cuisine & Art de la table',
                'photo' => 'articles/20251112011947_noD39GqlArdvv5G0.png',
                'lieu' => 'Abidjan',
                'quantite' => 4,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 7,
                'user_index' => 1,
            ],
            [
                'titre' => 'Ensemble lounge extérieur en teck',
                'prix' => 440000,
                'description' => 'Salon de jardin 4 pièces, bois de teck FSC et coussins déperlants.',
                'sous_cat' => 'Jardin & Extérieur',
                'photo' => 'articles/20251112011947_nxhq5QFFrHlN4gOG.png',
                'lieu' => 'Accra',
                'quantite' => 3,
                'neuf' => true,
                'livraison' => false,
                'status' => 'approved',
                'approved_days' => 1,
                'boost_days' => 7,
                'user_index' => 2,
            ],
            [
                'titre' => 'Tailleur minimaliste en lin beige',
                'prix' => 96000,
                'description' => 'Tailleur femme coupe moderne, lin premium, disponible du 34 au 44.',
                'sous_cat' => 'Mode Femme',
                'photo' => 'articles/20251112011947_VdQLG5tgRsRcuqoK.png',
                'lieu' => 'Paris',
                'quantite' => 12,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 8,
                'user_index' => 3,
            ],
            [
                'titre' => 'Blazer oversize coton bio',
                'prix' => 78000,
                'description' => 'Blazer homme coupe relax, coton biologique certifié GOTS.',
                'sous_cat' => 'Mode Homme',
                'photo' => 'articles/20251112015248_GWOwOMJleqjRKzSG.png',
                'lieu' => 'Dakar',
                'quantite' => 9,
                'neuf' => true,
                'livraison' => true,
                'status' => 'pending',
                'user_index' => 4,
            ],
            [
                'titre' => 'Sneakers édition limitée Neon Pulse',
                'prix' => 125000,
                'description' => 'Edition limitée 500 exemplaires, semelle mousse recyclée et détails réfléchissants.',
                'sous_cat' => 'Chaussures & Sacs',
                'photo' => 'articles/1757270946_68bdd3a28528e.png',
                'lieu' => 'Lomé',
                'quantite' => 15,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 2,
                'boost_days' => 5,
                'user_index' => 0,
            ],
            [
                'titre' => 'Montre automatique saphir Midnight',
                'prix' => 385000,
                'description' => 'Boîtier acier 41mm, mouvement automatique suisse, verre saphir anti-reflet.',
                'sous_cat' => 'Montres & Bijoux',
                'photo' => 'articles/1757271209_68bdd4a912822.png',
                'lieu' => 'Abidjan',
                'quantite' => 5,
                'neuf' => true,
                'livraison' => false,
                'status' => 'approved',
                'approved_days' => 9,
                'boost_days' => 12,
                'user_index' => 1,
            ],
            [
                'titre' => 'Routine glow vitamine C',
                'prix' => 42000,
                'description' => 'Sérum concentré, crème SPF 30 et masque hydratant pour un teint éclatant.',
                'sous_cat' => 'Soins du Visage',
                'photo' => 'articles/1757271244_68bdd4ccd71cd.png',
                'lieu' => 'Lomé',
                'quantite' => 20,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 4,
                'user_index' => 2,
            ],
            [
                'titre' => 'Kit barber premium acier mat',
                'prix' => 67000,
                'description' => 'Tondeuse pro, shaver de précision et set de ciseaux carbone.',
                'sous_cat' => 'Cheveux & Barbershop',
                'photo' => 'articles/1757272406_68bdd9562e364.png',
                'lieu' => 'Accra',
                'quantite' => 7,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 5,
                'user_index' => 3,
            ],
            [
                'titre' => 'Coffret parfums signatures',
                'prix' => 99000,
                'description' => 'Trois fragrances mixtes 50ml, fabrication artisanale française.',
                'sous_cat' => 'Parfums & Maquillage',
                'photo' => 'articles/1757272406_68bdd9563589d.png',
                'lieu' => 'Paris',
                'quantite' => 9,
                'neuf' => true,
                'livraison' => true,
                'status' => 'pending',
                'user_index' => 4,
            ],
            [
                'titre' => 'Programme nutrition énergie 30j',
                'prix' => 56000,
                'description' => 'Pack de compléments énergétiques, guide nutrition et suivi digital.',
                'sous_cat' => 'Fitness & Nutrition',
                'photo' => 'articles/1757273775_68bddeafecffc.jpg',
                'lieu' => 'Lomé',
                'quantite' => 18,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 6,
                'user_index' => 0,
            ],
            [
                'titre' => 'Pack home gym Elite',
                'prix' => 310000,
                'description' => 'Banc ajustable, haltères modulaires et application coaching incluse.',
                'sous_cat' => 'Matériel de Fitness',
                'photo' => 'articles/1757274054_68bddfc61ead6.jpg',
                'lieu' => 'Cotonou',
                'quantite' => 4,
                'neuf' => true,
                'livraison' => false,
                'status' => 'approved',
                'approved_days' => 3,
                'boost_days' => 9,
                'user_index' => 1,
            ],
            [
                'titre' => 'Kit camping aventure 6 pièces',
                'prix' => 96000,
                'description' => 'Tente légère 3 places, réchaud compact, lampes LED et accessoires.',
                'sous_cat' => 'Sports Outdoor',
                'photo' => 'articles/1757289351_68be1b87dda89.png',
                'lieu' => 'Abidjan',
                'quantite' => 10,
                'neuf' => true,
                'livraison' => false,
                'status' => 'approved',
                'approved_days' => 2,
                'user_index' => 2,
            ],
            [
                'titre' => 'Ballon pro match Elite',
                'prix' => 39000,
                'description' => 'Ballon match FIFA Quality Pro, grip haute précision et couture thermique.',
                'sous_cat' => 'Sports Collectifs',
                'photo' => 'articles/1757327183_68beaf4fc59c2.jpg',
                'lieu' => 'Libreville',
                'quantite' => 25,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 5,
                'user_index' => 3,
            ],
            [
                'titre' => 'Vélo urbain électrique Pulse City',
                'prix' => 780000,
                'description' => 'Batterie 80 km, cadre aluminium, assistance intelligente 5 modes.',
                'sous_cat' => 'Cyclisme & Mobilité douce',
                'photo' => 'articles/1757331213_68bebf0d8a891.png',
                'lieu' => 'Lomé',
                'quantite' => 6,
                'neuf' => true,
                'livraison' => true,
                'status' => 'approved',
                'approved_days' => 4,
                'boost_days' => 6,
                'user_index' => 4,
            ],
        ];

        // Images de démonstration existantes dans public/images
        $demoPhotos = [
            'images/1.png',
            'images/2.png',
            'images/3.png',
        ];

        foreach ($articlesData as $articleData) {
            $sousCategorie = $createdSousCategories->get($articleData['sous_cat'] ?? '');

            if (!$sousCategorie) {
                $this->command?->warn("Sous-catégorie introuvable pour l'article : {$articleData['titre']}");
                continue;
            }

            $userIndex = $articleData['user_index'] ?? array_rand($users);
            $user = $users[$userIndex % count($users)];
            $status = $articleData['status'] ?? 'approved';
            $approvedAt = $status === 'approved'
                ? now()->subDays($articleData['approved_days'] ?? 1)
                : null;
            $boostedUntil = isset($articleData['boost_days'])
                ? now()->addDays($articleData['boost_days'])
                : null;

            // Choisir une image de démonstration existante
            $photo = $demoPhotos[array_rand($demoPhotos)];

            $payload = [
                'user_id' => $user->id,
                'titre' => $articleData['titre'],
                'description' => $articleData['description'],
                'prix_ht' => $articleData['prix'],
                'lieu' => $articleData['lieu'],
                'sous_categorie_id' => $sousCategorie->id,
                'photo' => $photo,
                'photo1' => null,
                'photo2' => null,
                'photo3' => null,
                'photo4' => null,
                'neuf' => $articleData['neuf'],
                'livraison' => $articleData['livraison'],
                'status' => $status,
                'approved_at' => $approvedAt,
                'boosted_until' => $boostedUntil,
            ];

            if ($status === 'blocked') {
                $payload['blocked_at'] = now()->subDays($articleData['blocked_days'] ?? 0);
                $payload['block_reason'] = $articleData['block_reason'] ?? 'Blocage préventif en cours de vérification.';
            }

            Article::create($payload);
        }

        $this->command->info('Base de données initialisée avec succès !');
        $this->command->info('Admin: princeawu1999@gmail.com / password');
        $this->command->info('Utilisateurs: user1@example.com à user5@example.com / password');
    }
}
