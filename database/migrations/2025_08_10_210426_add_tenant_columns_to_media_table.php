<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('business_id')
                ->nullable()
                ->after('id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('business_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['business_id','collection_name']);
        });
    }
    public function down(): void {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['business_id','collection_name']);
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropConstrainedForeignId('business_id');
        });
    }
};