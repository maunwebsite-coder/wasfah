<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThedolciCoupon extends Model
{
    use HasFactory;

    protected $table = 'thedolci_coupons';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'max_discount',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'value' => 'decimal:2',
            'min_order' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isUsable(?CarbonInterface $now = null): bool
    {
        $now = $now ?? now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function discountForSubtotal(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            return min($subtotal, (float) $this->value);
        }

        $discount = $subtotal * ((float) $this->value / 100);

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return min($subtotal, $discount);
    }
}
