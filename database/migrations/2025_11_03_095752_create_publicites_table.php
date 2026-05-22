<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publicites', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->nullable(); // Titre/libellé de la publicité
            $table->string('image'); // Image de la bannière publicitaire
            $table->string('lien_url')->nullable(); // URL vers laquelle rediriger au clic
            $table->enum('position', ['header', 'sidebar', 'footer', 'entre_articles', 'homepage_top', 'homepage_bottom'])->default('entre_articles'); // Position d'affichage
            $table->date('date_debut')->nullable(); // Date de début de diffusion
            $table->date('date_fin')->nullable(); // Date de fin de diffusion
            $table->boolean('is_active')->default(true); // Actif ou non
            $table->integer('ordre')->default(0); // Ordre d'affichage
            $table->integer('clics')->default(0); // Compteur de clics
            $table->integer('affichages')->default(0); // Compteur d'affichages
            $table->text('notes')->nullable(); // Notes internes pour l'admin
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['position', 'is_active']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicites');
    }
};
