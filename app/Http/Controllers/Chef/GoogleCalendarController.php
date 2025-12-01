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
use Google\Client;
use Google\Service\Calendar\Event;

class GoogleCalendarController extends Controller
{
    // Redirect the user to Google for authentication and consent
    public function redirect(Request $request): RedirectResponse
    {
        $this->authorizeChef();

        // Get the redirect URL after the Google Calendar authentication
        $redirectTo = $request->input('redirect') ?: url()->previous() ?: route('chef.workshops.create');
        session(['google_calendar_redirect' => $redirectTo]);

        // Redirect to Google OAuth for Google Calendar permission
        return Socialite::driver('google')
            ->scopes([Calendar::CALENDAR_EVENTS, 'openid', 'profile', 'email'])  // Scopes to access user's Google Calendar
            ->with([
                'access_type' => 'offline', // Access type to get refresh token
                'prompt' => 'consent', // Always ask for consent
                'include_granted_scopes' => 'true', // Include previously granted scopes
            ])
            ->redirectUrl(route('chef.google.calendar.callback'))  // Callback URL after authentication
            ->redirect();
    }

    // Callback after the user has authenticated with Google
    public function callback(Request $request): RedirectResponse
    {
        $this->authorizeChef();
        $user = Auth::user();
        $redirectTo = session('google_calendar_redirect', route('chef.workshops.create')); // Redirect URL after success

        try {
            // Retrieve the authenticated user's data from Google
            $googleUser = Socialite::driver('google')
                ->stateless()  // Use stateless authentication (no session)
                ->redirectUrl(route('chef.google.calendar.callback'))
                ->user();
        } catch (\Throwable $exception) {
            // Log error if OAuth fails
            Log::warning('Google Calendar OAuth failed.', ['error' => $exception->getMessage()]);

            return redirect()
                ->to($redirectTo)
                ->with('error', 'تعذر ربط حساب Google Calendar. يرجى المحاولة مرة أخرى.');
        }

        // Get the refresh token (if available) for accessing Google Calendar in future requests
        $refreshToken = $googleUser->refreshToken
            ?? ($googleUser->accessTokenResponseBody['refresh_token'] ?? $user->google_calendar_refresh_token ?? $user->google_refresh_token);

        if (!$refreshToken) {
            return redirect()
                ->to($redirectTo)
                ->with('error', 'لم نحصل على صلاحية التقويم. يرجى الموافقة على خيار الوصول دون اتصال (Offline) عند ربط حساب Google.');
        }

        // Calculate token expiry
        $expiresIn = $googleUser->expiresIn ?? null;
        $tokenExpiresAt = $expiresIn ? now()->addSeconds((int) $expiresIn) : null;

        // Normalize and prepare scopes and id_token
        $scopes = $googleUser->accessTokenResponseBody['scope'] ?? null;
        $idToken = $googleUser->accessTokenResponseBody['id_token'] ?? null;
        $normalizedScopes = null;

        if (is_array($scopes)) {
            $normalizedScopes = implode(' ', $scopes);
        } elseif (is_string($scopes) && trim($scopes) !== '') {
            $normalizedScopes = trim($scopes);
        }

        // Update the user data with Google account details
        $user->forceFill([
            'google_email' => $user->google_email ?: $googleUser->getEmail(),
            'google_drive_email' => $user->google_drive_email ?: $googleUser->getEmail(),
            'google_calendar_email' => $user->google_calendar_email ?: $googleUser->getEmail(),
            'google_calendar_id' => $user->google_calendar_id ?: $googleUser->getEmail(),
            'google_calendar_access_token' => $googleUser->token ?: $user->google_calendar_access_token,
            'google_access_token' => $googleUser->token ?: $user->google_access_token,
            'provider_token' => $googleUser->token ?: $user->provider_token,
            'google_calendar_refresh_token' => $refreshToken ?: $user->google_calendar_refresh_token,
            'google_refresh_token' => $refreshToken ?: $user->google_refresh_token,
            'google_calendar_token_expires_at' => $tokenExpiresAt ?: $user->google_calendar_token_expires_at,
            'google_expires_at' => $tokenExpiresAt ?: $user->google_expires_at,
            'google_calendar_scopes' => $normalizedScopes ?: $user->google_calendar_scopes,
            'google_drive_scopes' => $normalizedScopes ?: $user->google_drive_scopes,
            'google_id_token' => $idToken ?: $user->google_id_token,
        ])->save();

        // Forget the redirect URL from the session after successful authentication
        session()->forget('google_calendar_redirect');

        return redirect($redirectTo)
            ->with('success', 'تم ربط Google Calendar بنجاح. يمكنك الآن إنشاء رابط الاجتماع من حسابك.');
    }

    // Create a new event in the specified Google Calendar
    public function createEvent(Request $request)
    {
        $client = new Client();
        $client->setAccessToken(Auth::user()->google_calendar_access_token);

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken(Auth::user()->google_calendar_refresh_token);
        }

        $calendarService = new Calendar($client);
        $calendarId = 'primary';  // The default calendar (or use a specific calendar ID for your site)

        // Create an event with additional metadata to identify it as created by your site
        $event = new Event([
            'summary' => $request->input('event_title'),
            'description' => "Created by MySite: " . $request->input('event_description'),  // Add site-specific identifier
            'start' => [
                'dateTime' => $request->input('start_datetime'),
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => $request->input('end_datetime'),
                'timeZone' => 'America/Los_Angeles',
            ],
            'extendedProperties' => [
                'private' => [
                    'created_by' => 'MySite',  // Store information about your site
                ],
            ],
        ]);

        // Insert the event into the specified calendar
        $createdEvent = $calendarService->events->insert($calendarId, $event);

        return response()->json(['event' => $createdEvent]);
    }

    // Get the list of events created by the site
    public function getEvents(Request $request)
    {
        $client = new Client();
        $client->setAccessToken(Auth::user()->google_calendar_access_token);

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken(Auth::user()->google_calendar_refresh_token);
        }

        $calendarService = new Calendar($client);
        $calendarId = 'primary';  // The default calendar (or use a specific calendar ID for your site)

        // Fetch events with a filter for 'created_by' in the extended properties
        $events = $calendarService->events->listEvents($calendarId, [
            'q' => 'created_by:MySite',  // Filter for events created by MySite
        ]);

        return response()->json(['events' => $events->getItems()]);
    }

    // Check if the current user has the appropriate role to access this functionality
    protected function authorizeChef(): void
    {
        $user = Auth::user();

        // If no user is authenticated, abort with 403 error
        if (!$user) {
            abort(403);
        }

        // Ensure the user is either an admin or a chef
        if (!$user->isAdmin() && $user->role !== User::ROLE_CHEF) {
            abort(403);
        }
    }
}
