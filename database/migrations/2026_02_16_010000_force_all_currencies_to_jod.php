<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $targets = [
            ['table' => 'workshops', 'column' => 'currency'],
            ['table' => 'workshop_bookings', 'column' => 'payment_currency'],
            ['table' => 'booking_revenue_shares', 'column' => 'currency'],
            ['table' => 'finance_invoices', 'column' => 'currency'],
            ['table' => 'referral_commissions', 'column' => 'currency'],
            ['table' => 'users', 'column' => 'referral_commission_currency'],
        ];

        foreach ($targets as $target) {
            $table = $target['table'];
            $column = $target['column'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->where(function ($query) use ($column) {
                    $query->whereNull($column)
                        ->orWhere($column, '!=', 'JOD');
                })
                ->update([$column => 'JOD']);
        }
    }

    public function down(): void
    {
        // No rollback: currency unification is intentional.
    }
};
