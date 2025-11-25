<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Service\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleCalendarController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $this->authorizeChef();

        $redirectTo = $request->input('redirect') ?: url()->previous() ?: route('chef.workshops.create');
        session(['google_calendar_redirect' => $redirectTo]);

        return Socialite::driver('google')
            ->scopes([Calendar::CALENDAR_EVENTS, 'openid', 'profile', 'email'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirectUrl(route('chef.google.calendar.callback'))
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->authorizeChef();
        $user = Auth::user();
        $redirectTo = session('google_calendar_redirect', route('chef.workshops.create'));

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(route('chef.google.calendar.callback'))
                ->user();
        } catch (\Throwable $exception) {
            Log::warning('Google Calendar OAuth failed.', ['error' => $exception->getMessage()]);

            return redirect()
                ->to($redirectTo)
                ->with('error', 'تعذر ربط حساب Google Calendar. يرجى المحاولة مرة أخرى.');
        }

        $refreshToken = $googleUser->refreshToken
            ?? ($googleUser->accessTokenResponseBody['refresh_token'] ?? null);

        if (!$refreshToken) {
            return redirect()
                ->to($redirectTo)
                ->with('error', 'لم نحصل على صلاحية التقويم. يرجى الموافقة على خيار الوصول دون اتصال (Offline) عند ربط حساب Google.');
        }

        $expiresIn = $googleUser->expiresIn ?? null;
        $tokenExpiresAt = $expiresIn ? now()->addSeconds((int) $expiresIn) : null;
        $scopes = $googleUser->accessTokenResponseBody['scope'] ?? null;

        $user->forceFill([
            'google_email' => $user->google_email ?: $googleUser->getEmail(),
            'google_calendar_email' => $googleUser->getEmail(),
            'google_calendar_id' => $googleUser->getEmail(),
            'google_calendar_access_token' => $googleUser->token,
            'google_calendar_refresh_token' => $refreshToken,
            'google_calendar_token_expires_at' => $tokenExpiresAt,
            'google_calendar_scopes' => is_array($scopes) ? implode(' ', $scopes) : (string) $scopes,
        ])->save();

        session()->forget('google_calendar_redirect');

        return redirect($redirectTo)
            ->with('success', 'تم ربط Google Calendar بنجاح. يمكنك الآن إنشاء رابط الاجتماع من حسابك.');
    }

    protected function authorizeChef(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if (!$user->isAdmin() && $user->role !== User::ROLE_CHEF) {
            abort(403);
        }
    }
}
