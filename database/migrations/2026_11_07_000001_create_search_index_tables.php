<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index du moteur de recherche : une ligne par annonce approuvée, le vocabulaire,
     * et le lien mot → annonce. Tout se reconstruit avec `php artisan search:rebuild`.
     */
    public function up(): void
    {
        Schema::create('search_documents', function (Blueprint $table) {
            $table->foreignId('article_id')->primary()->constrained('articles')->cascadeOnDelete();
            $table->text('titre');
            $table->text('categories');
            $table->text('lieu');
            $table->string('ville', 191)->default('')->index(); // lieu en une valeur : la liste des villes en vient
            $table->text('vendeur');
            $table->text('description');
            $table->timestamp('boosted_until')->nullable();
            $table->boolean('certifie')->default(false);
            $table->unsignedTinyInteger('photos')->default(0);
            $table->timestamp('publie_le')->nullable();
            $table->decimal('prix', 15, 2)->default(0);
            $table->unsignedBigInteger('sous_categorie_id')->nullable();
        });

        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('terme', 64)->unique();
            $table->string('racine', 64)->index();
            $table->string('phonetique', 64)->index();
            $table->unsignedTinyInteger('longueur')->index();
            $table->unsignedInteger('df')->default(0); // nombre d'annonces contenant ce mot
        });

        // Prix médian par sous-catégorie, recalculé par `search:refresh`.
        Schema::create('search_category_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('sous_categorie_id')->primary();
            $table->decimal('prix_median', 15, 2);
        });

        Schema::create('search_postings', function (Blueprint $table) {
            $table->foreignId('term_id')->constrained('search_terms')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedTinyInteger('champ');
            $table->unsignedSmallInteger('occurrences')->default(1);

            $table->primary(['term_id', 'article_id', 'champ']);
            $table->index('article_id');
        });

        Schema::table('stat_recherches', function (Blueprint $table) {
            $table->unsignedTinyInteger('niveau')->nullable()->after('resultats');
        });
    }

    public function down(): void
    {
        Schema::table('stat_recherches', function (Blueprint $table) {
            $table->dropColumn('niveau');
        });

        Schema::dropIfExists('search_category_stats');
        Schema::dropIfExists('search_postings');
        Schema::dropIfExists('search_terms');
        Schema::dropIfExists('search_documents');
    }
};
