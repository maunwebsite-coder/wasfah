<?php

namespace App\Http\Controllers;

use App\Models\ChefLinkPage;
use App\Models\User;
use App\Models\Workshop;
use App\Services\UserGoogleDriveService;
use App\Support\BrandAssets;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChefPublicWorkshopController extends Controller
{
    use ResolvesWorkshopRecordings;

    public function __construct(protected UserGoogleDriveService $userDriveService)
    {
    }

    /**
     * Cache of the users table columns to avoid repeated schema lookups.
     */
    protected ?array $userTableColumns = null;

    /**
     * Display all recorded workshops for a given chef.
     */
    public function show(string $username): View
    {
        $chef = $this->resolveChef($username);

        if (! $chef->isChef()) {
            abort(404);
        }

        $workshops = $chef->workshops()
            ->orderByDesc('start_date')
            ->get()
            ->map(function (Workshop $workshop) use ($chef) {
                $recordingUrl = $this->resolveRecordingUrl($workshop);
                $previewUrl = $this->buildRecordingPreviewUrl($recordingUrl);

                if (! $previewUrl && ($workshop->meeting_code || $workshop->meeting_link)) {
                    $driveUrl = $this->resolveDriveRecordingForWorkshop($workshop, $chef);

                    if ($driveUrl) {
                        $recordingUrl = $driveUrl;
                        $previewUrl = $this->buildRecordingPreviewUrl($driveUrl);
                        $workshop->setAttribute('recording_from_drive', true);
                    }
                }

                $isDirectVideo = $this->isDirectVideoUrl($recordingUrl);

                $workshop->setAttribute('recording_source_url', $recordingUrl);
                $workshop->setAttribute('video_preview_url', $previewUrl);
                $workshop->setAttribute('is_direct_video', $isDirectVideo);
                $workshop->setAttribute('formatted_start_date', $this->formatWorkshopDate($workshop));

                return $workshop;
            });

        $appLocale = app()->getLocale();
        $carbonLocale = $appLocale === 'ar' ? 'ar' : 'en';
        $dateTimeFormat = __('chef.workshops.datetime_format');
        $driveRecordings = $this->resolveDriveRecordings($workshops, $carbonLocale, $dateTimeFormat, $chef);
        $workshops = $this->attachDriveRecordingsToWorkshops($workshops, $driveRecordings);
        $recordingStats = $this->summarizeRecordingCounts($workshops, $driveRecordings);

        return view('chef.public-workshops', [
            'chef' => $chef,
            'avatarUrl' => $this->resolveAvatarUrl($chef->avatar),
            'workshops' => $workshops,
            'driveRecordings' => $driveRecordings,
            'recordingStats' => $recordingStats,
        ]);
    }

    /**
     * Build a normalized list of Drive recordings so the view can render them.
     */
    protected function resolveDriveRecordings(Collection $workshops, string $locale, string $dateTimeFormat, User $chef): Collection
    {
        $userDriveEnabled = $chef->hasGoogleDriveCredentials();
        $driveEnabled = $userDriveEnabled;

        if (! $driveEnabled) {
            return collect();
        }

        $matchIndex = $this->buildWorkshopMatchIndex($workshops);

        if (empty($matchIndex['meeting']) && empty($matchIndex['title'])) {
            return collect();
        }

        $files = $this->userDriveService->listRecordings($chef, 100);

        return collect($files)
            ->filter(fn ($file) => $file instanceof DriveFile)
            ->map(function (DriveFile $file) use ($matchIndex, $locale, $dateTimeFormat, $chef): ?array {
                $name = strtolower($file->getName() ?? '');

                if ($name === '') {
                    return null;
                }

                $match = $this->matchWorkshopFromDriveFile($name, $matchIndex);

                if (! $match) {
                    return null;
                }

                $modifiedAt = $file->getModifiedTime()
                    ? Carbon::parse($file->getModifiedTime())->locale($locale)
                    : null;

                $fileId = $file->getId();
                $this->ensureDriveFileIsPublic($chef, $fileId, $file->getWebViewLink(), $file->getWebContentLink());
                $previewUrl = $fileId
                    ? sprintf('https://drive.google.com/file/d/%s/preview', $fileId)
                    : null;

                $watchUrl = $file->getWebViewLink()
                    ?: ($previewUrl ?: $file->getWebContentLink());

                return [
                    'id' => $fileId ?: uniqid('drive-', true),
                    'matched_code' => $match['code'] ?? null,
                    'matched_workshop_id' => $match['workshop_id'] ?? null,
                    'title' => $file->getName() ?: __('chef.recordings.untitled'),
                    'description' => $file->getDescription()
                        ? Str::limit($file->getDescription(), 140)
                        : __('chef.recordings.drive_default_description'),
                    'modified_label' => $modifiedAt
                        ? $modifiedAt->translatedFormat($dateTimeFormat)
                        : __('chef.recordings.updated_unknown'),
                    'preview_url' => $previewUrl,
                    'watch_url' => $watchUrl,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Attach any resolved Drive recordings back onto their workshops for inline playback.
     */
    protected function attachDriveRecordingsToWorkshops(Collection $workshops, Collection $driveRecordings): Collection
    {
        if ($driveRecordings->isEmpty()) {
            return $workshops;
        }

        $recordingsByWorkshop = $driveRecordings
            ->filter(fn (array $recording) => ! empty($recording['matched_workshop_id']))
            ->groupBy('matched_workshop_id');

        if ($recordingsByWorkshop->isEmpty()) {
            return $workshops;
        }

        return $workshops->map(function (Workshop $workshop) use ($recordingsByWorkshop) {
            $grouped = $recordingsByWorkshop->get($workshop->getKey());
            $recording = $grouped instanceof Collection ? $grouped->first() : null;

            if (! $recording) {
                return $workshop;
            }

            $currentSource = $workshop->getAttribute('recording_source_url');
            $currentPreview = $workshop->getAttribute('video_preview_url');
            $this->ensureDriveFileIsPublic(
                $workshop->chef,
                $recording['id'] ?? null,
                $recording['watch_url'] ?? null,
                $recording['preview_url'] ?? null
            );

            if (! $currentSource && ! empty($recording['watch_url'])) {
                $workshop->setAttribute('recording_source_url', $recording['watch_url']);
                $currentSource = $recording['watch_url'];
            }

            if (! $currentPreview && ! empty($recording['preview_url'])) {
                $workshop->setAttribute('video_preview_url', $recording['preview_url']);
            }

            if (! $workshop->getAttribute('is_direct_video')) {
                $workshop->setAttribute(
                    'is_direct_video',
                    $this->isDirectVideoUrl($workshop->getAttribute('recording_source_url'))
                );
            }

            if (! empty($recording['preview_url']) || ! empty($recording['watch_url'])) {
                $workshop->setAttribute('recording_from_drive', true);
            }

            return $workshop;
        });
    }

    /**
     * Build consistent recording statistics so the header chips stay logical.
     */
    protected function summarizeRecordingCounts(Collection $workshops, Collection $driveRecordings): array
    {
        $recordedWorkshops = $workshops->filter(fn (Workshop $workshop) => $this->hasRecordingAttached($workshop));

        $standaloneDriveCount = $driveRecordings
            ->filter(fn (array $recording) => empty($recording['matched_workshop_id']))
            ->count();

        $inlineCount = $recordedWorkshops
            ->filter(fn (Workshop $workshop) => $this->isInlinePlayable($workshop))
            ->count();

        $driveBackedCount = $recordedWorkshops
            ->filter(fn (Workshop $workshop) => $this->isDriveSource($workshop))
            ->count();

        $totalCount = $recordedWorkshops->count() + $standaloneDriveCount;
        $driveTotal = $driveBackedCount + $standaloneDriveCount;

        return [
            'total' => $totalCount,
            'inline' => min($inlineCount, $totalCount),
            'drive' => min($driveTotal, $totalCount),
        ];
    }

    /**
     * Check if a workshop already has any recording attached.
     */
    protected function hasRecordingAttached(Workshop $workshop): bool
    {
        return (bool) (
            $workshop->getAttribute('recording_source_url')
            || $workshop->getAttribute('video_preview_url')
            || $workshop->getAttribute('is_direct_video')
        );
    }

    /**
     * Check if the workshop can play inline (preview iframe or direct video).
     */
    protected function isInlinePlayable(Workshop $workshop): bool
    {
        return (bool) (
            $workshop->getAttribute('video_preview_url')
            || $workshop->getAttribute('is_direct_video')
        );
    }

    /**
     * Determine whether the recording source comes from Google Drive.
     */
    protected function isDriveSource(Workshop $workshop): bool
    {
        if ($workshop->getAttribute('recording_from_drive')) {
            return true;
        }

        $sourceUrl = $workshop->getAttribute('recording_source_url');

        return is_string($sourceUrl) && $sourceUrl !== '' && $this->isGoogleDriveUrl($sourceUrl);
    }

    /**
     * Try to fetch a Drive recording URL using the workshop meeting code.
     */
    protected function resolveDriveRecordingForWorkshop(Workshop $workshop, User $chef): ?string
    {
        $meetingCode = $workshop->meeting_code ?: Workshop::extractMeetingCode($workshop->meeting_link);

        if (! $meetingCode) {
            return null;
        }

        if ($chef->hasGoogleDriveCredentials()) {
            $url = $this->userDriveService->findRecordingUrl($chef, $meetingCode);

            if ($url) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Build lookup tables to match Drive recordings by meeting codes or workshop titles.
     */
    protected function buildWorkshopMatchIndex(Collection $workshops): array
    {
        $meetingVariants = [];
        $titleVariants = [];

        foreach ($workshops as $workshop) {
            $workshopId = $workshop->getKey();
            $normalizedCode = $this->normalizeMeetingCodeValue($workshop->meeting_code);

            if ($normalizedCode) {
                foreach ($this->expandMeetingCodeVariants($normalizedCode) as $variant) {
                    if ($variant === '') {
                        continue;
                    }

                    $meetingVariants[$variant] = [
                        'workshop_id' => $workshopId,
                        'code' => $normalizedCode,
                    ];
                }
            }

            foreach ($this->expandTitleVariants($workshop) as $variant) {
                if ($variant === '') {
                    continue;
                }

                $titleVariants[$variant] = $workshopId;
            }
        }

        uksort($meetingVariants, fn ($a, $b) => strlen($b) <=> strlen($a));
        uksort($titleVariants, fn ($a, $b) => strlen($b) <=> strlen($a));

        return [
            'meeting' => $meetingVariants,
            'title' => $titleVariants,
        ];
    }

    /**
     * Determine which workshop a Drive recording belongs to.
     */
    protected function matchWorkshopFromDriveFile(string $fileName, array $matchIndex): ?array
    {
        foreach ($matchIndex['meeting'] ?? [] as $variant => $payload) {
            if ($variant !== '' && str_contains($fileName, $variant)) {
                return $payload;
            }
        }

        $sluggedName = Str::slug($fileName);

        foreach ($matchIndex['title'] ?? [] as $variant => $workshopId) {
            if ($variant !== '' && str_contains($sluggedName, $variant)) {
                return [
                    'workshop_id' => $workshopId,
                    'code' => null,
                ];
            }
        }

        return null;
    }

    /**
     * Normalize meeting codes to make filtering Drive file names easier.
     */
    protected function normalizeMeetingCodeValue(?string $code): ?string
    {
        if (! is_string($code)) {
            return null;
        }

        $normalized = strtolower(trim($code));

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * Generate the common variants of a meeting code that may appear in Drive file names.
     */
    protected function expandMeetingCodeVariants(string $code): array
    {
        $variants = [$code];

        $compressed = str_replace([' ', '-', '_'], '', $code);
        if ($compressed !== $code) {
            $variants[] = $compressed;
        }

        $hyphenated = str_replace([' ', '_'], '-', $code);
        if ($hyphenated !== $code) {
            $variants[] = $hyphenated;
        }

        $spaced = str_replace(['-', '_'], ' ', $code);
        if ($spaced !== $code) {
            $variants[] = $spaced;
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * Generate normalized variants of a workshop title for matching Drive files.
     */
    protected function expandTitleVariants(Workshop $workshop): array
    {
        $candidates = [
            $workshop->title,
            $workshop->slug,
        ];

        $variants = [];

        foreach ($candidates as $value) {
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $slugged = Str::slug($value);

            if ($slugged !== '') {
                $variants[] = $slugged;
                $variants[] = str_replace('-', '', $slugged);
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * Resolve the chef model from the provided identifier.
     */
    protected function resolveChef(string $identifier): User
    {
        if ($user = $this->findChefByColumn($identifier)) {
            return $user;
        }

        $sluggedIdentifier = Str::slug($identifier, '-', app()->getLocale() ?? 'en');

        if ($sluggedIdentifier !== '') {
            $user = User::query()
                ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [$sluggedIdentifier])
                ->first();

            if ($user) {
                return $user;
            }

            $user = User::query()
                ->where('role', User::ROLE_CHEF)
                ->get()
                ->first(function (User $candidate) use ($sluggedIdentifier) {
                    return Str::slug((string) $candidate->name, '-') === $sluggedIdentifier;
                });

            if ($user) {
                return $user;
            }
        }

        if (is_numeric($identifier)) {
            $user = User::query()->find((int) $identifier);
            if ($user) {
                return $user;
            }
        }

        $linkPage = ChefLinkPage::query()
            ->where('slug', $identifier)
            ->first();

        if ($linkPage && $linkPage->user) {
            return $linkPage->user;
        }

        abort(404);
    }

    /**
     * Ensure a Drive file is shared publicly so visitors can view without requesting access.
     */
    protected function ensureDriveFileIsPublic(?User $chef, ?string $fileId, ?string $watchUrl = null, ?string $previewUrl = null): void
    {
        if (! $chef) {
            return;
        }

        if (! $fileId) {
            $fileId = $this->extractDriveFileId($previewUrl ?? $watchUrl ?? '');
        }

        if (! $fileId) {
            return;
        }

        try {
            $this->userDriveService->shareWithEmails($chef, $fileId, [], true);
        } catch (\Throwable $exception) {
            // Ignore silently; worst case the viewer sees Drive access prompt.
        }
    }

    /**
     * Attempt to find a chef using commonly used username/slug columns.
     */
    protected function findChefByColumn(string $identifier): ?User
    {
        $candidateColumns = array_filter([
            $this->usersTableHasColumn('username') ? 'username' : null,
            $this->usersTableHasColumn('slug') ? 'slug' : null,
            $this->usersTableHasColumn('handle') ? 'handle' : null,
            $this->usersTableHasColumn('referral_code') ? 'referral_code' : null,
        ]);

        foreach ($candidateColumns as $column) {
            $user = User::query()
                ->where($column, $identifier)
                ->first();

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Determine whether the users table has the given column.
     */
    protected function usersTableHasColumn(string $column): bool
    {
        if ($this->userTableColumns === null) {
            $this->userTableColumns = Schema::getColumnListing('users');
        }

        return in_array($column, $this->userTableColumns, true);
    }

    /**
     * Resolve the public avatar URL for the chef.
     */
    protected function resolveAvatarUrl(?string $avatar): string
    {
        if (! $avatar) {
            return BrandAssets::logoAsset('webp');
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        if (Storage::disk('public')->exists($avatar)) {
            return Storage::disk('public')->url($avatar);
        }

        return asset(trim($avatar, '/'));
    }
}
