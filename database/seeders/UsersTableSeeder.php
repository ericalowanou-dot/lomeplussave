<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         // Exemple d'ajout de plusieurs vendeurs
         DB::table('users')->insert([
            [
            'name'=>'eric',
            'email'=>'ericalowanou@gmail.com',
            'password' =>bcrypt('1234')],



            [
                'name'=>'utilisateur2',
                'email'=>'ericalowanou2@gmail.com',
                'password' => bcrypt('password')],
    
    

        ]);
    }
}
