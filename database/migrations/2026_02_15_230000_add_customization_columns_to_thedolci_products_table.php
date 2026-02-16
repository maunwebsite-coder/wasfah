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
        if (! Schema::hasTable('thedolci_products')) {
            return;
        }

        Schema::table('thedolci_products', function (Blueprint $table) {
            if (! Schema::hasColumn('thedolci_products', 'pepper_price')) {
                $table->decimal('pepper_price', 10, 2)->default(0)->after('size_prices');
            }

            if (! Schema::hasColumn('thedolci_products', 'packaging_options')) {
                $table->json('packaging_options')->nullable()->after('pepper_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('thedolci_products')) {
            return;
        }

        Schema::table('thedolci_products', function (Blueprint $table) {
            if (Schema::hasColumn('thedolci_products', 'packaging_options')) {
                $table->dropColumn('packaging_options');
            }

            if (Schema::hasColumn('thedolci_products', 'pepper_price')) {
                $table->dropColumn('pepper_price');
            }
        });
    }
};
