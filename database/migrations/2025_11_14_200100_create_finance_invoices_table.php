<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('workshop_booking_id')
                ->nullable()
                ->constrained('workshop_bookings')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('type', 20)->default('booking');
            $table->string('status', 20)->default('draft');
            $table->string('currency', 3)->default(config('finance.default_currency', 'JOD'));
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'currency']);
            $table->index(['workshop_booking_id', 'status'], 'finance_invoices_booking_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_invoices');
    }
};

