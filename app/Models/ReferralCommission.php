<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'referral_partner_id',
        'referred_user_id',
        'participant_user_id',
        'workshop_id',
        'workshop_booking_id',
        'booking_amount',
        'commission_rate',
        'commission_amount',
        'currency',
        'status',
        'earned_at',
        'paid_at',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'booking_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'earned_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function referralPartner()
    {
        return $this->belongsTo(User::class, 'referral_partner_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function participant()
    {
        return $this->belongsTo(User::class, 'participant_user_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function booking()
    {
        return $this->belongsTo(WorkshopBooking::class, 'workshop_booking_id');
    }

    public function scopeForPartner($query, int $partnerId)
    {
        return $query->where('referral_partner_id', $partnerId);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function markPaid(?string $notes = null): void
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        if ($notes) {
            $this->notes = trim($notes);
        }
        $this->save();
    }

    public function cancel(string $reason): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->save();
    }

    public function getCurrencySymbolAttribute(): string
    {
        $currency = $this->currency ?: (string) config('referrals.default_currency', 'JOD');

        return data_get(config('referrals.currencies', []), "{$currency}.symbol", $currency);
    }
}

