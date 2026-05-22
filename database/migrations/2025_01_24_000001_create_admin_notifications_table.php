<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'user_registered', 'article_pending', 'problem_report', etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Icone FontAwesome
            $table->string('color')->default('primary'); // Couleur de la notification
            $table->string('url')->nullable(); // URL vers la page concernée
            $table->unsignedBigInteger('related_id')->nullable(); // ID de l'entité liée (user_id, article_id, etc.)
            $table->string('related_type')->nullable(); // Type d'entité (User, Article, ProblemReport, etc.)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
