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
        if (! Schema::hasTable('thedolci_storefront_settings')) {
            return;
        }

        Schema::table('thedolci_storefront_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('thedolci_storefront_settings', 'instagram_section_title')) {
                $table->string('instagram_section_title')->nullable()->after('hero_image_alt');
            }

            if (! Schema::hasColumn('thedolci_storefront_settings', 'instagram_handle')) {
                $table->string('instagram_handle')->nullable()->after('instagram_section_title');
            }

            if (! Schema::hasColumn('thedolci_storefront_settings', 'instagram_profile_url')) {
                $table->string('instagram_profile_url', 2048)->nullable()->after('instagram_handle');
            }

            if (! Schema::hasColumn('thedolci_storefront_settings', 'instagram_posts')) {
                $table->json('instagram_posts')->nullable()->after('instagram_profile_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('thedolci_storefront_settings')) {
            return;
        }

        Schema::table('thedolci_storefront_settings', function (Blueprint $table) {
            if (Schema::hasColumn('thedolci_storefront_settings', 'instagram_posts')) {
                $table->dropColumn('instagram_posts');
            }

            if (Schema::hasColumn('thedolci_storefront_settings', 'instagram_profile_url')) {
                $table->dropColumn('instagram_profile_url');
            }

            if (Schema::hasColumn('thedolci_storefront_settings', 'instagram_handle')) {
                $table->dropColumn('instagram_handle');
            }

            if (Schema::hasColumn('thedolci_storefront_settings', 'instagram_section_title')) {
                $table->dropColumn('instagram_section_title');
            }
        });
    }
};
