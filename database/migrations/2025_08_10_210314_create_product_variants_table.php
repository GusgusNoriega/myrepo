<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku');
            $table->string('name')->nullable();
            $table->unsignedInteger('price_cents')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->unique(['product_id','sku']);
            $table->index('product_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_variants');
    }
};