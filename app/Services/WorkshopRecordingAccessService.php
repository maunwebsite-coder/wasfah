<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use Illuminate\Support\Arr;

class WorkshopRecordingAccessService
{
    use ResolvesWorkshopRecordings;

    public function __construct(
        protected GoogleDriveService $driveService,
        protected UserGoogleDriveService $userDriveService,
    ) {
    }

    /**
     * Share configured workshop assets with the attendee email when the booking is confirmed.
     */
    public function shareWithAttendee(WorkshopBooking $booking): void
    {
        if ($booking->status !== 'confirmed') {
            return;
        }

        $booking->loadMissing([
            'user:id,email,google_email,google_drive_email',
            'workshop',
            'workshop.chef:id,email,google_email,google_drive_email,google_access_token,google_refresh_token,google_calendar_refresh_token,google_calendar_email,google_calendar_id',
        ]);

        $email = $this->resolveAttendeeEmail($booking);

        if (! $email) {
            return;
        }

        $targets = $this->driveTargetIds($booking);

        if (empty($targets)) {
            return;
        }

        $host = $booking->workshop?->chef;

        foreach ($targets as $targetId) {
            $this->shareWithEmail($targetId, $email, $host);
        }
    }

    protected function shareWithEmail(string $fileOrFolderId, string $email, ?User $host): void
    {
        if ($host && $host->hasGoogleDriveCredentials()) {
            $this->userDriveService->shareWithEmails($host, $fileOrFolderId, [$email]);
        }

        $this->driveService->shareWithEmails($fileOrFolderId, [$email]);
    }

    protected function resolveAttendeeEmail(WorkshopBooking $booking): ?string
    {
        $email = $booking->user?->preferredGoogleDriveEmail() ?? $booking->user?->email;

        if (! is_string($email)) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }

    /**
     * Build a list of Drive file/folder IDs to share for the booking.
     *
     * @return array<int, string>
     */
    protected function driveTargetIds(WorkshopBooking $booking): array
    {
        $targets = [];
        $workshop = $booking->workshop;

        if ($workshop) {
            $recordingUrl = $this->resolveRecordingUrl($workshop);

            // If we only have a meeting reference, attempt to pull the Drive recording via meeting code
            if (! $this->isGoogleDriveUrl((string) $recordingUrl) && ! $this->isDirectVideoUrl($recordingUrl)) {
                $meetingCode = $workshop->meeting_code ?: Workshop::extractMeetingCode($workshop->meeting_link);
                $host = $workshop->chef;

                if ($meetingCode && $host && $host->hasGoogleDriveCredentials()) {
                    $driveUrl = $this->userDriveService->findRecordingUrl($host, $meetingCode);

                    if ($driveUrl) {
                        $recordingUrl = $driveUrl;
                    }
                }
            }
            $recordingId = $recordingUrl ? $this->extractDriveFileId($recordingUrl) : null;

            if ($recordingId) {
                $targets[] = $recordingId;
            }
        }

        foreach ($this->configuredFolderIds() as $folderId) {
            $targets[] = $folderId;
        }

        return collect($targets)
            ->map(fn ($id) => is_string($id) ? trim($id) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolve Drive folders configured for sharing with attendees.
     *
     * @return array<int, string>
     */
    protected function configuredFolderIds(): array
    {
        $config = config('services.google_drive', []);

        $candidates = array_merge(
            Arr::wrap($config['folder_id'] ?? null),
            Arr::wrap($config['recordings_folder_id'] ?? null),
            $this->normalizeAdditionalFolders($config['additional_folders'] ?? [])
        );

        return collect($candidates)
            ->map(fn ($id) => is_string($id) ? trim($id) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize additional folder IDs from config.
     *
     * @param  string|array|null  $value
     * @return array<int, string>
     */
    protected function normalizeAdditionalFolders($value): array
    {
        if (is_string($value)) {
            return array_filter(array_map('trim', explode(',', $value)));
        }

        if (is_array($value)) {
            return array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : null;
            }, $value));
        }

        return [];
    }
}
