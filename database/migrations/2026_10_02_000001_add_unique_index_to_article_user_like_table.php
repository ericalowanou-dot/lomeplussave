<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'article_user_like_article_user_unique';

    /**
     * Un double clic rapide sur « J'aime » pouvait enregistrer deux fois le même like.
     * On supprime les doublons (en gardant le plus ancien) puis on interdit qu'ils reviennent.
     */
    public function up(): void
    {
        $duplicates = DB::table('article_user_like')
            ->select('article_id', 'user_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('article_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('article_user_like')
                ->where('article_id', $duplicate->article_id)
                ->where('user_id', $duplicate->user_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        if (! Schema::hasIndex('article_user_like', self::INDEX)) {
            Schema::table('article_user_like', function (Blueprint $table) {
                $table->unique(['article_id', 'user_id'], self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('article_user_like', self::INDEX)) {
            return;
        }

        try {
            Schema::table('article_user_like', function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        } catch (\Throwable $e) {
            // MySQL peut utiliser cet index pour la clé étrangère article_id : on le laisse alors en place.
        }
    }
};
