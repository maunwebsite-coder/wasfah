<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PolicyConsentController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->requiresPolicyConsent()) {
            return $this->redirectAfterConsent($request, $user);
        }

        return view('auth.policy-consent', [
            'user' => $user,
            'termsUrl' => $this->resolveLegalUrl('legal.terms_url', 'legal.terms'),
            'privacyUrl' => $this->resolveLegalUrl('legal.privacy_url', 'legal.privacy'),
            'policyVersion' => config('legal.policies_version'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->requiresPolicyConsent()) {
            return $this->redirectAfterConsent($request, $user);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'accept_terms' => ['accepted'],
        ], [
            'name.required' => 'يرجى إدخال اسمك الكامل.',
            'accept_terms.accepted' => 'يجب الموافقة على الشروط وسياسة الخصوصية للمتابعة.',
        ]);

        $user->forceFill([
            'name' => $data['name'],
            'policies_accepted_at' => now(),
            'policies_accepted_ip' => $request->ip(),
            'policies_version' => config('legal.policies_version'),
        ])->save();

        return $this->redirectAfterConsent($request, $user);
    }

    /**
     * Redirect user to the right page once consent is recorded (or already present).
     */
    private function redirectAfterConsent(Request $request, User $user): RedirectResponse
    {
        $request->session()->forget('url.intended');

        $message = $request->session()->pull(
            'pending_policy_success_message',
            'تم تسجيل دخولك بنجاح! استمتع بتجربتك في وصفة.'
        );

        $requiresOnboarding = (bool) $request->session()->pull('pending_policy_requires_onboarding', false);

        if ($requiresOnboarding) {
            return redirect()
                ->route('onboarding.show')
                ->with('success', $message);
        }

        $pendingWorkshopId = $request->session()->pull('pending_workshop_booking');

        if ($pendingWorkshopId) {
            $workshop = Workshop::find($pendingWorkshopId);

            if ($workshop) {
                return redirect()
                    ->route('workshop.show', $workshop->slug)
                    ->with('success', $message);
            }
        }

        return redirect()->route('home')
            ->with('success', $message);
    }

    /**
     * Resolve the public URL for a policy page, falling back to a relative route when needed.
     */
    private function resolveLegalUrl(string $configKey, string $routeName): string
    {
        $configuredUrl = trim((string) config($configKey));

        if ($configuredUrl !== '') {
            // Allow relative paths in config while still producing a full, base-aware URL
            if (Str::startsWith($configuredUrl, ['http://', 'https://'])) {
                return $configuredUrl;
            }

            return url($configuredUrl);
        }

        // Use absolute route to respect app URL/base path in all environments
        return route($routeName);
    }
}
