<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /**
     * Supported countries for phone dialing codes.
     *
     * @var array<string, array{name: string, dial_code: string}>
     */
    private const COUNTRIES = [
        'AE' => ['name' => 'الإمارات العربية المتحدة', 'dial_code' => '+971'],
        'SA' => ['name' => 'المملكة العربية السعودية', 'dial_code' => '+966'],
        'KW' => ['name' => 'الكويت', 'dial_code' => '+965'],
        'QA' => ['name' => 'قطر', 'dial_code' => '+974'],
        'BH' => ['name' => 'البحرين', 'dial_code' => '+973'],
        'OM' => ['name' => 'سلطنة عُمان', 'dial_code' => '+968'],
        'JO' => ['name' => 'الأردن', 'dial_code' => '+962'],
        'EG' => ['name' => 'مصر', 'dial_code' => '+20'],
        'IQ' => ['name' => 'العراق', 'dial_code' => '+964'],
        'MA' => ['name' => 'المغرب', 'dial_code' => '+212'],
        'LB' => ['name' => 'لبنان', 'dial_code' => '+961'],
    ];

    /**
     * Display the onboarding form for newly authenticated users.
     */
    public function show(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->requiresOnboarding($user)) {
            if ($user->chef_status === User::CHEF_STATUS_PENDING) {
                return view('auth.onboarding-status', [
                    'user' => $user,
                ]);
            }

            $messageType = 'success';
            $messageText = 'ملفك الشخصي مكتمل بالفعل.';

            return redirect()
                ->intended($this->redirectPath($user))
                ->with($messageType, $messageText);
        }

        // Issue a fresh CSRF token + cookie to avoid stale tokens (419 errors) and prevent caching.
        $request->session()->regenerateToken();

        $response = response()
            ->view('auth.onboarding', [
                'user' => $user,
                'pendingWorkshopId' => session('pending_workshop_booking'),
                'countries' => $this->availableCountries(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

        $response->withCookie(
            cookie(
                'XSRF-TOKEN',
                csrf_token(),
                config('session.lifetime'),
                '/',
                config('session.domain'),
                (bool) config('session.secure', false),
                false,
                false,
                config('session.same_site', 'lax'),
                config('session.partitioned', false)
            )
        );

        return $response;
    }

    /**
     * Handle onboarding submission.
     */
    public function store(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $countries = $this->availableCountries();
        $countryCodes = array_keys($countries);

        $data = $request->validate([
            'country_code' => ['required', Rule::in($countryCodes)],
            'phone' => ['required', 'string', 'max:30'],
            'google_email' => ['required', 'email', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'chef_specialty_area' => ['nullable', 'in:food'],
            'chef_specialty_description' => ['nullable', 'string', 'max:2000'],
        ], [
            'country_code.required' => 'يرجى اختيار الدولة.',
            'country_code.in' => 'الدولة المختارة غير مدعومة حالياً.',
            'chef_specialty_area.in' => 'يجب أن يكون تخصصك الرئيسي في مجال الطعام والطبخ للانضمام كـ شيف.',
            'google_email.required' => 'يرجى إدخال بريد Google الذي ستستخدمه لاستضافة الورش.',
            'google_email.email' => 'صيغة بريد Google غير صحيحة.',
        ]);

        $countryCode = $data['country_code'];
        $dialCode = $countries[$countryCode]['dial_code'] ?? null;
        $fullPhone = $this->normalizePhoneNumber($dialCode, $data['phone']);

        if (!$fullPhone) {
            return back()
                ->withInput()
                ->withErrors([
                    'phone' => 'يرجى إدخال رقم جوال صالح.',
                ]);
        }

        $user->country_code = $countryCode;
        $user->phone_country_code = $this->ensureDialCodePrefix($dialCode ?? '');
        $user->phone = $fullPhone;
        $user->instagram_url = $data['instagram_url'] ?? null;
        $user->youtube_url = $data['youtube_url'] ?? null;
        $user->google_email = $data['google_email'];
        $user->chef_specialty_area = $data['chef_specialty_area'];
        $user->chef_specialty_description = $data['chef_specialty_description'];

        if (!$user->isAdmin()) {
            $user->role = User::ROLE_CHEF;
            $user->chef_status = User::CHEF_STATUS_APPROVED;
            $user->chef_approved_at = now();
        }

        $user->save();

        $finalRedirect = $this->redirectPath($user);

        return redirect()->route('chef.google.calendar.connect', ['redirect' => $finalRedirect])
            ->with('success', 'تهانينا! تم اعتمادك فوراً كشيف. يرجى ربط تقويم Google لإكمال الإعداد.');
    }

    private function requiresOnboarding(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if (is_null($user->chef_status)) {
            return true;
        }

        if (!$user->hasCompletedChefProfile()) {
            return true;
        }

        return in_array($user->chef_status, [
            User::CHEF_STATUS_NEEDS_PROFILE,
            User::CHEF_STATUS_REJECTED,
        ], true);
    }

    private function redirectPath(User $user): string
    {
        $pendingWorkshopId = session('pending_workshop_booking');
        if ($pendingWorkshopId) {
            $workshop = \App\Models\Workshop::find($pendingWorkshopId);
            if ($workshop) {
                session()->forget('pending_workshop_booking');
                return route('workshop.show', $workshop->slug);
            }
        }

        session()->forget('pending_workshop_booking');

        if ($user->isChef()) {
            return route('chef.recipes.index');
        }

        return route('home');
    }

    /**
     * @return array<string, array{name: string, dial_code: string}>
     */
    private function availableCountries(): array
    {
        return self::COUNTRIES;
    }

    private function normalizePhoneNumber(?string $dialCode, string $number): ?string
    {
        if (!$dialCode) {
            return null;
        }

        $normalizedDialCode = $this->ensureDialCodePrefix($dialCode);
        $digits = preg_replace('/\D+/', '', $number) ?? '';
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return null;
        }

        return $normalizedDialCode . $digits;
    }

    private function ensureDialCodePrefix(string $dialCode): string
    {
        $cleanDialCode = preg_replace('/[^0-9+]/', '', $dialCode) ?? '';

        return str_starts_with($cleanDialCode, '+')
            ? $cleanDialCode
            : '+' . ltrim($cleanDialCode, '+');
    }
}
