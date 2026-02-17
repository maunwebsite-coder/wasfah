<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Recipe;
use App\Models\Workshop;
use App\Models\Recording;
use App\Models\UserInteraction;
use App\Models\WorkshopUser;
use App\Services\ContentModerationService;
use App\Support\Timezones;
use App\Support\Concerns\ResolvesWorkshopRecordings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\ImageUploadConstraints;

class ProfileController extends Controller
{
    use ResolvesWorkshopRecordings;

    /**
     * عرض صفحة الملف الشخصي للمستخدم
     */
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', $this->buildProfileContext($user));
    }

    /**
     * صفحة الإحصائيات التفصيلية للملف الشخصي
     */
    public function statistics()
    {
        $user = Auth::user();

        return view('profile.statistics', $this->buildProfileContext($user));
    }

    /**
     * صفحة النشاطات والتفاعلات للملف الشخصي
     */
    public function activity()
    {
        $user = Auth::user();

        return view('profile.activity', $this->buildProfileContext($user));
    }

    /**
     * تجهيز البيانات المشتركة بين صفحات الملف الشخصي
     */
    protected function buildProfileContext(User $user): array
    {
        // الحصول على الوصفات المحفوظة
        $savedInteractions = UserInteraction::where('user_id', $user->id)
            ->where('is_saved', true)
            ->with(['recipe.category'])
            ->latest('updated_at')
            ->get();

        $savedRecipes = $savedInteractions
            ->map(function ($interaction) {
                if (!$interaction->recipe) {
                    return null;
                }

                $interaction->recipe->setRelation('userInteraction', $interaction);

                return $interaction->recipe;
            })
            ->filter()
            ->values(); // إزالة القيم الفارغة
        
        // الحصول على الوصفات المصنوعة
        $madeInteractions = UserInteraction::where('user_id', $user->id)
            ->where('is_made', true)
            ->with(['recipe.category'])
            ->latest('updated_at')
            ->get();

        $madeRecipes = $madeInteractions
            ->map(function ($interaction) {
                if (!$interaction->recipe) {
                    return null;
                }

                $interaction->recipe->setRelation('userInteraction', $interaction);

                return $interaction->recipe;
            })
            ->filter()
            ->values();
        
        // الحصول على جميع الورشات المحجوزة (مؤكدة ومعلقة وملغية)
        $bookedWorkshops = $user->workshopBookings()
            ->with('workshop')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // الحصول على تقييمات الورشات
        $workshopReviews = $user->workshopReviews()
            ->with('workshop')
            ->get();

        $upcomingWorkshops = $bookedWorkshops
            ->filter(function ($booking) {
                $startDate = optional($booking->workshop)->start_date;

                return $startDate && $startDate->isFuture();
            })
            ->sortBy(function ($booking) {
                return optional($booking->workshop->start_date);
            })
            ->values();

        $nextWorkshop = $upcomingWorkshops->first();

        $activityFeed = collect()
            ->merge(
                $savedInteractions->take(4)->map(function ($interaction) {
                    if (!$interaction->recipe) {
                        return null;
                    }

                    return [
                        'type' => 'saved_recipe',
                        'title' => $interaction->recipe->title,
                        'timestamp' => $interaction->updated_at ?? $interaction->created_at,
                        'meta' => [
                            'recipe_id' => $interaction->recipe->recipe_id,
                            'slug' => $interaction->recipe->slug,
                            'image' => $interaction->recipe->image ?? $interaction->recipe->image_url,
                            'category' => optional($interaction->recipe->category)->name,
                        ],
                    ];
                })
            )
            ->merge(
                $madeInteractions->take(4)->map(function ($interaction) {
                    if (!$interaction->recipe) {
                        return null;
                    }

                    return [
                        'type' => 'made_recipe',
                        'title' => $interaction->recipe->title,
                        'timestamp' => $interaction->updated_at ?? $interaction->created_at,
                        'meta' => [
                            'recipe_id' => $interaction->recipe->recipe_id,
                            'slug' => $interaction->recipe->slug,
                            'image' => $interaction->recipe->image ?? $interaction->recipe->image_url,
                            'category' => optional($interaction->recipe->category)->name,
                        ],
                    ];
                })
            )
            ->merge(
                $bookedWorkshops->take(6)->map(function ($booking) {
                    if (!$booking->workshop) {
                        return null;
                    }

                    return [
                        'type' => 'workshop_booking',
                        'title' => $booking->workshop->title,
                        'timestamp' => $booking->updated_at ?? $booking->created_at,
                        'meta' => [
                            'status' => $booking->status,
                            'start_date' => $booking->workshop->start_date,
                            'end_date' => $booking->workshop->end_date,
                            'workshop_id' => $booking->workshop->id,
                        ],
                    ];
                })
            )
            ->filter()
            ->sortByDesc('timestamp')
            ->values()
            ->take(8);

        $latestActivity = $activityFeed->first();

        $accessibleWorkshopIds = $this->recordingWorkshopAccessIds($user);
        $accessibleRecordings = Recording::query()
            ->with('workshop')
            ->where(function ($query) use ($user, $accessibleWorkshopIds) {
                $query->where('user_id', $user->id)
                    ->orWhere('is_public', true);

                if (!empty($accessibleWorkshopIds)) {
                    $query->orWhereIn('workshop_id', $accessibleWorkshopIds);
                }
            })
            ->latest()
            ->take(12)
            ->get()
            ->map(function (Recording $recording) {
                $workshop = $recording->workshop;
                $previewUrl = $recording->preview_url ?: $this->buildRecordingPreviewUrl($recording->recording_url);
                $dateLabel = $workshop?->start_date
                    ? $workshop->start_date->translatedFormat(__('chef.workshops.datetime_format'))
                    : ($recording->created_at
                        ? $recording->created_at->translatedFormat('d M Y')
                        : null);

                return [
                    'id' => $recording->id,
                    'title' => $recording->title ?: ($workshop?->title ?? __('chef.recordings.untitled')),
                    'watch_url' => $recording->recording_url,
                    'preview_url' => $previewUrl,
                    'is_public' => (bool) $recording->is_public,
                    'source' => $recording->source,
                    'workshop_title' => $workshop?->title,
                    'workshop_id' => $workshop?->id,
                    'date_label' => $dateLabel,
                    'is_direct_video' => $this->isDirectVideoUrl($recording->recording_url),
                ];
            })
            ->filter()
            ->values();

        // إحصائيات المستخدم
        $stats = [
            'saved_recipes_count' => $savedRecipes->count(),
            'made_recipes_count' => $madeRecipes->count(),
            'booked_workshops_count' => $bookedWorkshops->count(),
            'confirmed_workshops_count' => $bookedWorkshops->where('status', 'confirmed')->count(),
            'pending_workshops_count' => $bookedWorkshops->where('status', 'pending')->count(),
            'cancelled_workshops_count' => $bookedWorkshops->where('status', 'cancelled')->count(),
            'reviews_count' => $workshopReviews->count(),
            'upcoming_workshops_count' => $upcomingWorkshops->count(),
            'next_workshop_at' => optional(optional($nextWorkshop)->workshop)->start_date,
            'last_activity_at' => $latestActivity['timestamp'] ?? null,
        ];

        $stats['engagement_score'] = ($stats['saved_recipes_count'] * 2)
            + ($stats['made_recipes_count'] * 3)
            + ($stats['booked_workshops_count'] * 4);

        $computeAchievement = function (int $count, array $milestones) {
            $currentLevel = 'starter';
            $nextGoal = null;
            $progress = 100;

            foreach ($milestones as $threshold => $label) {
                if ($count >= $threshold) {
                    $currentLevel = $label;
                    continue;
                }

                $nextGoal = $threshold;
                $progress = $threshold > 0 ? min(100, (int) round(($count / $threshold) * 100)) : 0;
                break;
            }

            if ($nextGoal === null && $count < array_key_last($milestones)) {
                $nextGoal = array_key_last($milestones);
                $progress = $nextGoal > 0 ? min(100, (int) round(($count / $nextGoal) * 100)) : 0;
            }

            if ($count >= array_key_last($milestones)) {
                $nextGoal = null;
                $progress = 100;
            }

            return [
                'current_level' => $currentLevel,
                'next_goal' => $nextGoal,
                'progress' => $progress,
            ];
        };

        $achievements = [
            array_merge([
                'key' => 'collector',
                'title' => 'هاوي الوصفات',
                'description_prefix' => 'حفظت',
                'description_suffix' => 'وصفات في مكتبتك.',
                'count' => $stats['saved_recipes_count'],
                'icon' => 'bookmark',
            ], $computeAchievement($stats['saved_recipes_count'], [
                3 => 'bronze',
                10 => 'silver',
                25 => 'gold',
                50 => 'platinum',
            ])),
            array_merge([
                'key' => 'chef',
                'title' => 'الشيف المنزلي',
                'description_prefix' => 'جربت',
                'description_suffix' => 'وصفات بنفسك.',
                'count' => $stats['made_recipes_count'],
                'icon' => 'utensils',
            ], $computeAchievement($stats['made_recipes_count'], [
                1 => 'bronze',
                5 => 'silver',
                15 => 'gold',
                30 => 'platinum',
            ])),
            array_merge([
                'key' => 'learner',
                'title' => 'عاشق التعلّم',
                'description_prefix' => 'حجزت',
                'description_suffix' => 'ورشات حتى الآن.',
                'count' => $stats['booked_workshops_count'],
                'icon' => 'graduation-cap',
            ], $computeAchievement($stats['booked_workshops_count'], [
                1 => 'bronze',
                3 => 'silver',
                6 => 'gold',
                10 => 'platinum',
            ])),
        ];
        
        $chefOverview = null;

        if ($user->isChef()) {
            $chefRecipes = $user->recipes()
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

            $supportsVisibility = Recipe::supportsVisibilityFlag();

            if (!$supportsVisibility) {
                $chefRecipes->each(static function (Recipe $recipe): void {
                    if (empty($recipe->visibility)) {
                        $recipe->visibility = Recipe::VISIBILITY_PUBLIC;
                    }
                });
            }

            $supportsModeration = Recipe::supportsModerationStatus();

            $statusCounts = [
                'approved' => $supportsModeration
                    ? $chefRecipes->where('status', Recipe::STATUS_APPROVED)->count()
                    : $chefRecipes->count(),
                'pending' => $supportsModeration
                    ? $chefRecipes->where('status', Recipe::STATUS_PENDING)->count()
                    : 0,
                'draft' => $supportsModeration
                    ? $chefRecipes->where('status', Recipe::STATUS_DRAFT)->count()
                    : 0,
                'rejected' => $supportsModeration
                    ? $chefRecipes->where('status', Recipe::STATUS_REJECTED)->count()
                    : 0,
            ];

            $publicRecipes = $supportsVisibility
                ? $chefRecipes->where('visibility', Recipe::VISIBILITY_PUBLIC)->values()
                : $chefRecipes->values();

            $exclusiveRecipeCount = $supportsVisibility
                ? $chefRecipes->where('visibility', Recipe::VISIBILITY_PRIVATE)->count()
                : 0;

            $privateRecipes = $supportsVisibility
                ? $chefRecipes->where('visibility', Recipe::VISIBILITY_PRIVATE)->values()
                : collect();

            $popularRecipes = $publicRecipes
                ->merge($privateRecipes)
                ->sortByDesc(static function (Recipe $recipe): int {
                    $ratingScore = (float) ($recipe->interactions_avg_rating ?? 0);

                    return ($recipe->saved_count * 100000)
                        + ($ratingScore * 1000)
                        + (int) optional($recipe->created_at)->getTimestamp();
                })
                ->take(3)
                ->values();

            $averageRating = $chefRecipes->pluck('interactions_avg_rating')
                ->filter()
                ->average();

            $linkPage = $user->linkPage()->withCount('items')->first();

            $chefOverview = [
                'recipes_total' => $chefRecipes->count(),
                'public_recipes' => $publicRecipes->count(),
                'exclusive_recipes' => $exclusiveRecipeCount,
                'status_counts' => $statusCounts,
                'total_saves' => (int) $chefRecipes->sum('saved_count'),
                'total_made' => (int) $chefRecipes->sum('made_count'),
                'average_rating' => $averageRating ? round((float) $averageRating, 1) : null,
                'ratings_count' => (int) $chefRecipes->sum('rating_count'),
                'popular_recipes' => $popularRecipes,
                'public_profile_url' => route('chefs.show', ['chef' => $user->id]),
                'dashboard_url' => route('chef.dashboard'),
                'links_url' => route('chef.links.edit'),
                'link_page' => [
                    'public_url' => $linkPage ? route('links.chef', $linkPage) : null,
                    'items_count' => $linkPage?->items_count ?? 0,
                    'is_published' => (bool) ($linkPage?->is_published ?? false),
                ],
            ];
        }

        $timezoneOptions = Timezones::options();
        $detectedTimezone = request()->cookie('user_timezone');
        $preferredTimezone = $user->timezone ?? $detectedTimezone ?? config('app.timezone', 'UTC');

        return compact(
            'user',
            'savedRecipes',
            'madeRecipes', 
            'bookedWorkshops',
            'workshopReviews',
            'stats',
            'activityFeed',
            'achievements',
            'nextWorkshop',
            'upcomingWorkshops',
            'chefOverview',
            'timezoneOptions',
            'preferredTimezone',
            'detectedTimezone',
            'accessibleRecordings'
        );
    }

    protected function recordingWorkshopAccessIds(User $user): array
    {
        $bookingIds = $user->workshopBookings()
            ->where('status', 'confirmed')
            ->pluck('workshop_id')
            ->unique()
            ->all();

        $pivotIds = WorkshopUser::query()
            ->where('user_id', $user->id)
            ->where('has_recording_access', true)
            ->pluck('workshop_id')
            ->unique()
            ->all();

        return array_values(array_unique(array_merge($bookingIds, $pivotIds)));
    }
    
    /**
     * تحديث معلومات الملف الشخصي
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $timezoneKeys = array_keys(Timezones::options());
        $timezoneRule = [
            $user->role === User::ROLE_CHEF ? 'required' : 'nullable',
            'string',
            Rule::in($timezoneKeys),
        ];

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s\(\)]+$/',
            'timezone' => $timezoneRule,
            'avatar' => array_merge(['nullable'], ImageUploadConstraints::rules()),
        ];

        if ($user->isAdmin() || $user->isChef()) {
            $rules['instagram_url'] = 'nullable|url|max:255';
            $rules['youtube_url'] = 'nullable|url|max:255';
        }

        $messages = ImageUploadConstraints::messages('avatar', [
            'ar' => 'الصورة الشخصية',
            'en' => 'profile photo',
        ]);

        $request->validate($rules, $messages);

        if (ContentModerationService::containsProhibitedLanguage($request->name)) {
            return back()
                ->withErrors(['name' => 'الاسم يحتوي على كلمات غير لائقة.'])
                ->withInput();
        }
        
        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'timezone' => $request->input('timezone') ?: null,
        ];

        if ($user->isAdmin() || $user->isChef()) {
            $updateData['instagram_url'] = $request->input('instagram_url') ?: null;
            $updateData['youtube_url'] = $request->input('youtube_url') ?: null;
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store(
                'avatars/' . $user->id,
                'public'
            );

            $oldAvatar = $user->avatar;
            $shouldDeleteOldAvatar = $oldAvatar
                && ! Str::startsWith($oldAvatar, ['http://', 'https://'])
                && Storage::disk('public')->exists($oldAvatar);

            if ($shouldDeleteOldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            $updateData['avatar'] = $avatarPath;
        }

        $user->update($updateData);
        
        return redirect()->route('profile')->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }
}
