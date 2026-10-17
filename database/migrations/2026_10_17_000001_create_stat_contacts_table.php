<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal des clics « Appeler » et « WhatsApp » vers un vendeur (statistiques admin).
     * Table séparée de stat_visites pour ne pas fausser les compteurs de vues.
     */
    public function up(): void
    {
        Schema::create('stat_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('canal', 10);  // 'appel' | 'whatsapp'
            $table->string('source', 10); // 'site' | 'appli'
            $table->unsignedBigInteger('article_id')->nullable(); // null : depuis la boutique
            $table->unsignedBigInteger('vendeur_id');
            $table->unsignedBigInteger('visiteur_id')->nullable();
            $table->string('visiteur_hash', 64);
            $table->timestamp('created_at')->nullable();

            $table->index(['vendeur_id', 'created_at']);
            $table->index(['article_id', 'created_at']);
            $table->index(['visiteur_hash', 'created_at']); // dédoublonnage
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_contacts');
    }
};
