<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration ajoute des index pour améliorer les performances
     * des requêtes fréquentes, notamment avec beaucoup d'utilisateurs et de données.
     */
    public function up(): void
    {
        // Fonction helper pour vérifier si un index existe
        $hasIndex = function($tableName, $indexName) {
            $connection = Schema::getConnection();
            $database = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$database, $tableName, $indexName]
            );
            return $result[0]->count > 0;
        };

        // Index sur la table articles
        Schema::table('articles', function (Blueprint $table) use ($hasIndex) {
            // Index sur status (utilisé dans presque toutes les requêtes publiques)
            if (!$hasIndex('articles', 'articles_status_index')) {
                $table->index('status', 'articles_status_index');
            }
            
            // Index composite sur status et created_at (pour les tris fréquents)
            if (!$hasIndex('articles', 'articles_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'articles_status_created_at_index');
            }
            
            // Index sur boosted_until (pour les articles boostés)
            if (Schema::hasColumn('articles', 'boosted_until') && !$hasIndex('articles', 'articles_boosted_until_index')) {
                $table->index('boosted_until', 'articles_boosted_until_index');
            }
            
            // Index composite sur status, boosted_until et created_at (requête très fréquente)
            if (Schema::hasColumn('articles', 'boosted_until') && !$hasIndex('articles', 'articles_status_boosted_created_index')) {
                $table->index(['status', 'boosted_until', 'created_at'], 'articles_status_boosted_created_index');
            }
            
            // Index sur prix_ht (pour les tris par prix)
            if (!$hasIndex('articles', 'articles_prix_ht_index')) {
                $table->index('prix_ht', 'articles_prix_ht_index');
            }
        });

        // Index sur la table users
        Schema::table('users', function (Blueprint $table) use ($hasIndex) {
            // Index sur is_blocked (utilisé dans les filtres admin)
            if (Schema::hasColumn('users', 'is_blocked') && !$hasIndex('users', 'users_is_blocked_index')) {
                $table->index('is_blocked', 'users_is_blocked_index');
            }
            
            // Index sur certifie (pour les filtres)
            if (Schema::hasColumn('users', 'certifie') && !$hasIndex('users', 'users_certifie_index')) {
                $table->index('certifie', 'users_certifie_index');
            }
            
            // Index sur ville (utilisé dans les recherches)
            if (Schema::hasColumn('users', 'ville') && !$hasIndex('users', 'users_ville_index')) {
                $table->index('ville', 'users_ville_index');
            }
        });

        // Index sur la table sessions
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) use ($hasIndex) {
                // Index sur user_id si la colonne existe
                if (Schema::hasColumn('sessions', 'user_id') && !$hasIndex('sessions', 'sessions_user_id_index')) {
                    $table->index('user_id', 'sessions_user_id_index');
                }
                
                // Index sur last_activity (pour le nettoyage des sessions expirées)
                if (Schema::hasColumn('sessions', 'last_activity') && !$hasIndex('sessions', 'sessions_last_activity_index')) {
                    $table->index('last_activity', 'sessions_last_activity_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            try {
                $table->dropIndex('articles_status_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('articles_status_created_at_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('articles_boosted_until_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('articles_status_boosted_created_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('articles_prix_ht_index');
            } catch (\Exception $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropIndex('users_is_blocked_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('users_certifie_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('users_ville_index');
            } catch (\Exception $e) {}
        });

        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                try {
                    $table->dropIndex('sessions_user_id_index');
                } catch (\Exception $e) {}
                try {
                    $table->dropIndex('sessions_last_activity_index');
                } catch (\Exception $e) {}
            });
        }
    }
};

