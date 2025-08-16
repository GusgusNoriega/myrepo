<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('product_limit');
            $table->unsignedBigInteger('storage_limit_bytes');
            $table->unsignedInteger('staff_limit');
            $table->unsignedInteger('asset_limit')->nullable();
            $table->unsignedInteger('category_limit')->nullable();
            $table->json('other')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('plan_features');
    }
};