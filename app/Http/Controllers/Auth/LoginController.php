<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle traditional email/password login.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => __('auth.validation.email_required'),
                'email.email' => __('auth.validation.email_email'),
                'password.required' => __('auth.validation.password_required'),
            ]
        );

        $remember = $request->boolean('remember');

        if ($request->filled('pending_workshop_booking')) {
            session(['pending_workshop_booking' => $request->input('pending_workshop_booking')]);
        }

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.validation.credentials'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        $this->updateLoginMetadata($user, $request);

        if ($this->shouldRedirectToOnboarding($user)) {
            return redirect()->route('onboarding.show');
        }

        if ($pendingWorkshop = session('pending_workshop_booking')) {
            session()->forget('pending_workshop_booking');

            $workshop = Workshop::find($pendingWorkshop);
            if ($workshop) {
                return redirect()
                    ->route('workshop.show', $workshop->slug)
                    ->with('success', __('auth.flash.workshop_success'));
            }
        }

        return redirect()->intended('/')
            ->with('success', __('auth.flash.login_success'));
    }

    /**
     * Determine whether the logged-in user must finish onboarding.
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

    /**
     * Persist login metadata only when the underlying columns exist.
     */
    private function updateLoginMetadata(User $user, Request $request): void
    {
        static $userColumns;

        if ($userColumns === null) {
            $userColumns = Schema::connection($user->getConnectionName())
                ->getColumnListing($user->getTable());
        }

        $data = [];

        if (in_array('last_login_at', $userColumns, true)) {
            $data['last_login_at'] = now();
        }

        if (in_array('last_login_ip', $userColumns, true)) {
            $data['last_login_ip'] = $request->ip();
        }

        if (!empty($data)) {
            $user->forceFill($data)->save();
        }
    }
}
