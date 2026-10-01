<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal des recherches tapées par les visiteurs (statistiques admin).
     * La recherche en direct est regroupée : une saisie progressive = une seule ligne.
     */
    public function up(): void
    {
        Schema::create('stat_recherches', function (Blueprint $table) {
            $table->id();
            $table->string('terme', 255);
            $table->string('terme_normalise', 191);
            $table->unsignedInteger('resultats')->default(0);
            $table->string('source', 30)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('visiteur_hash', 64);
            $table->timestamps();

            $table->index('created_at');
            $table->index(['terme_normalise', 'created_at']);
            $table->index(['visiteur_hash', 'updated_at']); // fusion des frappes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_recherches');
    }
};
