<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Services\GoogleDriveService;
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

    public function __construct(protected GoogleDriveService $googleDrive)
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
            ->map(function (Workshop $workshop) {
                $recordingUrl = $this->resolveRecordingUrl($workshop);
                $workshop->setAttribute('recording_source_url', $recordingUrl);
                $workshop->setAttribute('video_preview_url', $this->buildRecordingPreviewUrl($recordingUrl));
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

        $recordingEntries = $this->buildRecordingEntries(
            $recordedWorkshops,
            $carbonLocale,
            $workshopDateTimeFormat,
            $viewer ? $this->confirmedBookingsForViewer($viewer, $recordedWorkshops->pluck('id')->all()) : [],
            $isOwner
        );

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

        $stats = $this->buildChefStats($recipes);

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
    protected function buildChefStats(Collection $recipes): array
    {
        $averageRating = $recipes->pluck('interactions_avg_rating')
            ->filter()
            ->average();

        return [
            'recipes_count' => $recipes->count(),
            'total_saves' => (int) $recipes->sum('saved_count'),
            'total_made' => (int) $recipes->sum('made_count'),
            'rating_count' => (int) $recipes->sum('rating_count'),
            'average_rating' => $averageRating
                ? round((float) $averageRating, 1)
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
        bool $isOwner = false
    ): Collection {
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

                if (! $isOwner && ($isHiddenEverywhere || $isHiddenPublic || ! in_array($workshop->id, $allowedWorkshopIds, true))) {
                    return null;
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

        $driveEntries = collect($this->googleDrive->listRecordings(null, 12))
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

        return $workshopEntries
            ->merge($driveEntries)
            ->sortByDesc('sort_timestamp')
            ->take(12)
            ->values();
    }

    /**
     * Get IDs of workshops this viewer has confirmed bookings for.
     */
    protected function confirmedBookingsForViewer(User $viewer, array $workshopIds): array
    {
        if (empty($workshopIds)) {
            return [];
        }

        return WorkshopBooking::query()
            ->where('user_id', $viewer->id)
            ->whereIn('workshop_id', $workshopIds)
            ->where('status', 'confirmed')
            ->pluck('workshop_id')
            ->unique()
            ->all();
    }
}

