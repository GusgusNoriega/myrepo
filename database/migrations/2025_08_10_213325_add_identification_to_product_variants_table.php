<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('product_variants', function (Blueprint $t) {
      $t->string('barcode', 64)->nullable()->after('sku');
      $t->index(['product_id','barcode']);
    });
  }
  public function down(): void {
    Schema::table('product_variants', function (Blueprint $t) {
      $t->dropIndex(['product_variants_product_id_barcode_index']);
      $t->dropColumn('barcode');
    });
  }
};