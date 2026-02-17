<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Support\IntendedUrl;
use App\Services\ReferralProgramService;
use App\Support\NotificationCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    private const ALLOWED_FLOWS = [
        'login',
        'register_customer',
        'register_chef',
    ];

    public function __construct(
        protected ReferralProgramService $referrals,
    ) {
    }

    /**
     * Redirect the user to Google's authentication page.
     */
    public function redirect(Request $request)
    {
        IntendedUrl::rememberFromRequest($request);

        // تخزين معرف الورشة في session إذا كان موجوداً
        $pendingWorkshopId = $request->input('pending_workshop_booking');
        if ($pendingWorkshopId) {
            session(['pending_workshop_booking' => $pendingWorkshopId]);
        }

        $flow = $request->input('flow', 'login');
        if (!in_array($flow, self::ALLOWED_FLOWS, true)) {
            $flow = 'login';
        }

        $intent = $request->input('intent', User::ROLE_CUSTOMER);
        if (!in_array($intent, [User::ROLE_CUSTOMER, User::ROLE_CHEF], true)) {
            $intent = User::ROLE_CUSTOMER;
        }

        session([
            'auth_login_intent' => $intent,
            'auth_login_flow' => $flow,
        ]);
        
        $redirectUrl = $this->googleRedirectUrl();

        return Socialite::driver('google')
            ->scopes($this->googleAuthScopes())
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request)
    {
        $stage = 'start';
        $socialUser = null;
        $redirectUrl = $this->googleRedirectUrl();

        try {
            $stage = 'fetch-social-user';
            $socialUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
            $normalizedSocialEmail = strtolower((string) $socialUser->getEmail());

            $stage = 'resolve-flow-and-intent';
            $flow = session('auth_login_flow', 'login');
            session()->forget('auth_login_flow');
            if (!in_array($flow, self::ALLOWED_FLOWS, true)) {
                $flow = 'login';
            }

            $intent = session('auth_login_intent', User::ROLE_CUSTOMER);
            session()->forget('auth_login_intent');

            if (!in_array($intent, [User::ROLE_CUSTOMER, User::ROLE_CHEF], true)) {
                $intent = User::ROLE_CUSTOMER;
            }

            // Check if user exists by email first
            $stage = 'find-existing-user';
            $existingUser = User::where('email', $socialUser->getEmail())->first();
            $isNewUser = false;
            $tokenPayload = $this->googleTokenPayload($socialUser, $existingUser);
            
            if ($existingUser) {
                // User exists, update their social login info
                $stage = 'update-existing-user';
                $updates = [
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                ];

                if ($normalizedSocialEmail !== '') {
                    if ($existingUser->role === User::ROLE_CHEF) {
                        $currentGoogle = strtolower((string) $existingUser->google_email);

                        if ($currentGoogle !== $normalizedSocialEmail) {
                            $updates['google_email'] = $normalizedSocialEmail;
                        }
                    } elseif (empty($existingUser->google_email)) {
                        $updates['google_email'] = $normalizedSocialEmail;
                    }
                }

                $existingUser->update(array_merge($updates, $tokenPayload));
                $user = $existingUser;
            } else {
                if ($flow === 'login') {
                    $stage = 'no-user-found-login-flow';
                    return redirect()->route('login')
                        ->with('error', 'لم نعثر على حساب مرتبط ببريدك الإلكتروني في وصفة. يرجى اختيار خيار إنشاء حساب جديد.');
                }

                // Create new user
                $stage = 'prepare-new-user-data:' . $flow;
                $newUserData = [
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'google_email' => $normalizedSocialEmail ?: $socialUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                    'password' => Hash::make(uniqid()), // Random password for social login users
                    'role' => $intent === User::ROLE_CHEF ? User::ROLE_CHEF : User::ROLE_CUSTOMER,
                ];

                if ($intent === User::ROLE_CHEF) {
                    $newUserData['chef_status'] = User::CHEF_STATUS_NEEDS_PROFILE;
                }

                $stage = 'create-new-user:' . $flow;
                $user = User::create(array_merge($newUserData, $tokenPayload));
                $isNewUser = true;

                $this->assignReferralPartner($request, $user);

                // إنشاء إشعارات ترحيبية للمستخدم الجديد بدون تعطيل عملية تسجيل الدخول في حال الفشل
                try {
                    $stage = 'create-welcome-notifications';
                    $this->createWelcomeNotifications($user);
                } catch (\Throwable $notificationException) {
                    \Log::warning('Failed to create welcome notifications', [
                        'user_id' => $user->id,
                        'error' => $notificationException->getMessage(),
                    ]);
                }
            }

            // Log the user in
            $stage = 'login-user:' . $flow;
            if (! $user->isAdmin()) {
                return redirect()->route('login')
                    ->with('error', 'تسجيل الدخول متاح حالياً للأدمن فقط.');
            }

            Auth::login($user);

            $successMessage = $this->successMessageFor($flow, $isNewUser);
            $onboardingMessage = 'مرحباً بك! نحتاج لبعض التفاصيل الإضافية لاعتمادك كشيف في وصفة.';
            $requiresOnboarding = $this->shouldRedirectToOnboarding($user);

            if ($user->requiresPolicyConsent()) {
                session([
                    'pending_policy_success_message' => $requiresOnboarding ? $onboardingMessage : $successMessage,
                    'pending_policy_requires_onboarding' => $requiresOnboarding,
                ]);

                return redirect()->route('policy-consent.show');
            }

            // Redirect to onboarding if profile incomplete
            $stage = 'redirect-onboarding-check:' . $flow;
            if ($requiresOnboarding) {
                return redirect()
                    ->route('onboarding.show')
                    ->with('success', $onboardingMessage);
            }

            // التحقق من وجود معرف ورشة محفوظ في session
            $stage = 'pending-workshop-check:' . $flow;
            $pendingWorkshopId = session('pending_workshop_booking');
            if ($pendingWorkshopId) {
                // مسح معرف الورشة من session
                session()->forget('pending_workshop_booking');

                $workshop = \App\Models\Workshop::find($pendingWorkshopId);
                if (!$workshop) {
                    return redirect('/')
                        ->with('info', 'تم تسجيل الدخول بنجاح، لكن لم يتم العثور على الورشة المطلوبة. يمكنك تصفح الورشات المتاحة الآن.');
                }

                $workshopMessage = $isNewUser
                    ? ($flow === 'register_chef'
                        ? 'تم إنشاء حساب شيف جديد بنجاح! يمكنك الآن متابعة الورشة المختارة.'
                        : 'تم إنشاء حساب جديد بنجاح! يمكنك الآن حجز الورشة.')
                    : ($flow === 'register_chef'
                        ? 'تم تسجيل الدخول بنجاح كشيف! يمكنك الآن متابعة الورشة المختارة.'
                        : 'تم تسجيل الدخول بنجاح! يمكنك الآن حجز الورشة.');

                return redirect()
                    ->route('workshop.show', $workshop->slug)
                    ->with('success', $workshopMessage);
            }

            // Redirect with appropriate message based on whether it's a new user or existing user
            return redirect()->intended('/')->with('success', $successMessage);

        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Google OAuth Error', [
                'stage' => $stage,
                'message' => $e->getMessage(),
                'email' => $socialUser ? $socialUser->getEmail() : null,
                'exception' => $e,
            ]);
            
            // Handle exceptions, e.g., redirect to login with an error message
            return redirect()->route('login')->with('error', 'حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * إنشاء إشعارات ترحيبية للمستخدم الجديد
     */
    private function createWelcomeNotifications(User $user)
    {
        [$title, $message] = NotificationCopy::welcome($user);

        Notification::createNotification(
            $user->id,
            'general',
            $title,
            $message,
            [
                'welcome' => true,
                'user_name' => $user->name,
                'action_url' => route('profile'),
            ]
        );
    }

    /**
     * Determine the appropriate success message based on flow context.
     */
    private function successMessageFor(string $flow, bool $isNewUser): string
    {
        if ($isNewUser) {
            return match ($flow) {
                'register_chef' => 'تم إنشاء حساب شيف جديد بنجاح! مرحباً بك في وصفة.',
                'register_customer' => 'تم إنشاء حساب مستخدم جديد بنجاح! مرحباً بك في وصفة.',
                default => 'تم إنشاء حساب جديد بنجاح! مرحباً بك في وصفة.',
            };
        }

        return match ($flow) {
            'register_chef' => 'تم تسجيل الدخول بنجاح! تم تحديث حسابك كشيف في وصفة.',
            'register_customer' => 'تم تسجيل الدخول بنجاح! حسابك في وصفة جاهز للاستخدام.',
            default => 'تم تسجيل الدخول بنجاح! مرحباً بك مرة أخرى في وصفة.',
        };
    }

    /**
     * Determine if user should complete onboarding.
     */
    private function shouldRedirectToOnboarding(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if (is_null($user->chef_status)) {
            return false;
        }

        if (!$user->hasCompletedChefProfile()) {
            return true;
        }

        return in_array($user->chef_status, [
            User::CHEF_STATUS_NEEDS_PROFILE,
            User::CHEF_STATUS_REJECTED,
        ], true);
    }

    private function assignReferralPartner(Request $request, User $user): void
    {
        if ($user->referrer_id) {
            return;
        }

        $referrer = $this->referrals->rememberedPartner($request);

        if ($referrer) {
            $this->referrals->assignReferrerIfNeeded($user, $referrer);
        }
    }

    private function googleAuthScopes(): array
    {
        return [
            'openid',
            'email',
            'profile',
        ];
    }

    private function googleRedirectUrl(): string
    {
        return config('services.google.redirect') ?: route('google.callback');
    }

    private function googleTokenPayload($googleUser, ?User $existingUser = null): array
    {
        $accessToken = $googleUser->token ?? ($googleUser->accessTokenResponseBody['access_token'] ?? null);
        $refreshToken = $googleUser->refreshToken
            ?? ($googleUser->accessTokenResponseBody['refresh_token'] ?? null);
        $idToken = $googleUser->accessTokenResponseBody['id_token'] ?? null;
        $expiresIn = $googleUser->expiresIn ?? ($googleUser->accessTokenResponseBody['expires_in'] ?? null);
        $tokenExpiresAt = $expiresIn ? now()->addSeconds((int) $expiresIn) : null;
        $scopes = $googleUser->accessTokenResponseBody['scope'] ?? null;

        $normalizedScopes = null;
        if (is_array($scopes)) {
            $normalizedScopes = implode(' ', $scopes);
        } elseif (is_string($scopes) && trim($scopes) !== '') {
            $normalizedScopes = trim($scopes);
        }

        $email = $googleUser->getEmail()
            ?: ($existingUser?->google_email ?? $existingUser?->email);
        $calendarEmail = $existingUser?->google_calendar_email ?: $email;

        $payload = [
            'google_drive_email' => $existingUser?->google_drive_email ?: $email,
        ];

        if ($accessToken) {
            $payload['google_access_token'] = $accessToken;
            $payload['google_calendar_access_token'] = $accessToken;
            $payload['provider_token'] = $accessToken;
        }

        if ($refreshToken) {
            $payload['google_refresh_token'] = $refreshToken;
            $payload['google_calendar_refresh_token'] = $refreshToken;
        }

        if ($tokenExpiresAt) {
            $payload['google_expires_at'] = $tokenExpiresAt;
            $payload['google_calendar_token_expires_at'] = $tokenExpiresAt;
        }

        if ($normalizedScopes) {
            $payload['google_drive_scopes'] = $normalizedScopes;
            $payload['google_calendar_scopes'] = $normalizedScopes;
        }

        if ($idToken) {
            $payload['google_id_token'] = $idToken;
        }

        if ($calendarEmail) {
            $payload['google_calendar_email'] = $calendarEmail;
            $payload['google_calendar_id'] = $existingUser?->google_calendar_id ?: $calendarEmail;
        }

        return $payload;
    }
}


