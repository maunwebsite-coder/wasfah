<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Recording;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Models\WorkshopReview;
use App\Models\WorkshopUser;
use App\Services\UserGoogleDriveService;
use App\Support\BrandAssets;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChefPublicProfileController extends Controller
{
    use ResolvesWorkshopRecordings;

    public function __construct(protected UserGoogleDriveService $userDriveService)
    {
    }

    /**
     * Display the public profile for a chef with their published recipes.
     */
    public function show(User $chef): View
    {
        if (!$chef->isChef()) {
            abort(404);
        }

        $chef->loadCount('followers');

        $viewer = Auth::user();
        $isOwner = $viewer && $viewer->id === $chef->id;
        $isFollowing = $viewer ? $viewer->isFollowingChef($chef) : false;
        $canViewExclusive = $isOwner;
        $visibilityColumnExists = Schema::hasColumn('recipes', 'visibility');

        $recipes = $chef->recipes()
            ->approved()
            ->with(['category'])
            ->withCount([
                'interactions as saved_count' => function ($query) {
                    $query->where('is_saved', true);
                },
                'interactions as made_count' => function ($query) {
                    $query->where('is_made', true);
                },
                'interactions as rating_count' => function ($query) {
                    $query->whereNotNull('rating');
                },
            ])
            ->withAvg('interactions', 'rating')
            ->orderByDesc('created_at')
            ->get();

        $currentTime = now();

        $workshops = $chef->workshops()
            ->active()
            ->withCount('bookings')
            ->orderBy('start_date')
            ->get();

        [$upcomingWorkshops, $pastWorkshops] = $workshops->partition(function (Workshop $workshop) use ($currentTime): bool {
            if (!$workshop->start_date) {
                return true;
            }

            return $workshop->start_date->greaterThan($currentTime);
        });

        $upcomingWorkshops = $upcomingWorkshops->values();

        $pastWorkshops = $pastWorkshops
            ->sortByDesc(function (Workshop $workshop): int {
                return (int) ($workshop->start_date?->getTimestamp() ?? 0);
            })
            ->values();

        $recordingColumns = collect([
            'recording_url',
            Schema::hasColumn('workshops', 'recording_link') ? 'recording_link' : null,
            Schema::hasColumn('workshops', 'recording') ? 'recording' : null,
            Schema::hasColumn('workshops', 'video_url') ? 'video_url' : null,
            Schema::hasColumn('workshops', 'meeting_link') ? 'meeting_link' : null,
        ])->filter();

        $recordingCandidates = $chef->workshops()
            ->when($recordingColumns->isNotEmpty(), function ($query) use ($recordingColumns) {
                $query->where(function ($subQuery) use ($recordingColumns) {
                    foreach ($recordingColumns as $column) {
                        $subQuery->orWhereNotNull($column);
                    }
                });
            })
            ->orderByDesc('start_date')
            ->limit(8)
            ->get();

        $recordedWorkshops = $recordingCandidates
            ->map(function (Workshop $workshop) use ($chef) {
                $recordingUrl = $this->resolveRecordingUrl($workshop);
                $previewFromUrl = $this->buildRecordingPreviewUrl($recordingUrl);

                // If we only have a Meet link (non-embeddable) try to fetch the actual Drive recording
                if (!$previewFromUrl && $workshop->meeting_code) {
                    $driveUrl = $this->resolveDriveRecordingForWorkshop($workshop, $chef);

                    if ($driveUrl) {
                        $recordingUrl = $driveUrl;
                        $previewFromUrl = $this->buildRecordingPreviewUrl($driveUrl);
                    }
                }

                $workshop->setAttribute('recording_source_url', $recordingUrl);
                $workshop->setAttribute('video_preview_url', $previewFromUrl);
                $workshop->setAttribute('is_direct_video', $this->isDirectVideoUrl($recordingUrl));

                return $workshop;
            })
            ->filter(function (Workshop $workshop) {
                return $workshop->getAttribute('recording_source_url')
                    || $workshop->getAttribute('video_preview_url');
            })
            ->values();

        $appLocale = app()->getLocale();
        $carbonLocale = $appLocale === 'ar' ? 'ar' : 'en';
        $workshopDateTimeFormat = __('chef.workshops.datetime_format');

        $recordingsFromDb = Recording::query()
            ->with('workshop')
            ->where('user_id', $chef->id)
            ->latest()
            ->take(12)
            ->get();

        $relevantWorkshopIds = $recordedWorkshops->pluck('id')
            ->merge($recordingsFromDb->pluck('workshop_id')->filter())
            ->unique()
            ->values()
            ->all();

        $viewerAllowedWorkshops = $viewer
            ? $this->confirmedBookingsForViewer($viewer, $relevantWorkshopIds)
            : [];

        $recordingEntries = $this->buildRecordingEntries(
            $recordedWorkshops,
            $carbonLocale,
            $workshopDateTimeFormat,
            $viewerAllowedWorkshops,
            $isOwner,
            $chef
        );

        $dbRecordingEntries = $this->mapRecordingModelsToEntries(
            $recordingsFromDb,
            $carbonLocale,
            $workshopDateTimeFormat,
            $viewerAllowedWorkshops,
            $isOwner
        );

        $recordingEntries = $dbRecordingEntries
            ->merge($recordingEntries)
            ->sortByDesc('sort_timestamp')
            ->take(12)
            ->values();

        if (!$visibilityColumnExists) {
            $recipes->each(function (Recipe $recipe) {
                if (empty($recipe->visibility)) {
                    $recipe->visibility = Recipe::VISIBILITY_PUBLIC;
                }
            });
        }

        $publicRecipes = $visibilityColumnExists
            ? $recipes->where('visibility', Recipe::VISIBILITY_PUBLIC)->values()
            : $recipes->values();

        $exclusiveRecipes = ($visibilityColumnExists && $canViewExclusive)
            ? $recipes->where('visibility', Recipe::VISIBILITY_PRIVATE)->values()
            : collect();

        $popularRecipes = $publicRecipes
            ->merge($exclusiveRecipes)
            ->sortByDesc(function ($recipe) {
                $ratingScore = (float) ($recipe->interactions_avg_rating ?? 0);

                return ($recipe->saved_count * 100000)
                    + ($ratingScore * 1000)
                    + $recipe->created_at?->getTimestamp();
            })
            ->take(12)
            ->values();

        $stats = $this->buildChefStats($chef, $recipes);

        $viewName = collect([
            'chef.public-profile',
            'chef.profile-fallback',
        ])->first(function (string $candidate): bool {
            return ViewFacade::exists($candidate);
        });

        if (!$viewName) {
            abort(500, 'Chef public profile view is missing.');
        }

        return view($viewName, [
            'chef' => $chef,
            'avatarUrl' => $this->resolveAvatarUrl($chef->avatar),
            'publicRecipes' => $publicRecipes,
            'exclusiveRecipes' => $exclusiveRecipes,
            'popularRecipes' => $popularRecipes,
            'stats' => $stats,
            'socialLinks' => $this->buildSocialLinks($chef),
            'isOwner' => $isOwner,
            'canViewExclusive' => $canViewExclusive,
            'upcomingWorkshops' => $upcomingWorkshops,
            'pastWorkshops' => $pastWorkshops,
            'recordedWorkshops' => $recordedWorkshops,
            'recordingEntries' => $recordingEntries,
            'isFollowing' => $isFollowing,
            'followRoutes' => [
                'follow' => route('chefs.follow', ['chef' => $chef->id]),
                'unfollow' => route('chefs.unfollow', ['chef' => $chef->id]),
            ],
        ]);
    }

    /**
     * Prepare aggregate stats for the chef's recipes.
     */
    protected function buildChefStats(User $chef, Collection $recipes): array
    {
        $averageRecipeRating = $recipes->pluck('interactions_avg_rating')
            ->filter()
            ->average();

        $workshopReviewStats = WorkshopReview::query()
            ->where('is_approved', true)
            ->whereHas('workshop', function ($query) use ($chef) {
                $query->where('user_id', $chef->id);
            })
            ->selectRaw('workshop_id, COUNT(*) as total_reviews, AVG(rating) as average_rating')
            ->groupBy('workshop_id')
            ->get();

        $ratedWorkshopsCount = $workshopReviewStats->count();

        $workshopAverageRating = $ratedWorkshopsCount > 0
            ? round((float) $workshopReviewStats->avg('average_rating'), 1)
            : null;

        $totalWorkshopReviews = (int) $workshopReviewStats->sum('total_reviews');

        return [
            'recipes_count' => $recipes->count(),
            'total_saves' => (int) $recipes->sum('saved_count'),
            'total_made' => (int) $recipes->sum('made_count'),
            'rating_count' => $totalWorkshopReviews,
            'rated_workshops_count' => $ratedWorkshopsCount,
            'average_rating' => $workshopAverageRating,
            'recipes_average_rating' => $averageRecipeRating
                ? round((float) $averageRecipeRating, 1)
                : null,
        ];
    }

    /**
     * Convert the stored avatar path to a public URL.
     */
    protected function resolveAvatarUrl(?string $avatar): string
    {
        if (!$avatar) {
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

    /**
     * Build social links list for the profile header.
     */
    protected function buildSocialLinks(User $chef): Collection
    {
        return collect([
            $chef->instagram_url ? [
                'label' => 'إنستغرام',
                'url' => $chef->instagram_url,
                'icon' => 'fab fa-instagram',
                'followers' => $chef->instagram_followers,
            ] : null,
            $chef->youtube_url ? [
                'label' => 'يوتيوب',
                'url' => $chef->youtube_url,
                'icon' => 'fab fa-youtube',
                'followers' => $chef->youtube_followers,
            ] : null,
        ])->filter()->values();
    }

    protected function buildRecordingEntries(
        Collection $recordedWorkshops,
        string $locale,
        string $dateTimeFormat,
        array $allowedWorkshopIds = [],
        bool $isOwner = false,
        ?User $chef = null
    ): Collection {
        $userDriveEnabled = $chef?->hasGoogleDriveCredentials() ?? false;
        $workshopEntries = $recordedWorkshops
            ->map(function (Workshop $workshop) use ($locale, $dateTimeFormat, $allowedWorkshopIds, $isOwner): ?array {
                $startDateLabel = $workshop->start_date
                    ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateTimeFormat)
                    : __('chef.workshops.unscheduled_time');

                $locationLabel = $workshop->is_online
                    ? __('chef.workshops.online_live')
                    : ($workshop->location ?: __('chef.workshops.location_tbd'));

                $recordingUrl = $workshop->getAttribute('recording_source_url');
                $previewUrl = $workshop->getAttribute('video_preview_url');

                if (! $recordingUrl && ! $previewUrl) {
                    return null;
                }

                $isHiddenPublic = (bool) ($workshop->hide_public_recording ?? false);
                $isHiddenEverywhere = (bool) ($workshop->hide_recording_everywhere ?? false);

                if (! $isOwner) {
                    if ($isHiddenEverywhere) {
                        return null;
                    }

                    if ($isHiddenPublic && ! in_array($workshop->id, $allowedWorkshopIds, true)) {
                        return null;
                    }
                }

                $description = $workshop->description
                    ? Str::limit(strip_tags($workshop->description), 130)
                    : null;

                return [
                    'id' => 'workshop-' . $workshop->id,
                    'title' => $workshop->title,
                    'excerpt' => $description,
                    'date_label' => $startDateLabel,
                    'location_label' => $locationLabel,
                    'watch_url' => $recordingUrl,
                    'preview_url' => $previewUrl,
                    'details_url' => $workshop->slug
                        ? route('workshop.show', ['workshop' => $workshop->slug])
                        : null,
                    'badge' => $previewUrl
                        ? __('chef.recordings.badges.available')
                        : __('chef.recordings.badges.drive'),
                    'type' => 'workshop',
                    'sort_timestamp' => (int) ($workshop->start_date?->getTimestamp() ?? 0),
                    'poster' => $workshop->image
                        ? asset('storage/' . ltrim($workshop->image, '/'))
                        : null,
                    'is_direct_video' => $this->isDirectVideoUrl($recordingUrl),
                ];
            })
            ->filter()
            ->values()
            ->toBase();

        $driveEntries = collect();

        if ($isOwner && $userDriveEnabled) {
            $files = $this->userDriveService->listRecordings($chef, 12);

            $driveEntries = collect($files)
                ->filter(fn ($file) => $file instanceof DriveFile)
                ->map(function (DriveFile $file) use ($locale, $dateTimeFormat): array {
                    $modifiedAt = $file->getModifiedTime()
                        ? Carbon::parse($file->getModifiedTime())->locale($locale)
                        : null;

                    $fileId = $file->getId();
                    $previewUrl = $fileId
                        ? sprintf('https://drive.google.com/file/d/%s/preview', $fileId)
                        : null;

                    $watchUrl = $file->getWebViewLink() ?: $previewUrl ?: $file->getWebContentLink();
                    $description = $file->getDescription();

                    return [
                        'id' => 'drive-' . ($fileId ?: uniqid('drive-', true)),
                        'title' => $file->getName() ?: __('chef.recordings.untitled'),
                        'excerpt' => $description
                            ? Str::limit($description, 130)
                            : __('chef.recordings.drive_default_description'),
                        'date_label' => $modifiedAt
                            ? $modifiedAt->translatedFormat($dateTimeFormat)
                            : __('chef.recordings.updated_unknown'),
                        'location_label' => __('chef.recordings.library_label'),
                        'watch_url' => $watchUrl,
                        'preview_url' => $previewUrl,
                        'details_url' => $watchUrl,
                        'badge' => __('chef.recordings.badges.available'),
                        'type' => 'drive',
                        'sort_timestamp' => $modifiedAt ? $modifiedAt->getTimestamp() : 0,
                        'poster' => $file->getIconLink(),
                        'is_direct_video' => false,
                    ];
                });
        }

        return $workshopEntries
            ->merge($driveEntries)
            ->sortByDesc('sort_timestamp')
            ->take(12)
            ->values();
    }

    /**
     * Map stored recording rows to the rendering format with access checks.
     */
    protected function mapRecordingModelsToEntries(
        Collection $recordings,
        string $locale,
        string $dateTimeFormat,
        array $allowedWorkshopIds,
        bool $isOwner = false
    ): Collection {
        return $recordings
            ->map(function (Recording $recording) use ($locale, $dateTimeFormat, $allowedWorkshopIds, $isOwner): ?array {
                $workshop = $recording->relationLoaded('workshop')
                    ? $recording->workshop
                    : $recording->workshop()->first();

                $hasAccess = $isOwner
                    || (bool) $recording->is_public
                    || ($recording->workshop_id && in_array($recording->workshop_id, $allowedWorkshopIds, true));

                if (! $hasAccess) {
                    return null;
                }

                $recordingUrl = $recording->recording_url;
                $previewUrl = $recording->preview_url ?: $this->buildRecordingPreviewUrl($recordingUrl);

                $dateLabel = $workshop?->start_date
                    ? $workshop->start_date->copy()->locale($locale)->translatedFormat($dateTimeFormat)
                    : ($recording->created_at
                        ? $recording->created_at->copy()->locale($locale)->translatedFormat($dateTimeFormat)
                        : null);

                $locationLabel = $workshop
                    ? ($workshop->is_online
                        ? __('chef.workshops.online_live')
                        : ($workshop->location ?: __('chef.workshops.location_tbd')))
                    : __('chef.recordings.library_label');

                $description = $workshop?->description
                    ? Str::limit(strip_tags($workshop->description), 130)
                    : null;

                return [
                    'id' => 'recording-' . $recording->id,
                    'title' => $recording->title ?: ($workshop?->title ?? __('chef.recordings.untitled')),
                    'excerpt' => $description,
                    'date_label' => $dateLabel,
                    'location_label' => $locationLabel,
                    'watch_url' => $recordingUrl,
                    'preview_url' => $previewUrl,
                    'details_url' => $workshop?->slug
                        ? route('workshop.show', ['workshop' => $workshop->slug])
                        : null,
                    'badge' => $previewUrl
                        ? __('chef.recordings.badges.available')
                        : __('chef.recordings.badges.drive'),
                    'type' => 'recording',
                    'sort_timestamp' => (int) ($workshop?->start_date?->getTimestamp()
                        ?? $recording->created_at?->getTimestamp()
                        ?? 0),
                    'poster' => $workshop?->image
                        ? asset('storage/' . ltrim($workshop->image, '/'))
                        : null,
                    'is_direct_video' => $this->isDirectVideoUrl($recordingUrl),
                ];
            })
            ->filter()
            ->values()
            ->toBase();
    }

    /**
     * Get IDs of workshops this viewer has confirmed bookings for.
     */
    protected function confirmedBookingsForViewer(User $viewer, array $workshopIds): array
    {
        if (empty($workshopIds)) {
            return [];
        }

        $bookingIds = WorkshopBooking::query()
            ->where('user_id', $viewer->id)
            ->whereIn('workshop_id', $workshopIds)
            ->where(function ($query) {
                $query->whereIn('status', ['confirmed', 'pending', 'completed'])
                    ->orWhere('payment_status', 'paid');
            })
            ->pluck('workshop_id')
            ->unique()
            ->all();

        $pivotIds = WorkshopUser::query()
            ->where('user_id', $viewer->id)
            ->whereIn('workshop_id', $workshopIds)
            ->where('has_recording_access', true)
            ->pluck('workshop_id')
            ->unique()
            ->all();

        return array_values(array_unique(array_merge($bookingIds, $pivotIds)));
    }

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
}

