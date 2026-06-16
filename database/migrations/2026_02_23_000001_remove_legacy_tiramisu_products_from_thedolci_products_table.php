<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('thedolci_products')) {
            return;
        }

        DB::table('thedolci_products')
            ->whereIn('slug', [
                'classic-tiramisu',
                'strawberry-tiramisu',
                'chocolate-tiramisu',
                'pistachio-espresso-tiramisu',
            ])
            ->delete();
    }

    public function down(): void
    {
        // No-op: deleted products are not recreated automatically.
    }
};
