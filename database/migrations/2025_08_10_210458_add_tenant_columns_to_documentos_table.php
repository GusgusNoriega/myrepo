<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('business_id')->after('id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->after('business_id')->constrained('users')->nullOnDelete();

            // si quieres búsquedas por tipo dentro del negocio
            $table->index(['business_id','tipo']);
        });
    }
    public function down(): void {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex(['business_id','tipo']);
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropConstrainedForeignId('business_id');
        });
    }
};