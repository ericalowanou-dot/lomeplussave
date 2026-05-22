<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCertifiedTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
          
         
            [
                'name'=>'komlannnnnnnnviiiii',
                'email'=>'ama01@gmail.com',
                'password' =>bcrypt('12345'),
                'role'=>null,
                'ville'=>'baguida',
                'telephone'=>93343666,
                'photo_profil'=>'togo.jpg',
                'certifie' => 1, // Certifié
                ],

                [
                    'name'=>'francis',
                    'email'=>'abalo3@gmail.com',
                    'password' =>bcrypt('12345'),
                    'role'=>null,
                    'ville'=>'sokode',
                    'telephone'=>93343666,
                    'photo_profil'=>null,
                    'certifie' => 1, // Certifié
                    ],

                  
            ]);










    }
}
