<?php

namespace App\Services;

use App\Models\ReferralCommission;
use App\Models\User;
use App\Models\WorkshopBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ReferralProgramService
{
    public const SESSION_KEY = 'referral.partner';

    protected ?User $cachedPartner = null;

    public function resolvePartnerByCode(?string $code): ?User
    {
        if (blank($code)) {
            return null;
        }

        return User::query()
            ->where('referral_code', $code)
            ->where('is_referral_partner', true)
            ->first();
    }

    public function rememberPartner(User $partner, Request $request): void
    {
        $data = [
            'id' => $partner->id,
            'code' => $partner->ensureReferralCode(),
            'captured_at' => now()->toIso8601String(),
        ];

        $request->session()->put(self::SESSION_KEY, $data);
        $this->cachedPartner = $partner;

        $cookieName = config('referrals.cookie_name', 'wasfah_ref');
        $minutes = config('referrals.cookie_lifetime_days', 30) * 24 * 60;

        Cookie::queue(cookie(
            $cookieName,
            $data['code'],
            $minutes,
            '/',
            config('session.domain'),
            (bool) config('session.secure', false),
            false
        ));
    }

    public function forgetPartner(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
        $this->cachedPartner = null;

        $cookieName = config('referrals.cookie_name', 'wasfah_ref');
        Cookie::queue(Cookie::forget($cookieName, '/', config('session.domain')));
    }

    public function rememberedPartner(Request $request): ?User
    {
        if ($this->cachedPartner) {
            return $this->cachedPartner;
        }

        $sessionData = $request->session()->get(self::SESSION_KEY);

        if (is_array($sessionData) && isset($sessionData['id'])) {
            $partner = User::query()
                ->where('id', $sessionData['id'])
                ->where('is_referral_partner', true)
                ->first();

            if ($partner) {
                return $this->cachedPartner = $partner;
            }
        }

        $cookieName = config('referrals.cookie_name', 'wasfah_ref');
        $cookieCode = $request->cookie($cookieName);

        if ($cookieCode) {
            $partner = $this->resolvePartnerByCode($cookieCode);
            if ($partner) {
                $this->rememberPartner($partner, $request);
                return $partner;
            }
        }

        return null;
    }

    public function assignReferrerIfNeeded(User $user, ?User $referrer): void
    {
        if (
            !$referrer
            || !$referrer->isReferralPartner()
            || $user->referrer_id
            || $user->id === $referrer->id
        ) {
            return;
        }

        $user->referrer_id = $referrer->id;
        $user->save();
    }

    public function handleBookingPaid(WorkshopBooking $booking): ?ReferralCommission
    {
        $booking->loadMissing('workshop.chef.referralPartner', 'user');
        $workshop = $booking->workshop;

        if (!$workshop || !$workshop->chef) {
            return null;
        }

        $referredChef = $workshop->chef;
        $partner = $referredChef->referralPartner;

        if (!$partner || !$partner->isReferralPartner()) {
            return null;
        }

        $bookingAmount = (float) ($booking->payment_amount ?? $workshop->price ?? 0);

        if ($bookingAmount <= 0) {
            return null;
        }

        $rate = (float) ($partner->referral_commission_rate ?? config('referrals.default_rate', 5));
        $commissionAmount = round($bookingAmount * ($rate / 100), 2);

        $commission = ReferralCommission::firstOrNew([
            'workshop_booking_id' => $booking->id,
        ]);

        $commission->fill([
            'referral_partner_id' => $partner->id,
            'referred_user_id' => $referredChef->id,
            'participant_user_id' => $booking->user_id,
            'workshop_id' => $workshop->id,
            'booking_amount' => $bookingAmount,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'currency' => $partner->referral_commission_currency
                ?? $workshop->currency
                ?? config('referrals.default_currency', 'JOD'),
        ]);

        if ($commission->status !== ReferralCommission::STATUS_PAID) {
            $commission->status = ReferralCommission::STATUS_READY;
            $commission->earned_at = now();
            $commission->paid_at = null;
        }

        $commission->cancelled_at = null;
        $commission->cancellation_reason = null;

        $commission->save();

        return $commission->fresh();
    }

    public function handleBookingPaymentReverted(WorkshopBooking $booking, string $reason = 'payment_reverted'): void
    {
        $commission = ReferralCommission::where('workshop_booking_id', $booking->id)->first();

        if (!$commission) {
            return;
        }

        if ($commission->status === ReferralCommission::STATUS_PAID) {
            return;
        }

        $commission->status = ReferralCommission::STATUS_CANCELLED;
        $commission->cancelled_at = now();
        $commission->cancellation_reason = $reason;
        $commission->save();
    }
}

