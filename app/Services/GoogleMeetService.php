<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventOrganizer;
use Google\Service\Calendar\EventCreator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleMeetService
{
    protected ?Calendar $calendar = null;
    protected ?string $calendarId = null;
    protected int $defaultDuration;
    protected string $timezone;
    protected ?string $organizerEmail;
    protected bool $enabled = false;
    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $refreshToken = null;

    public function __construct(?Client $client = null)
    {
        $config = config('services.google_meet', []);

        $clientId = $config['client_id'] ?? null;
        $clientSecret = $config['client_secret'] ?? null;
        $refreshToken = $config['refresh_token'] ?? null;
        $calendarId = $config['calendar_id'] ?? null;
        $this->organizerEmail = $config['organizer_email'] ?? null;
        // Default to 45 minutes if no value is provided in configuration.
        $this->defaultDuration = max(30, (int) ($config['default_duration'] ?? 45));
        $this->timezone = $config['timezone'] ?? config('app.timezone', 'UTC');
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;

        $credentialsReady = $clientId && $clientSecret && $refreshToken;
        $calendarReady = $calendarId || $this->organizerEmail;

        if ($credentialsReady && $calendarReady) {
            $this->calendarId = $calendarId ?: $this->organizerEmail;

            try {
                $this->calendar = $this->bootstrapCalendarClient($client);
                $this->enabled = true;
            } catch (\Throwable $exception) {
                $this->enabled = false;
                $this->calendar = null;

                Log::error('Failed to initialize Google Meet calendar client.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalize Google credentials, preferring per-host overrides when provided.
     */
    protected function normalizeCredentials(?array $overrides = null): array
    {
        $clientId = $overrides['client_id'] ?? $this->clientId;
        $clientSecret = $overrides['client_secret'] ?? $this->clientSecret;
        $refreshToken = $overrides['refresh_token'] ?? $this->refreshToken;
        $organizerEmail = $overrides['organizer_email'] ?? $this->organizerEmail;
        $calendarId = $overrides['calendar_id'] ?? $this->calendarId ?? $organizerEmail;
        $timezone = $overrides['timezone'] ?? $this->timezone;
        $defaultDuration = max(15, (int) ($overrides['default_duration'] ?? $this->defaultDuration));

        $enabled = (bool) ($clientId && $clientSecret && $refreshToken && $calendarId);

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'calendar_id' => $calendarId,
            'organizer_email' => $organizerEmail,
            'timezone' => $timezone,
            'default_duration' => $defaultDuration,
            'enabled' => $enabled,
        ];
    }

    /**
     * Resolve a Calendar service using either global or host-specific credentials.
     *
     * @return array{0:Calendar|null,1:array}
     */
    protected function resolveCalendarService(?array $credentialsOverride = null): array
    {
        $credentials = $this->normalizeCredentials($credentialsOverride);

        if (!$credentials['enabled']) {
            return [null, $credentials];
        }

        if ($credentialsOverride === null && $this->calendar) {
            return [$this->calendar, $credentials];
        }

        $client = $this->refreshAccessToken($credentials);



        $service = new Calendar($client);

        if ($credentialsOverride === null) {
            $this->calendar = $service;
        }

        return [$service, $credentials];
    }

    public function createMeeting(
        string $title,
        int $userId = 0,
        ?CarbonInterface $startsAt = null,
        ?int $durationMinutes = null,
        ?string $description = null,
        array $attendees = [],
        ?array $organizerOverride = null,
        ?array $credentialsOverride = null
    ): array {
        [$service, $credentials] = $this->resolveCalendarService($credentialsOverride);

        if (!$service || empty($credentials['calendar_id'])) {
            if (empty($credentials['calendar_id'])) {
                throw new RuntimeException('Google Meet integration error: Missing Calendar ID.');
            }

            if (!$credentials['enabled']) {
                $missing = [];
                if (empty($credentials['client_id'])) $missing[] = 'Client ID';
                if (empty($credentials['client_secret'])) $missing[] = 'Client Secret';
                if (empty($credentials['refresh_token'])) $missing[] = 'Refresh Token';
                throw new RuntimeException('Google Meet integration error: Missing credentials (' . implode(', ', $missing) . ').');
            }

            throw new RuntimeException('Google Meet integration error: Failed to refresh access token. Please reconnect your Google Calendar.');
        }

        $start = $startsAt?->copy() ?? now();
        $start = $start->setTimezone($credentials['timezone']);
        $duration = max(15, (int) ($durationMinutes ?? $credentials['default_duration']));
        $end = $start->copy()->addMinutes($duration);

        $eventPayload = [
            'summary' => trim($title) !== '' ? $title : 'Wasfah Online Workshop',
            'description' => $description ?: sprintf('Auto-generated for Wasfah workshop #%s', $userId ?: 'N/A'),
            'start' => $this->makeEventDateTime($start, $credentials['timezone']),
            'end' => $this->makeEventDateTime($end, $credentials['timezone']),
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => (string) Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ];

        $event = new Event($eventPayload);

        $attendeePayload = [];

        if ($credentials['organizer_email']) {
            $attendeePayload[$credentials['organizer_email']] = [
                'email' => $credentials['organizer_email'],
                'organizer' => true,
            ];
        }

        foreach ($attendees as $attendee) {
            if (!is_array($attendee)) {
                continue;
            }

            $email = isset($attendee['email']) ? trim((string) $attendee['email']) : '';

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $payload = [
                'email' => strtolower($email),
            ];

            if (!empty($attendee['displayName'])) {
                $payload['displayName'] = (string) $attendee['displayName'];
            }

            if (array_key_exists('organizer', $attendee)) {
                $payload['organizer'] = (bool) $attendee['organizer'];
            }

            $attendeePayload[$payload['email']] = $payload;
        }

        if (!empty($attendeePayload)) {
            $event->setAttendees(array_values($attendeePayload));
        }

        if ($organizerOverride) {
            $organizerEmail = isset($organizerOverride['email'])
                ? strtolower(trim((string) $organizerOverride['email']))
                : null;

            if ($organizerEmail && filter_var($organizerEmail, FILTER_VALIDATE_EMAIL)) {
                $displayName = $organizerOverride['displayName'] ?? null;

                $organizer = new EventOrganizer();
                $organizer->setEmail($organizerEmail);
                if ($displayName) {
                    $organizer->setDisplayName($displayName);
                }
                $event->setOrganizer($organizer);

                $creator = new EventCreator();
                $creator->setEmail($organizerEmail);
                if ($displayName) {
                    $creator->setDisplayName($displayName);
                }
                $event->setCreator($creator);
            }
        }

        try {
            $created = $service->events->insert(
                $credentials['calendar_id'],
                $event,
                ['conferenceDataVersion' => 1, 'sendUpdates' => 'none']
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to create Google Meet event.', [
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('تعذر إنشاء اجتماع Google Meet: ' . $exception->getMessage());
        }

        $hangoutLink = $created->getHangoutLink();

        if (!$hangoutLink) {
            $conferenceData = $created->getConferenceData();
            if ($conferenceData) {
                $entryPoints = $conferenceData->getEntryPoints() ?? [];
                if (is_array($entryPoints) && isset($entryPoints[0])) {
                    $hangoutLink = $entryPoints[0]->getUri();
                }
            }
        }

        if (!$hangoutLink) {
            throw new RuntimeException('لم يتمكن Google من إنشاء رابط اجتماع صالح.');
        }

        return [
            'meeting_link' => $hangoutLink,
            'event_id' => $created->getId(),
            'calendar_id' => $credentials['calendar_id'],
            'conference_id' => optional($created->getConferenceData())->getConferenceId(),
            'provider' => 'google_meet',
            'starts_at' => $start,
            'ends_at' => $end,
        ];
    }

    public function refreshAccessToken(?array $credentials = null): ?Client
    {
        $creds = $this->normalizeCredentials($credentials);

        if (!$creds['client_id'] || !$creds['client_secret'] || !$creds['refresh_token']) {
            return null;
        }

        try {
            $googleClient = $this->buildConfiguredClient(null, $creds);
            $token = $googleClient->fetchAccessTokenWithRefreshToken($creds['refresh_token']);

            if (isset($token['error'])) {
                $error = $token['error'];
                $desc = $token['error_description'] ?? 'No description';
                Log::warning('Failed to refresh Google Meet access token.', [
                    'error' => $error,
                    'error_description' => $desc,
                ]);

                throw new RuntimeException("Google Token Error: $error ($desc)");
            }

            if (is_array($token) && !empty($token)) {
                $googleClient->setAccessToken($token);
            }

            return $googleClient;
        } catch (\Throwable $exception) {
            Log::error('Failed to refresh Google Meet token automatically.', [
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Token refresh failed: ' . $exception->getMessage());
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Replace the attendee list for an existing Google Calendar event.
     *
     * @param  string  $eventId
     * @param  array<array{email:string,displayName?:string,optional?:bool,organizer?:bool}>  $attendees
     * @param  string|null  $calendarId
     */
    public function syncEventAttendees(string $eventId, array $attendees, ?string $calendarId = null, ?array $credentialsOverride = null): void
    {
        $eventId = trim($eventId);

        if ($eventId === '') {
            throw new RuntimeException('لا يمكن تحديث ضيوف Google Meet بدون معرف اجتماع صالح.');
        }

        [$service, $credentials] = $this->resolveCalendarService($credentialsOverride);

        $targetCalendar = $calendarId ?: $credentials['calendar_id'];

        if (!$service || !$targetCalendar) {
            throw new RuntimeException('لا يوجد تقويم Google صالح لإرسال تحديثات الضيوف.');
        }

        $attendeeObjects = $this->buildEventAttendees($attendees);
        $event = new Event();
        $event->setAttendees($attendeeObjects);

        $service->events->patch(
            $targetCalendar,
            $eventId,
            $event,
            [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'none',
            ]
        );
    }

    protected function bootstrapCalendarClient(?Client $client = null, ?array $credentials = null): Calendar
    {
        $creds = $this->normalizeCredentials($credentials);

        if (!$creds['client_id'] || !$creds['client_secret'] || !$creds['refresh_token']) {
            throw new RuntimeException('بيانات اعتماد Google Meet غير مكتملة.');
        }

        $googleClient = $this->buildConfiguredClient($client, $creds);
        $token = $googleClient->fetchAccessTokenWithRefreshToken($creds['refresh_token']);

        if (isset($token['error'])) {
            throw new RuntimeException('فشل تحديث صلاحيات Google: ' . ($token['error_description'] ?? $token['error']));
        }

        if (is_array($token) && !empty($token)) {
            $googleClient->setAccessToken($token);
        }

        return new Calendar($googleClient);
    }

    protected function buildConfiguredClient(?Client $client = null, ?array $credentials = null): Client
    {
        $googleClient = $client ?: new Client();
        $googleClient->setApplicationName(config('app.name') . ' Workshops');

        $creds = $this->normalizeCredentials($credentials);

        if ($creds['client_id']) {
            $googleClient->setClientId($creds['client_id']);
        }

        if ($creds['client_secret']) {
            $googleClient->setClientSecret($creds['client_secret']);
        }

        $googleClient->setAccessType('offline');
        $googleClient->setPrompt('consent');
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->setScopes([
            Calendar::CALENDAR_EVENTS,
        ]);

        return $googleClient;
    }

    protected function makeEventDateTime(CarbonInterface $time, ?string $timezone = null): EventDateTime
    {
        $dateTime = new EventDateTime();
        $dateTime->setDateTime($time->toRfc3339String());
        $dateTime->setTimeZone($timezone ?? $this->timezone);

        return $dateTime;
    }

    /**
     * @param  array<array{email?:string,displayName?:string,name?:string,optional?:bool,organizer?:bool}>  $attendees
     * @return EventAttendee[]
     */
    protected function buildEventAttendees(array $attendees): array
    {
        $result = [];

        foreach ($attendees as $attendee) {
            $email = isset($attendee['email']) ? trim((string) $attendee['email']) : '';

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $payload = [
                'email' => $email,
            ];

            $displayName = $attendee['displayName'] ?? $attendee['name'] ?? null;

            if (is_string($displayName) && trim($displayName) !== '') {
                $payload['displayName'] = trim($displayName);
            }

            if (isset($attendee['optional'])) {
                $payload['optional'] = (bool) $attendee['optional'];
            }

            if (isset($attendee['organizer'])) {
                $payload['organizer'] = (bool) $attendee['organizer'];
            }

            $result[] = new EventAttendee($payload);
        }

        return $result;
    }

    public function eventHasAttendee(string $eventId, string $email, ?string $calendarId = null, ?array $credentialsOverride = null): ?bool
    {
        [$service, $credentials] = $this->resolveCalendarService($credentialsOverride);

        $targetCalendar = $calendarId ?: $credentials['calendar_id'];

        if (!$service || !$targetCalendar) {
            return null;
        }

        try {
            $event = $service->events->get($targetCalendar, $eventId);
        } catch (\Throwable $exception) {
            Log::warning('Failed to fetch Google Meet event for attendee verification.', [
                'event_id' => $eventId,
                'calendar_id' => $targetCalendar,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (!$service || !$event) {
            return false;
        }

        $attendees = $event->getAttendees() ?? [];

        foreach ($attendees as $attendee) {
            $attendeeEmail = strtolower((string) $attendee->getEmail());

            if ($attendeeEmail !== '' && hash_equals($attendeeEmail, strtolower($email))) {
                return true;
            }
        }

        return false;
    }

    public function ensureAttendeePresent(
        string $eventId,
        array $attendee,
        ?string $calendarId = null,
        ?array $credentialsOverride = null
    ): bool {
        [$service, $credentials] = $this->resolveCalendarService($credentialsOverride);

        $targetCalendar = $calendarId ?: $credentials['calendar_id'];

        if (!$targetCalendar) {
            return false;
        }

        try {
            $event = $service?->events->get($targetCalendar, $eventId);
        } catch (\Throwable $exception) {
            Log::warning('Failed to fetch Google Meet event while ensuring attendee.', [
                'event_id' => $eventId,
                'calendar_id' => $targetCalendar,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        $attendees = $event->getAttendees() ?? [];
        $normalizedEmail = strtolower(trim((string) ($attendee['email'] ?? '')));

        if ($normalizedEmail === '' || !filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        foreach ($attendees as $existing) {
            $existingEmail = strtolower((string) $existing->getEmail());

            if ($existingEmail !== '' && hash_equals($existingEmail, $normalizedEmail)) {
                return true;
            }
        }

        $newAttendees = $this->buildEventAttendees([
            [
                'email' => $normalizedEmail,
                'displayName' => $attendee['displayName'] ?? null,
                'organizer' => $attendee['organizer'] ?? false,
            ],
        ]);

        if (empty($newAttendees)) {
            return false;
        }

        $attendees[] = $newAttendees[0];
        $event->setAttendees($attendees);

        try {
            $service->events->patch(
                $targetCalendar,
                $eventId,
                $event,
                [
                    'conferenceDataVersion' => 1,
                    'sendUpdates' => 'none',
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to append host attendee to Google Meet event.', [
                'event_id' => $eventId,
                'calendar_id' => $targetCalendar,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
