<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThedolciOrder extends Model
{
    use HasFactory;

    protected $table = 'thedolci_orders';

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'fulfillment_type',
        'delivery_address',
        'delivery_date',
        'delivery_time',
        'coupon_code',
        'discount_amount',
        'subtotal',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'items',
        'loyalty_points_earned',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
