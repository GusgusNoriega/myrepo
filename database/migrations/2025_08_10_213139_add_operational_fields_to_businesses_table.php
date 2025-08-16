<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('businesses', function (Blueprint $t) {
      $t->string('domain')->nullable()->after('slug');           // dominio propio: tienda.com
      $t->string('subdomain')->nullable()->after('domain');       // subdominio: acme.tuapp.com
      $t->string('timezone', 64)->nullable()->after('currency');  // e.g. America/Lima
      $t->string('locale', 10)->nullable()->after('timezone');    // e.g. es_PE
      $t->string('contact_name')->nullable()->after('locale');
      $t->string('contact_email')->nullable()->after('contact_name');
      $t->json('settings')->nullable()->after('contact_email');   // tema, color primario, etc.

      $t->unique('domain');
      $t->unique('subdomain');
    });
  }
  public function down(): void {
    Schema::table('businesses', function (Blueprint $t) {
      $t->dropUnique(['domain']);
      $t->dropUnique(['subdomain']);
      $t->dropColumn(['domain','subdomain','timezone','locale','contact_name','contact_email','settings']);
    });
  }
};