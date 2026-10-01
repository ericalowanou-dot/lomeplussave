<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal des vues d'annonces et des visites de boutiques (statistiques admin).
     * Une ligne = un visiteur sur une fenêtre d'une heure (dédoublonnage côté app).
     */
    public function up(): void
    {
        Schema::create('stat_visites', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // 'article' | 'boutique'
            $table->unsignedBigInteger('article_id')->nullable();
            $table->unsignedBigInteger('vendeur_id')->nullable();
            $table->unsignedBigInteger('visiteur_id')->nullable();
            $table->string('visiteur_hash', 64);
            $table->timestamp('created_at')->nullable();

            $table->index(['type', 'created_at']);
            $table->index(['article_id', 'created_at']);
            $table->index(['vendeur_id', 'created_at']);
            $table->index(['visiteur_hash', 'created_at']); // dédoublonnage
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_visites');
    }
};
