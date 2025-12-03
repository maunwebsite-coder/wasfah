<?php

namespace App\Http\Controllers;

use Google\Service\Drive;
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

        // تقليل الأذونات لطلب إذن الوصول فقط للملفات التي يتم تحميلها عبر التطبيق
        return Socialite::driver('google')
            ->scopes([Drive::DRIVE_FILE]) // فقط للوصول إلى الملفات التي يتم تحميلها من خلال التطبيق
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
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
            // استلام بيانات المستخدم من جوجل بعد تسجيل الدخول
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

        // الحصول على refreshToken إذا كان موجودًا
        $refreshToken = $googleUser->refreshToken
            ?? ($googleUser->accessTokenResponseBody['refresh_token'] ?? $user->google_refresh_token);

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

        // التحقق من الأذونات التي تم منحها
        if (is_array($scopes)) {
            $normalizedScopes = implode(' ', $scopes);
        } elseif (is_string($scopes) && trim($scopes) !== '') {
            $normalizedScopes = trim($scopes);
        }

        // حفظ الرموز والمعلومات الضرورية في قاعدة البيانات
        $user->forceFill([
            'google_drive_email' => $googleUser->getEmail() ?: $user->google_drive_email,
            'google_access_token' => $googleUser->token ?: $user->google_access_token,
            'provider_token' => $googleUser->token ?: $user->provider_token,
            'google_refresh_token' => $refreshToken ?: $user->google_refresh_token,
            'google_expires_at' => $tokenExpiresAt ?: $user->google_expires_at,
            'google_drive_scopes' => $normalizedScopes ?: $user->google_drive_scopes,
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
