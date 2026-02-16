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
        Schema::create('booking_revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_booking_id')
                ->constrained('workshop_bookings')
                ->cascadeOnDelete();
            $table->enum('recipient_type', ['chef', 'partner', 'admin']);
            $table->foreignId('recipient_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default(config('finance.default_currency', 'JOD'));
            $table->enum('status', ['pending', 'distributed', 'cancelled'])->default('pending');
            $table->timestamp('distributed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(
                ['workshop_booking_id', 'recipient_type'],
                'booking_revenue_shares_booking_role_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_revenue_shares');
    }
};

