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
        Schema::create('thedolci_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->enum('fulfillment_type', ['delivery', 'pickup'])->default('delivery');
            $table->text('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['card', 'apple_pay', 'cash_on_delivery'])->default('card');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('status', ['new', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed', 'cancelled'])->default('new');
            $table->json('items');
            $table->text('notes')->nullable();
            $table->unsignedInteger('loyalty_points_earned')->default(0);
            $table->timestamps();

            $table->index(['customer_email', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thedolci_orders');
    }
};
