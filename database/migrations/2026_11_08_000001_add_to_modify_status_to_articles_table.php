<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('articles', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('pending', 'approved', 'blocked', 'to_modify') NOT NULL DEFAULT 'pending'");
        }
        // SQLite / autres : colonne string déjà permissive, rien à faire.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('articles', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('articles')->where('status', 'to_modify')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('pending', 'approved', 'blocked') NOT NULL DEFAULT 'pending'");
        }
    }
};
