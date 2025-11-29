<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected ?Drive $drive = null;
    protected bool $enabled = false;
    protected array $folderIds = [];
    protected ?string $sharedDriveId = null;
    protected int $maxResults = 25;
    protected ?string $serviceAccountPath = null;

    public function __construct(?Client $client = null)
    {
        $config = config('services.google_drive', []);

        $clientId = $config['client_id'] ?? null;
        $clientSecret = $config['client_secret'] ?? null;
        $refreshToken = $config['refresh_token'] ?? null;
        $this->serviceAccountPath = $this->resolveCredentialPath($config['service_account_json'] ?? null);
        $this->sharedDriveId = $config['shared_drive_id'] ?? null;
        $this->folderIds = $this->normalizeFolderIds($config);
        $this->maxResults = max(5, (int) ($config['max_results'] ?? 25));

        if ($this->serviceAccountPath) {
            $this->drive = $this->bootstrapServiceAccountClient($client, $this->serviceAccountPath);
            $this->enabled = $this->drive instanceof Drive;
        }

        if (! $this->enabled && $clientId && $clientSecret && $refreshToken) {
            $this->drive = $this->bootstrapDriveClient($client, $clientId, $clientSecret, $refreshToken);
            $this->enabled = $this->drive instanceof Drive;
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function findRecordingUrl(?string $meetingCode): ?string
    {
        $file = $this->findRecordingByMeetingCode($meetingCode);
        $file = $this->ensurePublicReaderPermission($file);

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
     * Ensure specific emails have viewer access to a Drive file.
     *
     * @param DriveFile|string|null $fileOrId
     * @param array<int, string|null> $emails
     */
    public function shareWithEmails(DriveFile|string|null $fileOrId, array $emails, bool $makePublic = false): void
    {
        if (! $this->enabled || ! $this->drive) {
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
            $permissions = $this->drive->permissions->listPermissions($fileId, [
                'fields' => 'permissions(id,type,role,emailAddress)',
                'supportsAllDrives' => true,
                'includePermissionsFromAllDrives' => true,
            ])->getPermissions() ?? [];

            $existingEmails = collect($permissions)
                ->map(fn ($permission) => strtolower((string) ($permission->getEmailAddress() ?? '')))
                ->filter()
                ->unique();

            if ($makePublic) {
                $this->ensurePublicReaderPermission($fileId);
            }

            foreach ($normalizedEmails as $email) {
                if ($existingEmails->contains($email)) {
                    continue;
                }

                $permission = new Permission();
                $permission->setType('user');
                $permission->setRole('reader');
                $permission->setEmailAddress($email);

                $this->drive->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'sendNotificationEmail' => false,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to share Drive file with attendees.', [
                'file_id' => $fileId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function findRecordingByMeetingCode(?string $meetingCode): ?DriveFile
    {
        if (! $this->enabled || ! $this->drive) {
            return null;
        }

        $normalizedCode = $this->normalizeMeetingCode($meetingCode);

        if (! $normalizedCode) {
            return null;
        }

        try {
            $params = $this->buildListParams($normalizedCode);
            $response = $this->drive->files->listFiles($params);
            $files = $response->getFiles() ?? [];
        } catch (\Throwable $exception) {
            Log::warning('Failed to query Google Drive recordings.', [
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

    public function listRecordings(?string $folderId = null, ?int $limit = null): array
    {
        if (! $this->enabled || ! $this->drive) {
            return [];
        }

        // نستغني عن فلتر parents بالكامل ونعتمد على full-text search
        $conditions = [
            "trashed = false",
            "(mimeType contains 'video/' or mimeType = 'application/vnd.google-apps.video')",
            "(name contains 'Recording' or name contains 'GMT')",
        ];

        $pageSize = $limit !== null ? (int) $limit : $this->maxResults;
        $pageSize = max(1, min(100, $pageSize));

        $params = [
            'q' => implode(' and ', $conditions),
            'pageSize' => $pageSize,
            'fields' => 'files(id,name,mimeType,modifiedTime,webViewLink,webContentLink,parents)',
            'orderBy' => 'modifiedTime desc',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];

        if ($this->sharedDriveId) {
            $params['driveId'] = $this->sharedDriveId;
            $params['corpora'] = 'drive';
        } else {
            $params['corpora'] = 'allDrives';
        }

        try {
            $response = $this->drive->files->listFiles($params);
            $files = $response->getFiles() ?? [];

            return array_values($files);
        } catch (\Throwable $e) {
            Log::warning('Failed to list Google Drive recordings.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
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

    protected function bootstrapDriveClient(
        ?Client $client,
        string $clientId,
        string $clientSecret,
        string $refreshToken
    ): Drive {
        $googleClient = $client ?: new Client();
        $googleClient->setApplicationName(config('app.name') . ' Drive Sync');
        $googleClient->setClientId($clientId);
        $googleClient->setClientSecret($clientSecret);
        $googleClient->setAccessType('offline');
        $googleClient->setPrompt('consent');
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->setScopes([Drive::DRIVE_FILE]);

        $token = $googleClient->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($token['error'])) {
            throw new \RuntimeException(
                'فشل تحديث صلاحيات Google Drive: ' . ($token['error_description'] ?? $token['error'])
            );
        }

        return new Drive($googleClient);
    }

    protected function buildListParams(string $meetingCode): array
    {
        $searchTerm = addcslashes($meetingCode, "'\\");
        $conditions = [
            "trashed = false",
            "(mimeType contains 'video/' or mimeType = 'application/vnd.google-apps.video')",
            sprintf("name contains '%s'", $searchTerm),
        ];

        $folderConstraint = $this->buildFolderConstraint();

        if ($folderConstraint) {
            $conditions[] = $folderConstraint;
        }

        $params = [
            'q' => implode(' and ', $conditions),
            'pageSize' => $this->maxResults,
            'fields' => 'files(id,name,mimeType,modifiedTime,webViewLink,webContentLink,parents)',
            'orderBy' => 'modifiedTime desc',
        ];

        if ($this->sharedDriveId) {
            $params['driveId'] = $this->sharedDriveId;
            $params['corpora'] = 'drive';
            $params['includeItemsFromAllDrives'] = true;
            $params['supportsAllDrives'] = true;
        }

        return $params;
    }

    protected function buildFolderConstraint(?array $folderIds = null): ?string
    {
        $folderIds = $folderIds ?? $this->folderIds;

        if (empty($folderIds)) {
            return null;
        }

        $parts = array_map(function (string $folderId): string {
            $escaped = addcslashes($folderId, "'\\");

            return sprintf("'%s' in parents", $escaped);
        }, $folderIds);

        return '(' . implode(' or ', $parts) . ')';
    }

    protected function normalizeFolderIds(array $config): array
    {
        $primary = Arr::wrap($config['folder_id'] ?? null);
        $additional = $config['additional_folders'] ?? [];
        $recordings = Arr::wrap($config['recordings_folder_id'] ?? null);

        if (is_string($additional)) {
            $additional = array_filter(array_map('trim', explode(',', $additional)));
        }

        $candidateIds = array_merge($primary, Arr::wrap($additional), $recordings);

        $normalized = array_map(function ($value) {
            if (! is_string($value)) {
                return null;
            }

            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }, $candidateIds);

        return array_values(array_filter(array_unique($normalized)));
    }

    /**
     * Ensure the Drive file is accessible via "anyone with the link" (reader).
     */
    protected function ensurePublicReaderPermission(DriveFile|string|null $file): ?DriveFile
    {
        if (! $this->enabled || ! $this->drive) {
            return $file instanceof DriveFile ? $file : null;
        }

        $fileId = $file instanceof DriveFile
            ? $file->getId()
            : (is_string($file) ? trim($file) : null);

        if (! $fileId) {
            return $file instanceof DriveFile ? $file : null;
        }

        try {
            $permissions = $this->drive->permissions->listPermissions($fileId, [
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

                $this->drive->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'sendNotificationEmail' => false,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to ensure public Drive permission.', [
                'error' => $exception->getMessage(),
                'file_id' => $fileId,
            ]);
        }

        return $file instanceof DriveFile ? $file : null;
    }

    protected function resolveCredentialPath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $cleanPath = trim($path);

        if (is_file($cleanPath)) {
            return $cleanPath;
        }

        $basePath = base_path($cleanPath);

        return is_file($basePath) ? $basePath : null;
    }

    protected function bootstrapServiceAccountClient(?Client $client, string $jsonPath): ?Drive
    {
        try {
            $googleClient = $client ?: new Client();
            $googleClient->setAuthConfig($jsonPath);
            $googleClient->setScopes([Drive::DRIVE_FILE]);
            $googleClient->setApplicationName(config('app.name') . ' Drive Sync');

            return new Drive($googleClient);
        } catch (\Throwable $exception) {
            Log::warning('Failed to bootstrap Google Drive service account.', [
                'error' => $exception->getMessage(),
            ]);
        }

        return null;
    }
}
