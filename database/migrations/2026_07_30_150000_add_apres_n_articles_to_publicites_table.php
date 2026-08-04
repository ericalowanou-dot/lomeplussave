<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicites', function (Blueprint $table) {
            $table->unsignedInteger('apres_n_articles')
                ->nullable()
                ->after('position')
                ->comment('Pour entre_articles : afficher après le N-ème article de la liste');
            $table->index(['position', 'apres_n_articles']);
        });
    }

    public function down(): void
    {
        Schema::table('publicites', function (Blueprint $table) {
            $table->dropIndex(['position', 'apres_n_articles']);
            $table->dropColumn('apres_n_articles');
        });
    }
};
