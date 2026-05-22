<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si l'utilisateur admin existe déjà
        $admin = User::where('email', 'admin@sitelomeplus.com')->first();
        
        if (!$admin) {
            // Créer un utilisateur administrateur
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@sitelomeplus.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'telephone' => '+228 90 12 34 56',
                'photo_profil' => null,
                'certifie' => true,
                'is_blocked' => false,
            ]);

            $this->command->info('Utilisateur administrateur créé avec succès !');
            $this->command->info('Email: admin@sitelomeplus.com');
            $this->command->info('Mot de passe: admin123');
        } else {
            $this->command->info('Utilisateur administrateur existe déjà.');
        }
    }
}