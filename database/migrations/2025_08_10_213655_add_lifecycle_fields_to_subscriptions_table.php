<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('subscriptions', function (Blueprint $t) {
      $t->timestamp('trial_ends_at')->nullable()->after('current_period_end');
      $t->timestamp('canceled_at')->nullable()->after('trial_ends_at');
      $t->string('external_customer_id')->nullable()->after('external_ref'); // id en pasarela
      $t->json('payment_method')->nullable()->after('external_customer_id'); // ult. método usado
    });
  }
  public function down(): void {
    Schema::table('subscriptions', function (Blueprint $t) {
      $t->dropColumn(['trial_ends_at','canceled_at','external_customer_id','payment_method']);
    });
  }
};