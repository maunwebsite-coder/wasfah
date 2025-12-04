<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Services\WorkshopLinkSecurityService;
use App\Services\ReferralProgramService;
use App\Services\BookingFinancialService;
use App\Services\FinanceInvoiceService;
use App\Services\WorkshopMeetingAttendeeSyncService;
use App\Services\WorkshopRecordingAccessService;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class WorkshopBooking extends Model
{
    use HasFactory;

    public const FINANCIAL_STATUS_PENDING = 'pending';
    public const FINANCIAL_STATUS_DISTRIBUTED = 'distributed';
    public const FINANCIAL_STATUS_VOID = 'void';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->public_code)) {
                $booking->public_code = static::generateUniquePublicCode();
            }
        });

        static::saving(function ($booking) {
            static::ensureCurrencyAttributes($booking);
        });

        // عند إنشاء حجز جديد
        static::created(function ($booking) {
            if ($booking->status === 'confirmed') {
                $booking->workshop->increment('bookings_count');
                static::grantRecordingAccess($booking);
                static::dispatchRecordingAccessShare($booking);
            }

            app(FinanceInvoiceService::class)->syncFromBooking($booking);

            if ($booking->status === 'confirmed') {
                static::dispatchGoogleMeetSync($booking);
            }
        });

        // عند تحديث حالة الحجز
        static::updated(function ($booking) {
            $originalStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;
            $originalPaymentStatus = $booking->getOriginal('payment_status');
            $newPaymentStatus = $booking->payment_status;

            // إذا تغيرت الحالة من غير مؤكد إلى مؤكد
            if ($originalStatus !== 'confirmed' && $newStatus === 'confirmed') {
                $booking->workshop->increment('bookings_count');
            }
            // إذا تغيرت الحالة من مؤكد إلى غير مؤكد
            elseif ($originalStatus === 'confirmed' && $newStatus !== 'confirmed') {
                $booking->workshop->decrement('bookings_count');
            }

            if ($originalPaymentStatus !== $newPaymentStatus) {
                $referrals = app(ReferralProgramService::class);

                if ($newPaymentStatus === 'paid') {
                    $referrals->handleBookingPaid($booking);
                } elseif ($originalPaymentStatus === 'paid') {
                    $referrals->handleBookingPaymentReverted($booking);
                }

                app(FinanceInvoiceService::class)->syncFromBooking($booking);
            }

            if (
                $booking->wasChanged('payment_amount') ||
                $booking->wasChanged('payment_currency')
            ) {
                app(FinanceInvoiceService::class)->syncFromBooking($booking);
            }

            if (
                $booking->wasChanged('status') ||
                $booking->wasChanged('payment_status')
            ) {
                app(BookingFinancialService::class)->sync($booking);
            }

            if (
                $booking->wasChanged('status') ||
                $booking->wasChanged('user_id')
            ) {
                static::dispatchGoogleMeetSync($booking);
            }

            $statusBecameConfirmed = $originalStatus !== 'confirmed' && $newStatus === 'confirmed';
            $userChangedWhileConfirmed = $booking->wasChanged('user_id') && $newStatus === 'confirmed';

            if ($statusBecameConfirmed || $userChangedWhileConfirmed) {
                static::grantRecordingAccess($booking);
                static::dispatchRecordingAccessShare($booking);
            }

            // Revoke recording access if booking was cancelled
            if ($booking->wasChanged('status') && $newStatus === 'cancelled') {
                static::revokeRecordingAccess($booking);
            }
        });

        // عند حذف حجز
        static::deleted(function ($booking) {
            if ($booking->status === 'confirmed') {
                $booking->workshop->decrement('bookings_count');
                static::dispatchGoogleMeetSync($booking);
            }

            if ($booking->payment_status === 'paid') {
                app(ReferralProgramService::class)->handleBookingPaymentReverted($booking, 'booking_deleted');
            }

            app(BookingFinancialService::class)->cancelDistribution($booking, 'booking_deleted');

            if ($invoice = $booking->invoice()->first()) {
                app(FinanceInvoiceService::class)->void($invoice, 'booking_deleted');
            }
        });
    }

    protected $fillable = [
        'workshop_id',
        'user_id',
        'status',
        'booking_date',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_payload',
        'payment_amount',
        'payment_currency',
        'payment_exchange_rate',
        'payment_amount_usd',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'admin_notes',
        'public_code',
        'first_joined_at',
        'join_device_token',
        'join_device_fingerprint',
        'join_device_ip',
        'join_device_user_agent',
        'financial_status',
        'financial_split_at',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'first_joined_at' => 'datetime',
        'payment_amount' => 'decimal:2',
        'payment_exchange_rate' => 'decimal:6',
        'payment_amount_usd' => 'decimal:2',
        'payment_payload' => 'array',
        'financial_split_at' => 'datetime',
    ];

    // العلاقات
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revenueShares()
    {
        return $this->hasMany(BookingRevenueShare::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(FinanceInvoice::class, 'workshop_booking_id');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeForCurrency($query, string $currency)
    {
        return $query->where('payment_currency', strtoupper($currency));
    }

    // Accessors
    public function getIsConfirmedAttribute()
    {
        return $this->status === 'confirmed';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_status === 'paid';
    }

    public function getPaymentCurrencyLabelAttribute(): string
    {
        $meta = Currency::meta($this->payment_currency);

        return $meta['label'] ?? strtoupper((string) $this->payment_currency);
    }

    public function getIsWhatsappBookingAttribute(): bool
    {
        $notes = Str::lower((string) ($this->notes ?? ''));
        $paymentMethod = Str::lower((string) ($this->payment_method ?? ''));
        $configuredNotes = Str::lower((string) config('services.whatsapp_booking.notes', ''));

        if ($paymentMethod && Str::contains($paymentMethod, 'whatsapp')) {
            return true;
        }

        foreach (array_filter([$configuredNotes, 'whatsapp', 'واتساب']) as $keyword) {
            if ($keyword && Str::contains($notes, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected static function generateUniquePublicCode(int $length = 10): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $code = '';

            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('public_code', $code)->exists());

        return $code;
    }

    public function getSecureJoinUrlAttribute(): string
    {
        return app(WorkshopLinkSecurityService::class)->makeParticipantJoinUrl($this);
    }

    public function getSecureStatusUrlAttribute(): string
    {
        return app(WorkshopLinkSecurityService::class)->makeParticipantStatusUrl($this);
    }

    protected static function ensureCurrencyAttributes(self $booking): void
    {
        $currency = strtoupper($booking->payment_currency ?: optional($booking->workshop)->currency ?: Currency::default());

        if (! $booking->relationLoaded('workshop') && empty($booking->payment_currency)) {
            $workshop = Workshop::find($booking->workshop_id);
            if ($workshop) {
                $currency = strtoupper($workshop->currency ?: $currency);
                $booking->setRelation('workshop', $workshop);
            }
        }

        $rate = $booking->payment_exchange_rate ?: Currency::rateToUsd($currency);
        $booking->payment_currency = $currency;
        $booking->payment_exchange_rate = $rate;

        if (! is_null($booking->payment_amount)) {
            $booking->payment_amount_usd = Currency::round($booking->payment_amount * $rate, 'USD');
        }
    }

    protected static function dispatchGoogleMeetSync(self $booking): void
    {
        $booking->loadMissing('workshop');

        $workshop = $booking->workshop;

        if (!$workshop) {
            return;
        }

        app(WorkshopMeetingAttendeeSyncService::class)->sync($workshop);
    }

    /**
     * Ensure the attendee has recording access once their booking is confirmed.
     */
    protected static function grantRecordingAccess(self $booking): void
    {
        if ($booking->status !== 'confirmed' || ! $booking->workshop_id || ! $booking->user_id) {
            return;
        }

        $pivot = WorkshopUser::firstOrNew([
            'workshop_id' => $booking->workshop_id,
            'user_id' => $booking->user_id,
        ]);

        $pivot->status = $booking->status ?: ($pivot->status ?: 'confirmed');
        $pivot->has_recording_access = true;

        if (! $pivot->recording_unlocked_at) {
            $pivot->recording_unlocked_at = now();
        }

        $pivot->save();
    }

    protected static function dispatchRecordingAccessShare(self $booking): void
    {
        try {
            app(WorkshopRecordingAccessService::class)->shareWithAttendee($booking);
        } catch (\Throwable $exception) {
            Log::warning('Failed to share Drive access for workshop booking.', [
                'booking_id' => $booking->id,
                'workshop_id' => $booking->workshop_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Revoke recording access when booking is cancelled.
     */
    protected static function revokeRecordingAccess(self $booking): void
    {
        if (! $booking->workshop_id || ! $booking->user_id) {
            return;
        }

        // Update pivot table to revoke access
        $pivot = WorkshopUser::where([
            'workshop_id' => $booking->workshop_id,
            'user_id' => $booking->user_id,
        ])->first();

        if ($pivot) {
            $pivot->has_recording_access = false;
            $pivot->save();
        }

        // Note: Google Drive permission revocation would be handled here
        // For now, we're just updating the local database
        // Future enhancement: Call WorkshopRecordingAccessService to revoke Drive permissions
    }
}
