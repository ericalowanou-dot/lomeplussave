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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_group_message')->default(false)->after('body');
            $table->unsignedBigInteger('parent_message_id')->nullable()->after('is_group_message');
            $table->foreign('parent_message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['parent_message_id']);
            $table->dropColumn(['is_group_message', 'parent_message_id']);
        });
    }
};
