<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'coins')) {
                $table->unsignedInteger('coins')->default(0)->after('role');
            }
            if (!Schema::hasColumn('users', 'certifie_until')) {
                $table->timestamp('certifie_until')->nullable()->after('coins');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'boosted_until')) {
                $table->timestamp('boosted_until')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'coins')) {
                $table->dropColumn('coins');
            }
            if (Schema::hasColumn('users', 'certifie_until')) {
                $table->dropColumn('certifie_until');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'boosted_until')) {
                $table->dropColumn('boosted_until');
            }
        });
    }
};


