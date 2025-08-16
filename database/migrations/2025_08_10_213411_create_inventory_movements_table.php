<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('inventory_movements', function (Blueprint $t) {
      $t->id();
      $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
      $t->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
      $t->enum('type', ['in','out','adjust'])->index(); // entrada/salida/ajuste
      $t->integer('quantity'); // positivo o negativo
      $t->string('reason')->nullable(); // venta, compra, ajuste
      $t->json('meta')->nullable();     // referencia externa (pedido, proveedor)
      $t->timestamps();

      $t->index(['business_id','variant_id','type']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('inventory_movements');
  }
};