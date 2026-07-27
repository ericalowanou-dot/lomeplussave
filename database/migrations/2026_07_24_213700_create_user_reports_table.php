<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 100);
            $table->string('message', 500)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->unique(['reporter_id', 'reported_user_id']);
            $table->index(['reported_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
