<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('media', function (Blueprint $t) {
      $t->boolean('is_public')->default(false)->after('owner_user_id'); // para URLs públicas
      $t->string('purpose', 64)->nullable()->after('is_public');        // e.g. product_gallery, logo, doc
      $t->index(['business_id','is_public']);
    });
  }
  public function down(): void {
    Schema::table('media', function (Blueprint $t) {
      $t->dropIndex(['media_business_id_is_public_index']);
      $t->dropColumn(['is_public','purpose']);
    });
  }
};