<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'active_business_id')) {
            Schema::table('users', function (Blueprint $table) {
                // primero crea la columna
                $table->unsignedBigInteger('active_business_id')->nullable()->after('remember_token');
            });

            Schema::table('users', function (Blueprint $table) {
                // luego agrega FK e índice
                $table->foreign('active_business_id')
                      ->references('id')->on('businesses')
                      ->nullOnDelete();
                $table->index('active_business_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'active_business_id')) {
            Schema::table('users', function (Blueprint $table) {
                // quita FK e índice si existen
                try { $table->dropForeign(['active_business_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['active_business_id']); } catch (\Throwable $e) {}
                $table->dropColumn('active_business_id');
            });
        }
    }
};