<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('memberships', function (Blueprint $t) {
      $t->enum('state', ['invited','active','suspended'])->default('active')->after('role');
      $t->timestamp('accepted_at')->nullable()->after('state');
      $t->index(['business_id','state']);
    });
  }
  public function down(): void {
    Schema::table('memberships', function (Blueprint $t) {
      $t->dropIndex(['memberships_business_id_state_index']);
      $t->dropColumn(['state','accepted_at']);
    });
  }
};