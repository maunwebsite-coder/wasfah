<?php

namespace App\Services;

use App\Models\Workshop;
use Illuminate\Support\Facades\Log;

class WorkshopMeetingAttendeeSyncService
{
    public function __construct(protected GoogleMeetService $googleMeetService)
    {
    }

    public function sync(Workshop $workshop): void
    {
        if ($workshop->meeting_provider !== 'google_meet') {
            return;
        }

        $eventId = trim((string) $workshop->meeting_event_id);

        if ($eventId === '') {
            return;
        }

        $hostCredentials = $workshop->hostGoogleMeetCredentials();

        if (!$this->googleMeetService->isEnabled() && !$hostCredentials) {
            return;
        }

        $calendarId = $workshop->meeting_calendar_id
            ?: ($hostCredentials['calendar_id'] ?? null)
            ?: config('services.google_meet.calendar_id')
            ?: config('services.google_meet.organizer_email');

        if (!$calendarId) {
            return;
        }

        $workshop->loadMissing(['chef:id,name,email']);

        $attendees = [];

        $organizerEmail = $this->normalizeEmail($hostCredentials['organizer_email'] ?? config('services.google_meet.organizer_email'));

        if ($organizerEmail) {
            $attendees[$organizerEmail] = [
                'email' => $organizerEmail,
                'organizer' => true,
            ];
        }

        $hostPayload = $workshop->hostAttendeePayload();

        if ($hostPayload && isset($hostPayload['email'])) {
            $normalizedHostEmail = $this->normalizeEmail($hostPayload['email']);

            if ($normalizedHostEmail) {
                $attendees[$normalizedHostEmail] = [
                    'email' => $hostPayload['email'],
                    'displayName' => $hostPayload['displayName'] ?? null,
                ];
            }
        }

        $confirmedBookings = $workshop->bookings()
            ->where('status', 'confirmed')
            ->with('user:id,name,email')
            ->get();

        foreach ($confirmedBookings as $booking) {
            $user = $booking->user;

            if (!$user) {
                continue;
            }

            $participantEmail = $user->preferredGoogleEmail();
            $normalized = $this->normalizeEmail($participantEmail);

            if (!$normalized) {
                continue;
            }

            $attendees[$normalized] = [
                'email' => $normalized,
                'displayName' => $user->name,
            ];
        }

        try {
            $this->googleMeetService->syncEventAttendees(
                $eventId,
                array_values($attendees),
                $calendarId,
                $hostCredentials
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to sync Google Meet attendees for workshop.', [
                'workshop_id' => $workshop->id,
                'event_id' => $eventId,
                'calendar_id' => $calendarId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function normalizeEmail(?string $email): ?string
    {
        $normalized = strtolower(trim((string) $email));

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }
}
