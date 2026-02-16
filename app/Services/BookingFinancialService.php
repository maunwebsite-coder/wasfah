<?php

namespace App\Services;

use App\Models\BookingRevenueShare;
use App\Models\ReferralCommission;
use App\Models\WorkshopBooking;
use App\Support\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingFinancialService
{
    protected ?string $activeCurrency = null;
    public function sync(WorkshopBooking $booking): void
    {
        $booking->loadMissing('workshop.chef');

        if ($this->eligibleForDistribution($booking)) {
            $this->distribute($booking);

            return;
        }

        $this->cancelDistribution($booking, $this->determineCancellationReason($booking));
    }

    public function distribute(WorkshopBooking $booking): void
    {
        if (! $this->eligibleForDistribution($booking)) {
            $this->cancelDistribution($booking, $this->determineCancellationReason($booking));
            return;
        }

        $booking->loadMissing('workshop.chef.referralPartner', 'revenueShares');

        $workshop = $booking->workshop;

        if (! $workshop || ! $workshop->chef) {
            Log::warning('Unable to distribute financials for booking without workshop/chef.', [
                'booking_id' => $booking->id,
                'workshop_id' => $booking->workshop_id,
            ]);

            $this->cancelDistribution($booking, 'missing_workshop');

            return;
        }

        $paymentAmount = (float) $booking->payment_amount;

        if ($paymentAmount <= 0) {
            $this->cancelDistribution($booking, 'zero_payment');
            return;
        }

        $currency = $booking->payment_currency
            ?: ($workshop->currency ?? config('finance.default_currency', 'JOD'));
        $this->activeCurrency = $currency;
        $metadataBase = [
            'payment_amount' => $paymentAmount,
            'currency' => $currency,
            'exchange_rate' => $booking->payment_exchange_rate,
        ];

        DB::transaction(function () use ($booking, $workshop, $paymentAmount, $currency, $metadataBase) {
            $shares = $this->calculateShares($booking, $paymentAmount);

            $this->storeShare($booking, BookingRevenueShare::TYPE_CHEF, [
                'recipient_id' => $workshop->chef->id,
                'percentage' => $shares['chef']['percent'],
                'amount' => $shares['chef']['amount'],
                'currency' => $currency,
                'meta' => $metadataBase + ['role' => 'chef'],
            ]);

            if ($shares['partner']['amount'] > 0 && $shares['partner']['recipient_id']) {
                $partnerMeta = $metadataBase + [
                    'role' => 'partner',
                    'referral_commission_id' => $shares['partner']['commission_id'],
                ];

                $this->storeShare($booking, BookingRevenueShare::TYPE_PARTNER, [
                    'recipient_id' => $shares['partner']['recipient_id'],
                    'percentage' => $shares['partner']['percent'],
                    'amount' => $shares['partner']['amount'],
                    'currency' => $currency,
                    'meta' => $partnerMeta,
                ]);
            } else {
                $this->markShareAsCancelled($booking, BookingRevenueShare::TYPE_PARTNER, 'no_partner_distribution');
            }

            $adminMeta = $metadataBase + ['role' => 'admin'];
            if (!empty($shares['admin']['waived'])) {
                $adminMeta['platform_fee_waived'] = true;
            }

            $this->storeShare($booking, BookingRevenueShare::TYPE_ADMIN, [
                'recipient_id' => null,
                'percentage' => $shares['admin']['percent'],
                'amount' => $shares['admin']['amount'],
                'currency' => $currency,
                'meta' => $adminMeta,
            ]);

            WorkshopBooking::withoutEvents(function () use ($booking) {
                $booking->forceFill([
                    'financial_status' => WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED,
                    'financial_split_at' => now(),
                ])->save();
            });

            $this->flushStatsCache();
        });
        $this->activeCurrency = null;
    }

    public function cancelDistribution(WorkshopBooking $booking, string $reason = 'status_changed'): void
    {
        DB::transaction(function () use ($booking, $reason) {
            $booking->loadMissing('revenueShares');

            $shares = $booking->revenueShares;
            $shouldVoid = in_array($reason, ['booking_deleted', 'payment_refunded', 'missing_workshop'], true);

            if ($shares->isEmpty()) {
                WorkshopBooking::withoutEvents(function () use ($booking, $shouldVoid) {
                    $booking->forceFill([
                        'financial_status' => $shouldVoid
                            ? WorkshopBooking::FINANCIAL_STATUS_VOID
                            : WorkshopBooking::FINANCIAL_STATUS_PENDING,
                        'financial_split_at' => null,
                    ])->save();
                });

                return;
            }

            $now = now();

            foreach ($shares as $share) {
                if ($share->status === BookingRevenueShare::STATUS_CANCELLED) {
                    continue;
                }

                $meta = $share->meta ?? [];
                $meta['cancel_reason'] = $reason;

                $share->forceFill([
                    'status' => BookingRevenueShare::STATUS_CANCELLED,
                    'cancelled_at' => $now,
                    'distributed_at' => null,
                    'meta' => $meta,
                ])->save();
            }

            $financialStatus = $shouldVoid
                ? WorkshopBooking::FINANCIAL_STATUS_VOID
                : WorkshopBooking::FINANCIAL_STATUS_PENDING;

            WorkshopBooking::withoutEvents(function () use ($booking, $financialStatus) {
                $booking->forceFill([
                    'financial_status' => $financialStatus,
                    'financial_split_at' => null,
                ])->save();
            });

            $this->flushStatsCache();
        });
    }

    protected function eligibleForDistribution(WorkshopBooking $booking): bool
    {
        return $booking->status === 'confirmed'
            && $booking->payment_status === 'paid'
            && (float) $booking->payment_amount > 0
            && $booking->workshop
            && $booking->workshop->chef;
    }

    protected function determineCancellationReason(WorkshopBooking $booking): string
    {
        if (! $booking->workshop || ! $booking->workshop->chef) {
            Log::warning('Skipping financial distribution due to missing workshop or chef.', [
                'booking_id' => $booking->id,
                'workshop_id' => $booking->workshop_id,
            ]);

            return 'missing_workshop';
        }

        return $booking->payment_status === 'refunded'
            ? 'payment_refunded'
            : 'status_changed';
    }

    protected function calculateShares(WorkshopBooking $booking, float $paymentAmount): array
    {
        $chefPercent = $this->sanitizePercent(
            config('finance.chef_share_percent', 70),
            config('finance.min_chef_percent', 40),
            config('finance.max_chef_percent', 90)
        );

        $partnerShare = $this->resolvePartnerShare($booking);
        $partnerPercent = $this->sanitizePercent($partnerShare['percent'], 0, 40);

        $shouldWaivePlatformFee = $this->shouldWaivePlatformFee($booking);

        if ($shouldWaivePlatformFee) {
            $chefPercent = max(0, 100 - $partnerPercent);
        } elseif ($chefPercent + $partnerPercent > 100) {
            $partnerPercent = max(0, 100 - $chefPercent);
        }

        $adminPercent = $shouldWaivePlatformFee
            ? 0.0
            : max(0, 100 - $chefPercent - $partnerPercent);

        $chefAmount = $this->roundAmount($paymentAmount * ($chefPercent / 100));
        $partnerAmount = $partnerPercent > 0
            ? $this->roundAmount($paymentAmount * ($partnerPercent / 100))
            : 0.0;

        if ($partnerShare['amount'] > 0) {
            $partnerAmount = $this->roundAmount($partnerShare['amount']);
        }

        if ($shouldWaivePlatformFee) {
            $chefAmount = $this->roundAmount($paymentAmount - $partnerAmount);
            $adminAmount = 0.0;
        } else {
            $adminAmount = $this->roundAmount($paymentAmount - $chefAmount - $partnerAmount);

            if ($adminAmount < 0) {
                $adminAmount = 0.0;
            }
        }

        return [
            'chef' => [
                'percent' => $chefPercent,
                'amount' => $chefAmount,
            ],
            'partner' => [
                'percent' => $partnerPercent,
                'amount' => $partnerAmount,
                'recipient_id' => $partnerShare['recipient_id'],
                'commission_id' => $partnerShare['commission_id'],
            ],
            'admin' => [
                'percent' => $adminPercent,
                'amount' => $adminAmount,
                'waived' => $shouldWaivePlatformFee,
            ],
        ];
    }

    protected function shouldWaivePlatformFee(WorkshopBooking $booking): bool
    {
        $chef = $booking->workshop?->chef;

        if (! $chef) {
            return false;
        }

        return $chef->shouldWaivePlatformFeeForOwnWorkshops();
    }

    protected function resolvePartnerShare(WorkshopBooking $booking): array
    {
        $commission = ReferralCommission::where('workshop_booking_id', $booking->id)->first();

        if (! $commission && $booking->payment_status === 'paid') {
            $commission = app(ReferralProgramService::class)->handleBookingPaid($booking);
        }

        if (! $commission || $commission->status === ReferralCommission::STATUS_CANCELLED) {
            $partner = optional($booking->workshop->chef)->referralPartner;

            return [
                'percent' => $partner?->referral_commission_rate ?? 0,
                'amount' => 0.0,
                'recipient_id' => $partner?->id,
                'commission_id' => null,
            ];
        }

        return [
            'percent' => (float) $commission->commission_rate,
            'amount' => (float) $commission->commission_amount,
            'recipient_id' => $commission->referral_partner_id,
            'commission_id' => $commission->id,
        ];
    }

    protected function storeShare(WorkshopBooking $booking, string $type, array $payload): void
    {
        $share = BookingRevenueShare::firstOrNew([
            'workshop_booking_id' => $booking->id,
            'recipient_type' => $type,
        ]);

        $meta = array_merge($share->meta ?? [], $payload['meta'] ?? []);

        $share->fill([
            'recipient_id' => $payload['recipient_id'] ?? null,
            'percentage' => $payload['percentage'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'status' => BookingRevenueShare::STATUS_DISTRIBUTED,
            'distributed_at' => now(),
            'cancelled_at' => null,
            'meta' => $meta,
        ]);

        $share->save();
    }

    protected function markShareAsCancelled(WorkshopBooking $booking, string $type, string $reason): void
    {
        $share = BookingRevenueShare::where('workshop_booking_id', $booking->id)
            ->where('recipient_type', $type)
            ->first();

        if (! $share) {
            return;
        }

        $meta = $share->meta ?? [];
        $meta['cancel_reason'] = $reason;

        $share->forceFill([
            'status' => BookingRevenueShare::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'distributed_at' => null,
            'meta' => $meta,
        ])->save();
    }

    protected function sanitizePercent(float $value, float $min = 0, float $max = 100): float
    {
        $value = (float) $value;

        return max($min, min($max, $value));
    }

    protected function roundAmount(float $value): float
    {
        return Currency::round($value, $this->activeCurrency);
    }

    protected function flushStatsCache(): void
    {
        cache()->forget('booking_stats');
    }
}

