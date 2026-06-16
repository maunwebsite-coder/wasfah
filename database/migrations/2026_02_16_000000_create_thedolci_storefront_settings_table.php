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
        if (Schema::hasTable('thedolci_storefront_settings')) {
            return;
        }

        Schema::create('thedolci_storefront_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_kicker')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_primary_button_text')->nullable();
            $table->string('hero_secondary_button_text')->nullable();
            $table->string('hero_metric_1_title')->nullable();
            $table->string('hero_metric_1_subtitle')->nullable();
            $table->string('hero_metric_2_title')->nullable();
            $table->string('hero_metric_2_subtitle')->nullable();
            $table->string('hero_metric_3_title')->nullable();
            $table->string('hero_metric_3_subtitle')->nullable();
            $table->string('hero_image_url', 2048)->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thedolci_storefront_settings');
    }
};
