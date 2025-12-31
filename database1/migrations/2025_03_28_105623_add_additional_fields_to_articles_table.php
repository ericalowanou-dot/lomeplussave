<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'photo5')) {
                $table->string('photo5')->nullable();
            }
            if (!Schema::hasColumn('articles', 'photo6')) {
                $table->string('photo6')->nullable();
            }
            if (!Schema::hasColumn('articles', 'lieu')) {
                $table->string('lieu')->default('Lomé');
            }
            if (!Schema::hasColumn('articles', 'livraison')) {
                $table->boolean('livraison')->default(false);
            }
            if (!Schema::hasColumn('articles', 'neuf')) {
                $table->boolean('neuf')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'photo5')) {
                $table->dropColumn('photo5');
            }
            if (Schema::hasColumn('articles', 'photo6')) {
                $table->dropColumn('photo6');
            }
            if (Schema::hasColumn('articles', 'lieu')) {
                $table->dropColumn('lieu');
            }
            if (Schema::hasColumn('articles', 'livraison')) {
                $table->dropColumn('livraison');
            }
            if (Schema::hasColumn('articles', 'neuf')) {
                $table->dropColumn('neuf');
            }
        });
    }
};

