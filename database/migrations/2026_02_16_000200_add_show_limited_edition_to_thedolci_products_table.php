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
            if (! Schema::hasColumn('thedolci_products', 'show_limited_edition')) {
                $table->boolean('show_limited_edition')->default(false)->after('is_seasonal');
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
            if (Schema::hasColumn('thedolci_products', 'show_limited_edition')) {
                $table->dropColumn('show_limited_edition');
            }
        });
    }
};

