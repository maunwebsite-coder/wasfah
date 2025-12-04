<?php

namespace App\Services;

use App\Models\Recording;
use App\Models\User;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class UserGoogleDriveService
{
    protected array $config;
    protected int $maxResults = 25;

    public function __construct(protected ?Client $client = null)
    {
        $this->config = config('services.google_drive', []);
        $this->maxResults = max(5, (int) ($this->config['max_results'] ?? 25));
    }

    /**
     * Build an authenticated Drive instance for the given user.
     */
    public function getDrive(User $user): ?Drive
    {
        $client = $this->buildClient($user);

        if (! $client) {
            return null;
        }

        return new Drive($client);
    }

    /**
     * Find a recording file by the Google Meet code for this user.
     */
    public function findRecordingByMeetingCode(User $user, ?string $meetingCode, array $options = []): ?DriveFile
    {
        $drive = $this->getDrive($user);

        if (! $drive) {
            return null;
        }

        $normalizedCode = $this->normalizeMeetingCode($meetingCode);

        if (! $normalizedCode) {
            return null;
        }

        $folderIds = $this->normalizeFolderIds($options['folder_ids'] ?? []);
        $limit = isset($options['limit']) ? (int) $options['limit'] : $this->maxResults;
        $limit = max(1, min(100, $limit));

        try {
            $params = $this->buildListParams($normalizedCode, $folderIds, $limit);
            $response = $drive->files->listFiles($params);
            $files = $response->getFiles() ?? [];
        } catch (\Throwable $exception) {
            Log::warning('Failed to query Google Drive recordings for user.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (empty($files)) {
            return null;
        }

        /** @var DriveFile $latest */
        $latest = Arr::first($files);

        return $latest instanceof DriveFile ? $latest : null;
    }

    /**
     * Find the best recording URL for the user's Drive.
     */
    public function findRecordingUrl(User $user, ?string $meetingCode, array $options = []): ?string
    {
        $file = $this->findRecordingByMeetingCode($user, $meetingCode, $options);
        $drive = $this->getDrive($user);

        if ($drive) {
            $file = $this->ensurePublicReaderPermission($drive, $file);
        }

        if (! $file instanceof DriveFile) {
            return null;
        }

        $webViewLink = $file->getWebViewLink();

        if (is_string($webViewLink) && $webViewLink !== '') {
            return $webViewLink;
        }

        $fileId = $file->getId();

        if (! is_string($fileId) || $fileId === '') {
            return null;
        }

        return sprintf('https://drive.google.com/file/d/%s/preview', $fileId);
    }

    /**
     * List recent Drive recordings for the user (video mime types only).
     *
     * @return array<int, DriveFile>
     */
    public function listRecordings(User $user, ?int $limit = null, array $options = []): array
    {
        $drive = $this->getDrive($user);

        if (! $drive) {
            return [];
        }

        $limit = $limit !== null ? (int) $limit : $this->maxResults;
        $limit = max(1, min(100, $limit));
        $folderIds = $this->normalizeFolderIds($options['folder_ids'] ?? []);

        $conditions = [
            "trashed = false",
            "(mimeType contains 'video/' or mimeType = 'application/vnd.google-apps.video')",
            "(name contains 'Recording' or name contains 'GMT')",
        ];

        if ($folderConstraint = $this->buildFolderConstraint($folderIds)) {
            $conditions[] = $folderConstraint;
        }

        $params = [
            'q' => implode(' and ', $conditions),
            'pageSize' => $limit,
            'fields' => 'files(id,name,mimeType,modifiedTime,webViewLink,webContentLink,parents,iconLink,thumbnailLink)',
            'orderBy' => 'modifiedTime desc',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];

        try {
            $response = $drive->files->listFiles($params);

            return $response->getFiles() ?? [];
        } catch (\Throwable $exception) {
            Log::warning('Failed to list user Drive recordings.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Ensure specific emails have viewer access to a Drive file.
     *
     * @param array<int, string|null> $emails
     */
    public function shareWithEmails(User $user, DriveFile|string|null $fileOrId, array $emails, bool $makePublic = false): void
    {
        $drive = $this->getDrive($user);

        if (! $drive) {
            return;
        }

        $fileId = $fileOrId instanceof DriveFile
            ? $fileOrId->getId()
            : (is_string($fileOrId) ? trim($fileOrId) : null);

        if (! $fileId) {
            return;
        }

        $normalizedEmails = collect($emails)
            ->map(function ($email) {
                if (! is_string($email)) {
                    return null;
                }

                $normalized = strtolower(trim($email));

                return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($normalizedEmails->isEmpty() && ! $makePublic) {
            return;
        }

        try {
            $permissions = $drive->permissions->listPermissions($fileId, [
                'fields' => 'permissions(id,type,role,emailAddress)',
                'supportsAllDrives' => true,
                'includePermissionsFromAllDrives' => true,
            ])->getPermissions() ?? [];

            $existingEmails = collect($permissions)
                ->map(fn ($permission) => strtolower((string) ($permission->getEmailAddress() ?? '')))
                ->filter()
                ->unique();

            if ($makePublic) {
                $this->ensurePublicReaderPermission($drive, $fileId);
            }

            foreach ($normalizedEmails as $email) {
                if ($existingEmails->contains($email)) {
                    continue;
                }

                $permission = new Permission();
                $permission->setType('user');
                $permission->setRole('reader');
                $permission->setEmailAddress($email);

                $drive->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'sendNotificationEmail' => false,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to share Drive file with attendees (user OAuth).', [
                'user_id' => $user->id,
                'file_id' => $fileId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * List video files belonging to the user.
     *
     * @return array<int, DriveFile>
     */
    public function listUserVideos(User $user, int $limit = 25): array
    {
        $drive = $this->getDrive($user);

        if (! $drive) {
            return [];
        }

        $limit = max(1, min(100, $limit));
        $query = [
            "trashed = false",
            "(mimeType contains 'video/' or mimeType = 'application/vnd.google-apps.video')",
        ];

        try {
            $response = $drive->files->listFiles([
                'q' => implode(' and ', $query),
                'pageSize' => $limit,
                'fields' => 'files(id,name,mimeType,modifiedTime,webViewLink,webContentLink,thumbnailLink,iconLink,owners(emailAddress,displayName))',
                'orderBy' => 'modifiedTime desc',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            return $response->getFiles() ?? [];
        } catch (\Throwable $exception) {
            Log::warning('Failed to list user videos from Drive.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch metadata and streaming URLs for a Drive file.
     */
    public function getFileInfo(User $user, string $fileId): ?array
    {
        $drive = $this->getDrive($user);

        if (! $drive) {
            return null;
        }

        try {
            $file = $drive->files->get($fileId, [
                'supportsAllDrives' => true,
                'fields' => 'id,name,mimeType,webViewLink,webContentLink,thumbnailLink,iconLink,owners(emailAddress,displayName)',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to fetch Drive file info.', [
                'user_id' => $user->id,
                'file_id' => $fileId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $this->fileOwnedByUser($user, $file)) {
            return null;
        }

        return $this->normalizeDriveFile($file);
    }

    /**
     * Create or update a Recording row from Drive metadata.
     */
    public function createRecordingEntry(User $user, DriveFile|array $fileInfo, bool $isPublic = false, ?int $workshopId = null): Recording
    {
        $data = $fileInfo instanceof DriveFile
            ? $this->normalizeDriveFile($fileInfo)
            : $fileInfo;

        $driveId = $data['drive_file_id'] ?? null;

        return Recording::updateOrCreate(
            [
                'user_id' => $user->id,
                'drive_file_id' => $driveId,
            ],
            [
                'workshop_id' => $workshopId,
                'title' => $data['title'] ?? null,
                'recording_url' => $data['recording_url'] ?? null,
                'preview_url' => $data['preview_url'] ?? null,
                'is_public' => $isPublic,
                'source' => $data['source'] ?? 'drive',
                'meta' => $data['meta'] ?? null,
            ]
        );
    }

    /**
     * Refresh the user's Drive token when expired.
     */
    public function refreshUserTokens(User $user): ?array
    {
        $refreshToken = $user->google_refresh_token ?: $user->google_calendar_refresh_token;
        $clientId = $this->config['client_id'] ?? config('services.google.client_id');
        $clientSecret = $this->config['client_secret'] ?? config('services.google.client_secret');

        if (! $refreshToken || ! $clientId || ! $clientSecret) {
            return null;
        }

        $client = $this->client ?: new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);

        try {
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
        } catch (\Throwable $exception) {
            Log::warning('Failed to refresh Google Drive token.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (isset($token['error'])) {
            Log::warning('Google Drive token refresh returned an error.', [
                'user_id' => $user->id,
                'error' => $token['error'] ?? null,
                'description' => $token['error_description'] ?? null,
            ]);

            return null;
        }

        $expiresIn = $token['expires_in'] ?? null;

        $user->forceFill([
            'google_access_token' => $token['access_token'] ?? $user->google_access_token,
            'google_refresh_token' => $refreshToken,
            'google_expires_at' => $expiresIn ? now()->addSeconds((int) $expiresIn) : null,
        ])->save();

        return $token;
    }

    /**
     * Check whether the stored access token is expired.
     */
    protected function tokenExpired(User $user): bool
    {
        $expiresAt = $user->google_expires_at;

        if (! $expiresAt) {
            return false;
        }

        return now()->greaterThanOrEqualTo($expiresAt->copy()->subMinutes(1));
    }

    protected function buildClient(User $user): ?Client
    {
        $clientId = $this->config['client_id'] ?? config('services.google.client_id');
        $clientSecret = $this->config['client_secret'] ?? config('services.google.client_secret');

        if (! $clientId || ! $clientSecret) {
            return null;
        }

        $client = clone ($this->client ?: new Client());
        $client->setApplicationName(config('app.name') . ' Drive OAuth');
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes([Drive::DRIVE]);

        $accessToken = $this->buildAccessToken($user, $client);

        if (! $accessToken) {
            return null;
        }

        $client->setAccessToken($accessToken);

        return $client;
    }

    protected function buildAccessToken(User $user, Client $client): ?array
    {
        $token = [
            'access_token' => $user->google_access_token ?: $user->google_calendar_access_token,
            'refresh_token' => $user->google_refresh_token ?: $user->google_calendar_refresh_token,
        ];

        if ($this->tokenExpired($user)) {
            $refreshed = $this->refreshUserTokens($user);

            if (! $refreshed) {
                return null;
            }

            $token['access_token'] = $refreshed['access_token'] ?? $token['access_token'];
        }

        if (! $token['access_token']) {
            return null;
        }

        // Ensure refresh token is attached for future refresh cycles.
        $client->setAccessToken($token);

        return $client->getAccessToken();
    }

    /**
     * Ensure the Drive file is accessible via "anyone with the link" (reader).
     */
    protected function ensurePublicReaderPermission(Drive $drive, DriveFile|string|null $file): ?DriveFile
    {
        $fileId = $file instanceof DriveFile
            ? $file->getId()
            : (is_string($file) ? trim($file) : null);

        if (! $fileId) {
            return $file instanceof DriveFile ? $file : null;
        }

        try {
            $permissions = $drive->permissions->listPermissions($fileId, [
                'fields' => 'permissions(id,type,role)',
                'supportsAllDrives' => true,
                'includePermissionsFromAllDrives' => true,
            ])->getPermissions() ?? [];

            $hasAnyoneReader = collect($permissions)->contains(function ($permission) {
                return $permission->getType() === 'anyone' && $permission->getRole() === 'reader';
            });

            if (! $hasAnyoneReader) {
                $permission = new Permission();
                $permission->setType('anyone');
                $permission->setRole('reader');

                $drive->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'sendNotificationEmail' => false,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to ensure public Drive permission (user OAuth).', [
                'error' => $exception->getMessage(),
                'file_id' => $fileId,
            ]);
        }

        return $file instanceof DriveFile ? $file : null;
    }

    protected function normalizeMeetingCode(?string $input): ?string
    {
        if (! is_string($input)) {
            return null;
        }

        $trimmed = strtolower(trim($input));

        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^[a-z0-9]+-[a-z0-9]+-[a-z0-9]+$/', $trimmed)) {
            return $trimmed;
        }

        if (preg_match('/^[a-z0-9]{3}-[a-z0-9]{4}-[a-z0-9]{3}$/', $trimmed)) {
            return $trimmed;
        }

        return preg_match('/^[a-z0-9_-]{6,}$/', $trimmed) ? $trimmed : null;
    }

    protected function buildListParams(string $meetingCode, array $folderIds, int $limit): array
    {
        $searchTerm = addcslashes($meetingCode, "'\\");
        $conditions = [
            "trashed = false",
            "(mimeType contains 'video/' or mimeType = 'application/vnd.google-apps.video')",
            sprintf("name contains '%s'", $searchTerm),
        ];

        if ($folderConstraint = $this->buildFolderConstraint($folderIds)) {
            $conditions[] = $folderConstraint;
        }

        return [
            'q' => implode(' and ', $conditions),
            'pageSize' => $limit,
            'fields' => 'files(id,name,mimeType,modifiedTime,webViewLink,webContentLink,parents,iconLink,thumbnailLink)',
            'orderBy' => 'modifiedTime desc',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];
    }

    protected function buildFolderConstraint(array $folderIds): ?string
    {
        if (empty($folderIds)) {
            return null;
        }

        $parts = array_map(function (string $folderId): string {
            $escaped = addcslashes($folderId, "'\\");

            return sprintf("'%s' in parents", $escaped);
        }, $folderIds);

        return '(' . implode(' or ', $parts) . ')';
    }

    protected function normalizeFolderIds(array $candidateIds): array
    {
        $normalized = array_map(function ($value) {
            if (! is_string($value)) {
                return null;
            }

            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }, Arr::wrap($candidateIds));

        return array_values(array_filter(array_unique($normalized)));
    }

    protected function fileOwnedByUser(User $user, DriveFile $file): bool
    {
        $email = $user->preferredGoogleDriveEmail();

        if (! $email) {
            return true;
        }

        $owners = $file->getOwners() ?? [];

        foreach ($owners as $owner) {
            $ownerEmail = strtolower((string) ($owner->getEmailAddress() ?? ''));

            if ($ownerEmail !== '' && $ownerEmail === strtolower($email)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeDriveFile(DriveFile $file): array
    {
        $fileId = $file->getId();
        $previewUrl = $fileId ? $this->buildPreviewUrl($fileId) : null;

        return [
            'drive_file_id' => $fileId,
            'title' => $file->getName(),
            'recording_url' => $file->getWebViewLink() ?: $previewUrl ?: $file->getWebContentLink(),
            'preview_url' => $previewUrl,
            'source' => 'drive',
            'meta' => [
                'mimeType' => $file->getMimeType(),
                'iconLink' => $file->getIconLink(),
                'thumbnailLink' => $file->getThumbnailLink(),
            ],
        ];
    }

    protected function buildPreviewUrl(?string $fileId): ?string
    {
        if (! $fileId) {
            return null;
        }

        return sprintf('https://drive.google.com/file/d/%s/preview', $fileId);
    }
}
