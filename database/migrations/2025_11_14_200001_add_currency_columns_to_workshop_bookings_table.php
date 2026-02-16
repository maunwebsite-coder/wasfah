<?php

use App\Support\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('workshop_bookings', 'payment_currency')) {
                $table->string('payment_currency', 3)
                    ->default(config('finance.default_currency', 'JOD'))
                    ->after('payment_amount');
            }

            if (! Schema::hasColumn('workshop_bookings', 'payment_exchange_rate')) {
                $table->decimal('payment_exchange_rate', 12, 6)
                    ->nullable()
                    ->after('payment_currency');
            }

            if (! Schema::hasColumn('workshop_bookings', 'payment_amount_usd')) {
                $table->decimal('payment_amount_usd', 10, 2)
                    ->nullable()
                    ->after('payment_exchange_rate');
            }
        });

        $defaultCurrency = Currency::default();

        DB::table('workshop_bookings')
            ->select('id', 'workshop_id', 'payment_amount', 'payment_amount_usd', 'payment_exchange_rate', 'payment_currency')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($defaultCurrency) {
                $workshopIds = collect($bookings)->pluck('workshop_id')->unique();
                $workshopCurrencies = DB::table('workshops')
                    ->whereIn('id', $workshopIds)
                    ->pluck('currency', 'id');

                foreach ($bookings as $booking) {
                    $currency = strtoupper($workshopCurrencies[$booking->workshop_id] ?? $defaultCurrency);
                    $exchangeRate = Currency::rateToUsd($currency);
                    $usdAmount = Currency::round(
                        $booking->payment_amount_usd ?? (($booking->payment_amount ?? 0) * $exchangeRate),
                        'USD'
                    );

                    DB::table('workshop_bookings')
                        ->where('id', $booking->id)
                        ->update([
                            'payment_currency' => $currency,
                            'payment_exchange_rate' => $exchangeRate,
                            'payment_amount_usd' => $usdAmount,
                        ]);
                }
            });

        DB::table('workshop_bookings')
            ->whereNull('payment_exchange_rate')
            ->update(['payment_exchange_rate' => 1.0]);

        DB::table('workshop_bookings')
            ->whereNull('payment_amount_usd')
            ->update(['payment_amount_usd' => DB::raw('payment_amount')]);
    }

    public function down(): void
    {
        Schema::table('workshop_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('workshop_bookings', 'payment_amount_usd')) {
                $table->dropColumn('payment_amount_usd');
            }

            if (Schema::hasColumn('workshop_bookings', 'payment_exchange_rate')) {
                $table->dropColumn('payment_exchange_rate');
            }

            if (Schema::hasColumn('workshop_bookings', 'payment_currency')) {
                $table->dropColumn('payment_currency');
            }
        });
    }
};

