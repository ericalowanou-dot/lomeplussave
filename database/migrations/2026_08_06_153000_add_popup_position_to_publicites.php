<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE publicites MODIFY COLUMN position ENUM(
                'header',
                'sidebar',
                'footer',
                'entre_articles',
                'homepage_top',
                'homepage_bottom',
                'popup'
            ) NOT NULL DEFAULT 'entre_articles'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('publicites')->where('position', 'popup')->update(['position' => 'homepage_top']);
            DB::statement("ALTER TABLE publicites MODIFY COLUMN position ENUM(
                'header',
                'sidebar',
                'footer',
                'entre_articles',
                'homepage_top',
                'homepage_bottom'
            ) NOT NULL DEFAULT 'entre_articles'");
        }
    }
};
