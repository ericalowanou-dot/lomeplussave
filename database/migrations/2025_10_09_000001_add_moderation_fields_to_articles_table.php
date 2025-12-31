<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'status')) {
                $table->enum('status', ['pending', 'approved', 'blocked'])->default('pending')->after('photo6');
            }
            if (!Schema::hasColumn('articles', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('articles', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('articles', 'block_reason')) {
                $table->text('block_reason')->nullable()->after('blocked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'block_reason')) {
                $table->dropColumn('block_reason');
            }
            if (Schema::hasColumn('articles', 'blocked_at')) {
                $table->dropColumn('blocked_at');
            }
            if (Schema::hasColumn('articles', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('articles', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};












