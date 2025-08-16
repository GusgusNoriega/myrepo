<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('product_category', function (Blueprint $t) {
      $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $t->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
      $t->primary(['product_id','category_id']);
      $t->index('category_id');
    });
  }
  public function down(): void {
    Schema::dropIfExists('product_category');
  }
};