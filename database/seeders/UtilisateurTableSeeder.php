<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UtilisateurTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            [
            'name'=>'eric',
            'email'=>'eric@gmail.com',
            'password' =>('1234'),
            'role'=>null,
            'ville'=>'lome',
            'telephone'=>93343666,
            'photo_profil'=>'pneu.jpg',
            ],

            [
                'name'=>'ama',
                'email'=>'ama@gmail.com',
                'password' =>('12345'),
                'role'=>null,
                'ville'=>'baguida',
                'telephone'=>93343666,
                'photo_profil'=>'cailloux2.jpg',
                ],
            ]);










    }
}
