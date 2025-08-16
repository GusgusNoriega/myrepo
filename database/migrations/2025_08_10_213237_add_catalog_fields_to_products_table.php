<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('products', function (Blueprint $t) {
      $t->boolean('has_variants')->default(false)->after('status'); // true si usa variants
      $t->unsignedInteger('cost_cents')->nullable()->after('price_cents');     // costo
      $t->unsignedInteger('compare_at_price_cents')->nullable()->after('cost_cents'); // precio tachado
      $t->string('barcode', 64)->nullable()->after('sku');          // EAN/UPC
      $t->boolean('tax_included')->default(true)->after('currency');
      $t->unsignedInteger('weight_grams')->nullable()->after('attributes');
      $t->json('dimensions')->nullable()->after('weight_grams');    // {w,h,d,unit}
      $t->timestamp('published_at')->nullable()->after('deleted_at');

      // índices útiles para filtro/listado
      $t->index(['business_id','status']);
      $t->index(['business_id','category_id','status']);
      $t->index(['business_id','price_cents']);
      $t->index(['business_id','barcode']);
    });
  }
  public function down(): void {
    Schema::table('products', function (Blueprint $t) {
      $t->dropIndex(['products_business_id_status_index']);
      $t->dropIndex(['products_business_id_category_id_status_index']);
      $t->dropIndex(['products_business_id_price_cents_index']);
      $t->dropIndex(['products_business_id_barcode_index']);
      $t->dropColumn([
        'has_variants','cost_cents','compare_at_price_cents','barcode',
        'tax_included','weight_grams','dimensions','published_at'
      ]);
    });
  }
};