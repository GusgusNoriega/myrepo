<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('assets_count')->default(0);
            $table->unsignedBigInteger('storage_bytes')->default(0);
            $table->timestamps();

            $table->primary('business_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('usage_counters');
    }
};