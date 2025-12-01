<?php

namespace App\Http\Controllers;

use Google\Service\Drive;
use Google\Service\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleDriveAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $user = $this->ensureAuthenticated();

        $redirectTo = $request->input('redirect') ?: url()->previous() ?: route('profile');
        session(['google_drive_redirect' => $redirectTo]);

        $redirectUrl = config('services.google_drive.redirect')
            ?: route('google.drive.callback');

        return Socialite::driver('google')
            ->scopes([Drive::DRIVE_FILE, Calendar::CALENDAR_EVENTS, 'openid', 'profile', 'email'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $user = $this->ensureAuthenticated();
        $redirectTo = session('google_drive_redirect', route('profile'));
        $redirectUrl = config('services.google_drive.redirect')
            ?: route('google.drive.callback');

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl($redirectUrl)
                ->user();
        } catch (\Throwable $exception) {
            Log::warning('Google Drive OAuth failed.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->to($redirectTo)
                ->with('error', 'تعذر ربط حساب Google Drive. يرجى المحاولة مرة أخرى.');
        }

        $refreshToken = $googleUser->refreshToken
            ?? ($googleUser->accessTokenResponseBody['refresh_token'] ?? $user->google_refresh_token ?? $user->google_calendar_refresh_token);

        if (! $refreshToken) {
            return redirect()
                ->to($redirectTo)
                ->with('error', 'لم نحصل على صلاحية Drive. يرجى الموافقة على خيار الوصول دون اتصال (Offline) عند ربط حساب Google.');
        }

        $expiresIn = $googleUser->expiresIn ?? null;
        $tokenExpiresAt = $expiresIn ? now()->addSeconds((int) $expiresIn) : null;
        $scopes = $googleUser->accessTokenResponseBody['scope'] ?? null;
        $idToken = $googleUser->accessTokenResponseBody['id_token'] ?? null;
        $normalizedScopes = null;

        if (is_array($scopes)) {
            $normalizedScopes = implode(' ', $scopes);
        } elseif (is_string($scopes) && trim($scopes) !== '') {
            $normalizedScopes = trim($scopes);
        }

        $user->forceFill([
            'google_drive_email' => $googleUser->getEmail() ?: $user->google_drive_email,
            'google_access_token' => $googleUser->token ?: $user->google_access_token,
            'google_calendar_access_token' => $googleUser->token ?: $user->google_calendar_access_token,
            'provider_token' => $googleUser->token ?: $user->provider_token,
            'google_refresh_token' => $refreshToken ?: $user->google_refresh_token,
            'google_calendar_refresh_token' => $refreshToken ?: $user->google_calendar_refresh_token,
            'google_expires_at' => $tokenExpiresAt ?: $user->google_expires_at,
            'google_calendar_token_expires_at' => $tokenExpiresAt ?: $user->google_calendar_token_expires_at,
            'google_drive_scopes' => $normalizedScopes ?: $user->google_drive_scopes,
            'google_calendar_scopes' => $normalizedScopes ?: $user->google_calendar_scopes,
            'google_calendar_email' => $user->google_calendar_email ?: $googleUser->getEmail(),
            'google_calendar_id' => $user->google_calendar_id ?: $googleUser->getEmail(),
            'google_id_token' => $idToken ?: $user->google_id_token,
        ])->save();

        session()->forget('google_drive_redirect');

        return redirect($redirectTo)
            ->with('success', 'تم ربط Google Drive بنجاح. يمكنك الآن اختيار ملفات الفيديو من حسابك.');
    }

    protected function ensureAuthenticated()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        return $user;
    }
}
