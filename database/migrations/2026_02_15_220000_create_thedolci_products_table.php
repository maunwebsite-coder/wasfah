<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thedolci_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->text('story')->nullable();
            $table->string('cover_image');
            $table->json('gallery_images')->nullable();
            $table->json('size_prices');
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_seasonal')->default(false);
            $table->dateTime('seasonal_ends_at')->nullable();
            $table->unsignedInteger('limited_quantity')->nullable();
            $table->boolean('preorder_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thedolci_products');
    }
};
