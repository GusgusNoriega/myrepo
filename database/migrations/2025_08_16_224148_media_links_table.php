<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_links', function (Blueprint $table) {
            $table->id();

            // Multi-tenant (opcional pero útil para validar pertenencia)
            $table->unsignedBigInteger('business_id')->nullable()->index();

            // Relación polimórfica
            $table->string('linkable_type');
            $table->unsignedBigInteger('linkable_id');

            // Referencia a spatie media
            $table->unsignedBigInteger('media_id');

            // Flags para rol de la imagen
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_gallery')->default(false);

            // Orden para galería
            $table->unsignedInteger('position')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['linkable_type', 'linkable_id']);
            $table->index(['linkable_type', 'linkable_id', 'is_gallery']);
            $table->unique(['linkable_type','linkable_id','media_id'], 'media_links_unique');

            // FKs
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_links');
    }
};