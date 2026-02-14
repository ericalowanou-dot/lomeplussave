<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['admin_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropIndex(['admin_id', 'is_read', 'created_at']);
            $table->dropColumn('admin_id');
        });
    }
};
