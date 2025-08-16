<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->enum('status', ['draft','active','archived'])->default('draft');

            $table->unsignedInteger('price_cents')->nullable();
            $table->string('currency', 3)->default('USD');

            $table->json('attributes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id','slug']);
            $table->unique(['business_id','sku']);
            $table->index('business_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};