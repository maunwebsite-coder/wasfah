<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\HeroSlide;
use App\Models\Tool;
use App\Models\Workshop;
use App\Support\HeroSlideSchemaState;
use App\Support\VideoEmbed;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * عرض الصفحة الرئيسية
     */
    public function index()
    {
        // جلب الورشة المميزة مع ضمان عرض آخر ورشة مميزة حتى إن لم تكن مستقبلية
        $featuredWorkshopQuery = Workshop::active()
            ->featured()
            ->withCount(['bookings' => function ($query) {
                $query->where('status', 'confirmed');
            }]);

        $featuredWorkshop = (clone $featuredWorkshopQuery)
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->first();

        if (!$featuredWorkshop) {
            $featuredWorkshop = $featuredWorkshopQuery
                ->orderBy('start_date', 'desc')
                ->first();
        }

        // التحقق من وجود ورشات قادمة على الإطلاق
        $hasUpcomingWorkshops = Workshop::active()
            ->upcoming()
            ->exists();

        // جلب أحدث الورشات النشطة (بما في ذلك المكتملة) - استبعاد الورشة المميزة إذا كانت موجودة
        $workshopsQuery = Workshop::active()
            ->withCount(['bookings' => function ($query) {
                $query->where('status', 'confirmed');
            }])
            ->orderByDesc('start_date')
            ->orderByDesc('created_at');

        // استبعاد الورشة المميزة من قائمة الورشات العادية
        if ($featuredWorkshop) {
            $workshopsQuery->where('id', '!=', $featuredWorkshop->id);
        }

        $workshops = $workshopsQuery->limit(4)->get();

        $latestWorkshops = Workshop::active()
            ->withCount(['bookings' => function ($query) {
                $query->where('status', 'confirmed');
            }])
            ->orderByDesc('created_at')
            ->orderByDesc('start_date')
            ->limit(4)
            ->get();

        // جلب أحدث الوصفات للعرض في الشريط الجانبي
        $latestRecipes = Recipe::approved()
            ->public()
            ->with(['category'])
            ->withCount(['interactions as saved_count' => function ($query) {
                $query->where('is_saved', true);
            }])
            ->withCount(['interactions as made_count' => function ($query) {
                $query->where('is_made', true);
            }])
            ->withCount(['interactions as interactions_count'])
            ->withAvg('interactions', 'rating')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // جلب فيديوهات الخلاصة للعرض في القسم الرئيسي
        $feedVideos = Recipe::approved()
            ->public()
            ->whereNotNull('video_url')
            ->whereRaw("TRIM(video_url) <> ''")
            ->with(['category', 'chef'])
            ->withCount(['interactions as saved_count' => function ($query) {
                $query->where('is_saved', true);
            }])
            ->withCount(['interactions as made_count' => function ($query) {
                $query->where('is_made', true);
            }])
            ->withCount(['interactions as interactions_count'])
            ->withAvg('interactions', 'rating')
            ->orderBy('created_at', 'desc')
            ->limit(24)
            ->get()
            ->filter(function ($recipe) {
                return VideoEmbed::inlinePlayable($recipe->video_url)
                    || VideoEmbed::embedUrl($recipe->video_url);
            })
            ->map(function ($recipe) {
                // Mark videos whose title should stay hidden on the slider.
                $title = Str::lower(trim($recipe->title ?? ''));

                if (Str::contains($title, 'pre-coding routine') || Str::contains($title, 'فيديو قصير جديد')) {
                    $recipe->hide_title = true;
                }

                return $recipe;
            })
            ->values()
            ->take(8);

        $authenticatedUser = auth()->user();

        // إضافة حالة الحفظ للمستخدمين المسجلين
        if ($authenticatedUser) {
            $user = $authenticatedUser;
            $savedRecipes = $user->interactions()
                ->where('is_saved', true)
                ->pluck('recipe_id')
                ->toArray();

            $latestRecipes->each(function ($recipe) use ($savedRecipes) {
                $recipe->is_saved = in_array($recipe->recipe_id, $savedRecipes);
            });

            $feedVideos->each(function ($recipe) use ($savedRecipes) {
                $recipe->is_saved = in_array($recipe->recipe_id, $savedRecipes);
            });
        } else {
            $latestRecipes->each(function ($recipe) {
                $recipe->is_saved = false;
            });

            $feedVideos->each(function ($recipe) {
                $recipe->is_saved = false;
            });
        }

        $homeTools = Tool::active()->ordered()->limit(12)->get();

        $heroMedia = [
            'workshop' => $this->resolveWorkshopHeroMedia($featuredWorkshop, $workshops),
            'recipe' => $this->resolveRecipeHeroMedia($feedVideos, $latestRecipes),
            'tool' => $this->resolveToolHeroMedia(),
            'links' => $this->resolveLinksHeroMedia(),
        ];

        $isAuthenticated = (bool) $authenticatedUser;
        $isChefUser = $authenticatedUser?->isChef() ?? false;

        $createWorkshopAction = [
            'label' => $isChefUser ? 'أطلق تجربتك الآن' : ($isAuthenticated ? 'أكمل ملفك كخبير وأطلق تجربتك' : 'انضم كخبير وأطلق تجربتك'),
            'url' => $isChefUser ? route('chef.workshops.create') : ($isAuthenticated ? route('onboarding.show') : route('login')),
            'icon' => $isChefUser ? 'fas fa-plus-circle' : 'fas fa-user-plus',
            'type' => 'accent',
            'open_in_new_tab' => false,
        ];

        $createWasfahLinkAction = [
            'label' => $isChefUser ? 'أنشئ Peahskill Link الآن' : ($isAuthenticated ? 'أكمل ملفك لتفعيل Peahskill Links' : 'سجل وابدأ Peahskill Links'),
            'url' => $isChefUser ? route('chef.links.edit') : ($isAuthenticated ? route('onboarding.show') : route('register')),
            'icon' => 'fas fa-link',
            'type' => 'primary',
            'open_in_new_tab' => false,
        ];

        $managedHeroSlides = $this->loadManagedHeroSlides();
        $heroSlides = $this->transformHeroSlides($managedHeroSlides, $heroMedia, $createWorkshopAction, $createWasfahLinkAction);

        $bookedWorkshopIds = [];

        if ($authenticatedUser) {
            $bookedWorkshopIds = $authenticatedUser
                ->workshopBookings()
                ->pluck('workshop_id')
                ->unique()
                ->values()
                ->all();
        }

        return view('home', compact(
            'workshops',
            'featuredWorkshop',
            'latestWorkshops',
            'feedVideos',
            'hasUpcomingWorkshops',
            'heroMedia',
            'heroSlides',
            'homeTools',
            'bookedWorkshopIds'
        ));
    }

    protected function loadManagedHeroSlides(): Collection
    {
        if (!HeroSlideSchemaState::isReady()) {
            return collect();
        }

        try {
            return HeroSlide::active()->ordered()->get();
        } catch (QueryException $exception) {
            Log::warning('Failed to load managed hero slides, falling back to defaults.', [
                'sql_state' => $exception->errorInfo[0] ?? null,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * حدد صور شريحة الورش
     */
    protected function resolveWorkshopHeroMedia(?Workshop $featuredWorkshop, Collection $workshops): array
    {
        $candidates = collect([$featuredWorkshop])->merge($workshops)->filter();

        $withImages = $candidates->filter(function ($workshop) {
            return $workshop && !empty($workshop->image);
        });

        if ($withImages->isEmpty()) {
            $fallback = Workshop::active()->whereNotNull('image')->inRandomOrder()->first();
            if ($fallback) {
                $withImages = collect([$fallback]);
            }
        }

        $selected = $withImages->isNotEmpty() ? $withImages->random() : null;

        $desktop = $this->makeImageUrl(optional($selected)->image, asset('image/wterm.webp'));
        $mobile = $desktop;

        if ($selected) {
            $gallery = collect($selected->images ?? [])->filter();
            if ($gallery->isNotEmpty()) {
                $mobile = $this->makeImageUrl($gallery->random(), $desktop);
            }
        }

        return [
            'desktop' => $desktop,
            'mobile' => $mobile,
        ];
    }

    /**
     * حدد صور شريحة الوصفات
     */
    protected function resolveRecipeHeroMedia(Collection $highlightedRecipes, Collection $latestRecipes): array
    {
        $candidates = collect();

        if ($highlightedRecipes->isNotEmpty()) {
            $candidates = $candidates->merge($highlightedRecipes);
        }

        if ($latestRecipes->isNotEmpty()) {
            $candidates = $candidates->merge($latestRecipes);
        }

        if ($candidates->isEmpty()) {
            $randomRecipe = Recipe::approved()->public()->with(['category'])->inRandomOrder()->first();
            if ($randomRecipe) {
                $candidates = collect([$randomRecipe]);
            }
        }

        $selected = $candidates->isNotEmpty() ? $candidates->random() : null;

        $images = $selected ? collect($selected->getAllImages())->filter() : collect();

        $desktop = $images->isNotEmpty()
            ? $images->random()
            : $this->makeImageUrl(optional($selected)->image_url, asset('image/brownies.webp'));

        $mobile = $images->isNotEmpty()
            ? $images->random()
            : $desktop;

        return [
            'desktop' => $desktop,
            'mobile' => $mobile,
        ];
    }

    /**
     * حدد صور شريحة أدوات الشيف
     */
    protected function resolveToolHeroMedia(): array
    {
        $tool = Tool::active()->inRandomOrder()->first();

        $desktop = $tool ? $tool->image_url : asset('image/tnl.png');

        $gallery = $tool ? collect($tool->gallery_image_urls ?? [])->filter() : collect();

        $mobile = $gallery->isNotEmpty()
            ? $gallery->random()
            : $desktop;

        return [
            'desktop' => $desktop,
            'mobile' => $mobile,
        ];
    }

    /**
     * حدد صور شريحة Wasfah Links
     */
    protected function resolveLinksHeroMedia(): array
    {
        $fallback = asset('image/wasfah-links.webm');

        return [
            'desktop' => $fallback,
            'mobile' => $fallback,
        ];
    }

    /**
     * تهيئة رابط الصورة
     */
    protected function makeImageUrl(?string $path, string $fallback): string
    {
        if (!$path) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    protected function transformHeroSlides(Collection $managedSlides, array $heroMedia, array $createWorkshopAction, array $createWasfahLinkAction): array
    {
        if ($managedSlides->isEmpty()) {
            return $this->defaultHeroSlides($heroMedia, $createWorkshopAction, $createWasfahLinkAction);
        }

        $fallbackDesktop = data_get($heroMedia, 'workshop.desktop', asset('image/wterm.webp'));
        $fallbackMobile = data_get($heroMedia, 'workshop.mobile', $fallbackDesktop);

        return $managedSlides
            ->map(function (HeroSlide $slide) use ($fallbackDesktop, $fallbackMobile, $createWorkshopAction, $createWasfahLinkAction) {
                $desktop = $slide->desktop_image_url ?? $fallbackDesktop;
                $mobile = $slide->mobile_image_url ?? $slide->desktop_image_url ?? $fallbackMobile;

                $features = collect($slide->features ?? [])->filter()->values()->all();

                $actions = collect($slide->actions ?? [])
                    ->map(fn ($action) => $this->resolveHeroSlideAction($action, $createWorkshopAction, $createWasfahLinkAction))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'badge' => $slide->badge,
                    'title' => $slide->title,
                    'description' => $slide->description,
                    'features' => $features,
                    'image' => $desktop,
                    'mobile_image' => $mobile,
                    'image_alt' => $slide->image_alt ?: $slide->title,
                    'actions' => $actions,
                ];
            })
            ->map(fn (array $slide) => $this->localizeHeroSlide($slide))
            ->all();
    }

    protected function defaultHeroSlides(array $heroMedia, array $createWorkshopAction, array $createWasfahLinkAction): array
    {
        $defaults = trans('home.hero.defaults');

        $workshops = data_get($defaults, 'workshops', []);
        $chef = data_get($defaults, 'chef', []);
        $links = data_get($defaults, 'links', []);
        $tools = data_get($defaults, 'tools', []);
        $recipes = data_get($defaults, 'recipes', []);

        $workshopFeatures = array_values(array_filter(data_get($workshops, 'features', [])));
        $workshopActions = data_get($workshops, 'actions', []);

        $linksFeatures = array_values(array_filter(data_get($links, 'features', [])));
        $linksActions = data_get($links, 'actions', []);
        $linksSecondaryLabel = data_get($linksActions, 'secondary', 'استعرض Wasfah Links');

        $toolFeatures = array_values(array_filter(data_get($tools, 'features', [])));
        $toolActions = data_get($tools, 'actions', []);

        $recipeFeatures = array_values(array_filter(data_get($recipes, 'features', [])));
        $recipeActions = data_get($recipes, 'actions', []);

        $slides = [
            [
                'badge' => data_get($workshops, 'badge', 'ورشات العمل'),
                'title' => data_get($workshops, 'title', 'ورشات حلويات احترافية'),
                'description' => data_get($workshops, 'description', 'ورشات مباشرة بخطوات واضحة من شيفات مختصين.'),
                'features' => $workshopFeatures,
                'image' => data_get($heroMedia, 'workshop.desktop', asset('image/wterm.webp')),
                'mobile_image' => data_get($heroMedia, 'workshop.mobile', data_get($heroMedia, 'workshop.desktop', asset('image/wterm.webp'))),
                'image_alt' => data_get($workshops, 'image_alt', 'ورشة عمل للحلويات الاحترافية'),
                'actions' => [
                    [
                        'label' => data_get($workshopActions, 'primary', 'استكشف الورشات'),
                        'url' => route('workshops'),
                        'icon' => 'fas fa-chalkboard-teacher',
                        'type' => 'primary',
                        'open_in_new_tab' => false,
                    ],
                    [
                        'label' => data_get($workshopActions, 'secondary', 'جدول الورشات'),
                        'url' => route('workshops'),
                        'icon' => 'fas fa-calendar-alt',
                        'type' => 'secondary',
                        'open_in_new_tab' => false,
                    ],
                ],
            ],
            [
                'badge' => data_get($chef, 'badge', 'للشيفات'),
                'title' => data_get($chef, 'title', 'أنشئ ورشتك على وصفة'),
                'description' => data_get($chef, 'description', 'أطلق ورشتك الاحترافية مع نظام حجوزات مدمج وأدوات تسويق مصممة للشيفات.'),
                'features' => array_values(array_filter(data_get($chef, 'features', []))),
                'image' => data_get($heroMedia, 'chef.desktop', data_get($heroMedia, 'workshop.desktop', asset('image/wterm.webp'))),
                'mobile_image' => data_get($heroMedia, 'chef.mobile', data_get($heroMedia, 'chef.desktop', data_get($heroMedia, 'workshop.desktop', asset('image/wterm.webp')))),
                'image_alt' => data_get($chef, 'image_alt', 'شيف يطلق ورشته الخاصة'),
                'actions' => [
                    $createWorkshopAction,
                ],
            ],
            [
                'badge' => data_get($links, 'badge', 'Wasfah Links'),
                'title' => data_get($links, 'title', 'Wasfah Links للشيفات'),
                'description' => data_get($links, 'description', 'اجمع ورشاتك وروابطك المهمة في صفحة واحدة قابلة للمشاركة مع متابعيك.'),
                'features' => $linksFeatures,
                'image' => data_get($heroMedia, 'links.desktop', asset('image/wasfah-links.webm')),
                'mobile_image' => data_get($heroMedia, 'links.mobile', data_get($heroMedia, 'links.desktop', asset('image/wasfah-links.webm'))),
                'image_alt' => data_get($links, 'image_alt', 'صفحة Wasfah Links للشيف'),
                'actions' => [
                    $createWasfahLinkAction,
                    [
                        'label' => $linksSecondaryLabel,
                        'url' => route('links'),
                        'icon' => 'fas fa-eye',
                        'type' => 'secondary',
                        'open_in_new_tab' => false,
                    ],
                ],
            ],
            [
                'badge' => data_get($tools, 'badge', 'أدوات الشيف'),
                'title' => data_get($tools, 'title', 'دليل أدوات الشيف'),
                'description' => data_get($tools, 'description', 'اختيارات دقيقة لأدوات تساعدك على الإتقان.'),
                'features' => $toolFeatures,
                'image' => data_get($heroMedia, 'tool.desktop', asset('image/tnl.png')),
                'mobile_image' => data_get($heroMedia, 'tool.mobile', data_get($heroMedia, 'tool.desktop', asset('image/tnl.png'))),
                'image_alt' => data_get($tools, 'image_alt', 'مجموعة أدوات لتحضير الحلويات'),
                'actions' => [
                    [
                        'label' => data_get($toolActions, 'primary', 'استعرض أدوات الشيف'),
                        'url' => route('tools'),
                        'icon' => 'fas fa-toolbox',
                        'type' => 'primary',
                        'open_in_new_tab' => false,
                    ],
                    [
                        'label' => data_get($toolActions, 'secondary', 'الأدوات المحفوظة'),
                        'url' => route('saved.index'),
                        'icon' => 'fas fa-heart',
                        'type' => 'secondary',
                        'open_in_new_tab' => false,
                    ],
                ],
            ],
            [
                'badge' => data_get($recipes, 'badge', 'الوصفات'),
                'title' => data_get($recipes, 'title', 'مكتبة وصفات عالمية'),
                'description' => data_get($recipes, 'description', 'وصفات فاخرة مجرَّبة مع شرح مصوَّر ونصائح مختصرة.'),
                'features' => $recipeFeatures,
                'image' => data_get($heroMedia, 'recipe.desktop', asset('image/brownies.webp')),
                'mobile_image' => data_get($heroMedia, 'recipe.mobile', data_get($heroMedia, 'recipe.desktop', asset('image/brownies.webp'))),
                'image_alt' => data_get($recipes, 'image_alt', 'حلى براونيز فاخرة'),
                'actions' => [
                    [
                        'label' => data_get($recipeActions, 'primary', 'ابدأ اكتشاف الوصفات'),
                        'url' => route('recipes'),
                        'icon' => 'fas fa-utensils',
                        'type' => 'primary',
                        'open_in_new_tab' => false,
                    ],
                    [
                        'label' => data_get($recipeActions, 'secondary', 'الوصفات المحفوظة'),
                        'url' => route('saved.index'),
                        'icon' => 'fas fa-bookmark',
                        'type' => 'secondary',
                        'open_in_new_tab' => false,
                    ],
                ],
            ],
        ];

        return array_map(
            fn (array $slide) => $this->localizeHeroSlide($slide),
            $slides
        );
    }

    protected function resolveHeroSlideAction(array $action, array $createWorkshopAction, array $createWasfahLinkAction): ?array
    {
        $behavior = $action['behavior'] ?? 'static';

        if (in_array($behavior, ['create_workshop', 'create_wasfah_link'], true)) {
            $base = $behavior === 'create_workshop' ? $createWorkshopAction : $createWasfahLinkAction;

            if (!empty($action['label'])) {
                $base['label'] = $action['label'];
            }

            if (!empty($action['icon'])) {
                $base['icon'] = $action['icon'];
            }

            if (!empty($action['type'])) {
                $base['type'] = $action['type'];
            }

            if (!empty($action['url'])) {
                $base['url'] = $action['url'];
            }

            if (array_key_exists('open_in_new_tab', $action)) {
                $base['open_in_new_tab'] = (bool) $action['open_in_new_tab'];
            }

            return $base;
        }

        $label = $action['label'] ?? null;
        $url = $action['url'] ?? null;

        if (!$label || !$url) {
            return null;
        }

        return [
            'label' => $label,
            'url' => $url,
            'icon' => $action['icon'] ?? null,
            'type' => $action['type'] ?? 'primary',
            'open_in_new_tab' => (bool) ($action['open_in_new_tab'] ?? false),
        ];
    }

    /**
     * Apply locale-specific replacements to hero slide content.
     */
    protected function localizeHeroSlide(array $slide): array
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return $slide;
        }

        $translate = fn (?string $value) => $this->translateContentString($value, $locale);

        $slide['badge'] = $translate($slide['badge'] ?? null);
        $slide['title'] = $translate($slide['title'] ?? null);
        $slide['description'] = $translate($slide['description'] ?? null);
        $slide['image_alt'] = $translate($slide['image_alt'] ?? null);

        if (!empty($slide['features'])) {
            $slide['features'] = array_map(
                fn ($feature) => $translate($feature),
                $slide['features']
            );
        }

        if (!empty($slide['actions'])) {
            $slide['actions'] = array_map(function ($action) use ($translate) {
                if (!empty($action['label'])) {
                    $action['label'] = $translate($action['label']);
                }

                return $action;
            }, $slide['actions']);
        }

        return $slide;
    }

    /**
     * Retrieve translation mapping from config/content-translations.php.
     */
    protected function translateContentString(?string $text, string $locale): ?string
    {
        if (!$text || $locale === 'ar') {
            return $text;
        }

        static $dictionaries = [];

        if (!array_key_exists($locale, $dictionaries)) {
            $dictionaries[$locale] = data_get(config('content-translations.locales'), $locale, []);
        }

        $dictionary = $dictionaries[$locale] ?? [];
        $trimmed = trim($text);

        return $dictionary[$trimmed] ?? $text;
    }
}


