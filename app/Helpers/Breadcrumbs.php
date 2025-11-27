<?php

namespace App\Helpers;

use App\Models\Category;
use Illuminate\Support\Facades\Route;

class Breadcrumbs
{
    /**
     * Routes that should not render breadcrumbs.
     *
     * @var list<string>
     */
    private const HOME_ROUTE_NAMES = ['home', 'home.ar', 'home.en'];

    /**
     * Build breadcrumb trail for the current route.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    public static function generate(): array
    {
        $route = Route::current();

        if (!$route) {
            return [];
        }

        $routeName = $route->getName();

        if (!$routeName || in_array($routeName, self::HOME_ROUTE_NAMES, true)) {
            return [];
        }

        $breadcrumbs = [
            [
                'label' => self::translateLabel('breadcrumbs.home', 'الرئيسية'),
                'url' => route('home'),
            ],
        ];

        if (str_starts_with($routeName, 'admin.')) {
            $breadcrumbs = array_merge(
                $breadcrumbs,
                self::adminTrail($routeName)
            );
        } elseif (str_starts_with($routeName, 'chef.')) {
            $breadcrumbs = array_merge(
                $breadcrumbs,
                self::chefTrail($routeName)
            );
        } else {
            $breadcrumbs = array_merge(
                $breadcrumbs,
                self::siteTrail($routeName)
            );
        }

        $breadcrumbs = self::collapseConsecutiveDuplicates($breadcrumbs);

        if (count($breadcrumbs) <= 1) {
            return [];
        }

        // Ensure only the last breadcrumb is non-clickable.
        $lastIndex = count($breadcrumbs) - 1;
        foreach ($breadcrumbs as $index => &$breadcrumb) {
            if ($index === $lastIndex) {
                $breadcrumb['url'] = null;
            }
        }
        unset($breadcrumb);

        return $breadcrumbs;
    }

    /**
     * Resolve breadcrumbs for public site routes.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function siteTrail(string $routeName): array
    {
        $definitions = self::siteRouteDefinitions();

        if (array_key_exists($routeName, $definitions)) {
            return self::buildTrailFromDefinition($definitions[$routeName]);
        }

        return [
            [
                'label' => self::titleCaseLastSegment($routeName),
                'url' => null,
            ],
        ];
    }

    /**
     * Map public routes to breadcrumb builders.
     *
     * @return array<string, callable|array<int, array<string, mixed>>>
     */
    protected static function siteRouteDefinitions(): array
    {
        $brandLinksLabel = 'روابط ' . config('app.name', 'Peahskill');

        return [
            'recipes' => [self::class, 'recipesTrail'],
            'recipe.show' => [self::class, 'recipeTrail'],
            'tools' => [self::class, 'toolsTrail'],
            'tools.show' => [self::class, 'toolDetailsTrail'],
            'workshops' => [self::class, 'workshopsTrail'],
            'workshops.search' => static fn (): array => self::workshopsTrail(true),
            'workshop.show' => [self::class, 'workshopTrail'],
            'search' => self::singleCrumbTrail(self::translateLabel('breadcrumbs.search', 'نتائج البحث')),
            'saved.index' => self::singleCrumbTrail('الأدوات المحفوظة'),
            'profile' => self::singleCrumbTrail('الملف الشخصي'),
            'profile.statistics' => [
                self::crumb('الملف الشخصي', 'profile'),
                self::crumb('إحصائيات الحساب'),
            ],
            'profile.activity' => [
                self::crumb('الملف الشخصي', 'profile'),
                self::crumb('سجل النشاط'),
            ],
            'meetings.index' => self::singleCrumbTrail('اجتماعاتي'),
            'bookings.index' => self::singleCrumbTrail(
                self::translateLabel('breadcrumbs.bookings.index', 'حجوزاتي')
            ),
            'bookings.show' => [
                self::crumb(
                    self::translateLabel('breadcrumbs.bookings.index', 'حجوزاتي'),
                    'bookings.index'
                ),
                [
                    'label' => static function () {
                        $booking = request()->route('booking');
                        $code = $booking->public_code ?? $booking->id ?? null;

                        if ($code) {
                            return self::translateLabel(
                                'breadcrumbs.bookings.show.with_code',
                                'تفاصيل الحجز #:code',
                                ['code' => $code]
                            );
                        }

                        return self::translateLabel(
                            'breadcrumbs.bookings.show.title',
                            'تفاصيل الحجز'
                        );
                    },
                ],
            ],
            'bookings.join' => [
                self::crumb(
                    self::translateLabel('breadcrumbs.bookings.index', 'حجوزاتي'),
                    'bookings.index'
                ),
                [
                    'label' => static function () {
                        $booking = request()->route('booking');
                        $title = $booking?->workshop?->title;

                        if ($title) {
                            return self::translateLabel(
                                'breadcrumbs.bookings.join.with_title',
                                'غرفة ورشة: :title',
                                ['title' => $title]
                            );
                        }

                        return self::translateLabel(
                            'breadcrumbs.bookings.join.title',
                            'غرفة الورشة'
                        );
                    },
                ],
            ],
            'links' => self::singleCrumbTrail($brandLinksLabel),
            'links.chef' => [
                self::crumb($brandLinksLabel, 'links'),
                [
                    'label' => static function () {
                        $page = request()->route('chefLinkPage');

                        if (!$page) {
                            return 'صفحة روابط الشيف';
                        }

                        $name = $page->user?->name;
                        $headline = $page->headline ?? null;
                        $display = $name ?: $headline ?: ($page->slug ?? null);

                        return $display
                            ? 'روابط الشيف ' . $display
                            : 'صفحة روابط الشيف';
                    },
                ],
            ],
            'chefs.show' => [
                [
                    'label' => static function () {
                        $chef = request()->route('chef');
                        $name = $chef?->name;

                        return $name
                            ? 'بروفايل الشيف ' . $name
                            : 'بروفايل الشيف';
                    },
                ],
            ],
            'notifications.index' => self::singleCrumbTrail('مركز الإشعارات'),
            'referrals.dashboard' => self::singleCrumbTrail(
                self::translateLabel('breadcrumbs.referrals.dashboard', 'برنامج الشركاء')
            ),
            'legal.terms' => self::singleCrumbTrail('الشروط والأحكام'),
            'legal.privacy' => self::singleCrumbTrail('سياسة الخصوصية'),
            'about' => self::singleCrumbTrail('عن وصفة'),
            'baking-tips' => self::singleCrumbTrail('نصائح الخَبز'),
            'advertising' => self::singleCrumbTrail('الإعلان مع وصفة'),
            'partnership' => self::singleCrumbTrail(
                self::translateLabel('breadcrumbs.partnership', 'Peahskill Partner')
            ),
            'contact' => self::singleCrumbTrail('اتصل بنا'),
            'login' => self::singleCrumbTrail('تسجيل الدخول'),
            'register' => self::singleCrumbTrail('إنشاء حساب'),
            'register.verify.show' => [
                self::crumb('إنشاء حساب', 'register'),
                self::crumb('تأكيد البريد الإلكتروني'),
            ],
            'onboarding.show' => self::singleCrumbTrail('إكمال بيانات الشيف'),
            'policy-consent.show' => self::singleCrumbTrail('الموافقة على السياسات'),
        ];
    }

    /**
     * Build a simple breadcrumb trail with one entry.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function singleCrumbTrail(string $label, ?string $routeName = null): array
    {
        return [
            self::crumb($label, $routeName),
        ];
    }

    /**
     * Define a breadcrumb entry.
     *
     * @param string|callable $label
     * @return array<string, mixed>
     */
    protected static function crumb($label, ?string $routeName = null, array $parameters = []): array
    {
        $route = null;

        if ($routeName) {
            $route = [
                'name' => $routeName,
                'parameters' => $parameters,
            ];
        }

        return [
            'label' => $label,
            'route' => $route,
        ];
    }

    /**
     * Turn a route definition into breadcrumb entries.
     *
     * @param callable|array<int, array<string, mixed>> $definition
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function buildTrailFromDefinition($definition): array
    {
        if (is_callable($definition)) {
            $definition = $definition();
        }

        if (!is_array($definition)) {
            return [];
        }

        $trail = [];

        foreach ($definition as $crumb) {
            if (!is_array($crumb)) {
                continue;
            }

            $normalized = self::normalizeCrumbDefinition($crumb);

            if ($normalized) {
                $trail[] = $normalized;
            }
        }

        return $trail;
    }

    /**
     * Normalize a crumb definition to the required shape.
     *
     * @param array<string, mixed> $crumb
     */
    protected static function normalizeCrumbDefinition(array $crumb): ?array
    {
        if (!array_key_exists('label', $crumb)) {
            return null;
        }

        try {
            $label = is_callable($crumb['label'])
                ? $crumb['label']()
                : $crumb['label'];
        } catch (\Throwable $th) {
            return null;
        }

        if ($label === null || $label === '') {
            return null;
        }

        $label = is_string($label) ? trim($label) : (string) $label;

        if ($label === '') {
            return null;
        }

        $url = $crumb['url'] ?? null;

        if ($url === null && array_key_exists('route', $crumb)) {
            $url = self::routeUrl($crumb['route']);
        }

        return [
            'label' => $label,
            'url' => $url,
        ];
    }

    /**
     * Breadcrumbs for the recipes listing.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function recipesTrail(): array
    {
        $recipesLabel = self::translateLabel('breadcrumbs.recipes', 'الوصفات');

        $trail = [
            [
                'label' => $recipesLabel,
                'url' => null,
            ],
        ];

        $categoryId = request()->get('category');

        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $trail[0]['url'] = route('recipes');

                $trail[] = [
                    'label' => $category->name,
                    'url' => null,
                ];
            }
        }

        return $trail;
    }

    /**
     * Breadcrumbs for a single recipe page.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function recipeTrail(): array
    {
        $recipesLabel = self::translateLabel('breadcrumbs.recipes', 'الوصفات');

        $trail = [
            [
                'label' => $recipesLabel,
                'url' => route('recipes'),
            ],
        ];

        $recipe = request()->route('recipe');

        if ($recipe) {
            $trail[] = [
                'label' => $recipe->title,
                'url' => null,
            ];
        }

        return $trail;
    }

    /**
     * Breadcrumbs for the tools listing.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function toolsTrail(): array
    {
        return [
            [
                'label' => 'أدوات الطبخ',
                'url' => null,
            ],
        ];
    }

    /**
     * Breadcrumbs for a single tool page.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function toolDetailsTrail(): array
    {
        $trail = [
            [
                'label' => 'أدوات الطبخ',
                'url' => route('tools'),
            ],
        ];

        $tool = request()->route('tool');
        if ($tool && isset($tool->name)) {
            $trail[] = [
                'label' => $tool->name,
                'url' => null,
            ];
        }

        return $trail;
    }

    /**
     * Breadcrumbs for the workshops listing or search.
     *
     * @param bool $isSearch
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function workshopsTrail(bool $isSearch = false): array
    {
        $workshopsLabel = self::translateLabel('breadcrumbs.workshops', 'Workshops');

        $trail = [
            [
                'label' => $workshopsLabel,
                'url' => $isSearch ? route('workshops') : null,
            ],
        ];

        if ($isSearch) {
            $trail[] = [
                'label' => 'نتائج البحث',
                'url' => null,
            ];
        }

        return $trail;
    }

    /**
     * Breadcrumbs for a single workshop page.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function workshopTrail(): array
    {
        $workshopsLabel = self::translateLabel('breadcrumbs.workshops', 'Workshops');

        $trail = [
            [
                'label' => $workshopsLabel,
                'url' => route('workshops'),
            ],
        ];

        $workshop = request()->route('workshop');
        if ($workshop && isset($workshop->title)) {
            $trail[] = [
                'label' => $workshop->title,
                'url' => null,
            ];
        }

        return $trail;
    }

    /**
     * Build breadcrumbs for admin routes.
     */
    protected static function adminTrail(string $routeName): array
    {
        $trail = [
            [
                'label' => self::translateLabel('breadcrumbs.admin.area', 'منطقة الإدمن'),
                'url' => self::routeUrl('admin.dashboard'),
            ],
        ];

        $segments = explode('.', $routeName);
        array_shift($segments); // Remove "admin"

        if (empty($segments)) {
            return $trail;
        }

        $sectionKey = array_shift($segments);
        $sectionBreadcrumb = self::sectionBreadcrumb($sectionKey, self::adminSections());

        if ($sectionBreadcrumb) {
            $trail[] = $sectionBreadcrumb;
        }

        if (!empty($segments)) {
            $trail = array_merge($trail, self::adminActionTrail($sectionKey, $segments));
        }

        return $trail;
    }

    /**
     * Build breadcrumbs for chef routes.
     */
    protected static function chefTrail(string $routeName): array
    {
        $trail = [
            [
                'label' => self::translateLabel('breadcrumbs.chef.area', 'Peahskill zone'),
                'url' => self::routeUrl('chef.dashboard'),
            ],
        ];

        $segments = explode('.', $routeName);
        array_shift($segments); // Remove "chef"

        if (empty($segments)) {
            return $trail;
        }

        $sectionKey = array_shift($segments);
        $sectionBreadcrumb = self::sectionBreadcrumb($sectionKey, self::chefSections());

        if ($sectionBreadcrumb) {
            $trail[] = $sectionBreadcrumb;
        }

        if (!empty($segments)) {
            $trail = array_merge($trail, self::chefActionTrail($sectionKey, $segments));
        }

        return $trail;
    }

    /**
     * Remove consecutive breadcrumbs that point to the same destination.
     *
     * @param array<int, array{label: string, url: string|null}> $trail
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function collapseConsecutiveDuplicates(array $trail): array
    {
        if (count($trail) < 2) {
            return $trail;
        }

        $collapsed = [];

        foreach ($trail as $crumb) {
            if (!isset($crumb['label'])) {
                continue;
            }

            $crumb = [
                'label' => $crumb['label'],
                'url' => $crumb['url'] ?? null,
            ];

            $lastIndex = count($collapsed) - 1;
            $last = $lastIndex >= 0 ? $collapsed[$lastIndex] : null;

            if ($last && self::crumbsPointToSameTarget($last, $crumb)) {
                $collapsed[$lastIndex] = [
                    'label' => $crumb['label'],
                    'url' => $crumb['url'] ?? $last['url'],
                ];
                continue;
            }

            $collapsed[] = $crumb;
        }

        return $collapsed;
    }

    /**
     * Determine if two breadcrumbs represent the same target.
     *
     * @param array{label: string, url: string|null} $first
     * @param array{label: string, url: string|null} $second
     */
    protected static function crumbsPointToSameTarget(array $first, array $second): bool
    {
        $firstUrl = $first['url'] ?? null;
        $secondUrl = $second['url'] ?? null;

        if ($firstUrl !== null || $secondUrl !== null) {
            return $firstUrl !== null
                && $secondUrl !== null
                && $firstUrl === $secondUrl;
        }

        return $first['label'] === $second['label'];
    }

    /**
     * Map admin sections to readable labels and default routes.
     *
     * @return array<string, array{label: string, route?: string}>
     */
    protected static function adminSections(): array
    {
        return [
            'dashboard' => [
                'label' => 'منطقة الإدارة (لوحة التحكم)',
                'route' => 'admin.dashboard',
            ],
            'admin-area' => [
                'label' => 'منطقة الإدارة (لوحة التحكم)',
                'route' => 'admin.admin-area',
            ],
            'tools' => [
                'label' => 'أدوات المطبخ',
                'route' => 'admin.tools.index',
            ],
            'workshops' => [
                'label' => 'ورشات العمل',
                'route' => 'admin.workshops.index',
            ],
            'bookings' => [
                'label' => 'حجوزات الورش',
                'route' => 'admin.bookings.index',
            ],
            'recipes' => [
                'label' => 'الوصفات',
                'route' => 'admin.recipes.index',
            ],
            'users' => [
                'label' => 'إدارة المستخدمين',
                'route' => 'admin.users.index',
            ],
            'referrals' => [
                'label' => self::translateLabel('breadcrumbs.admin.sections.referrals', 'برنامج الإحالات'),
                'route' => 'admin.referrals.index',
            ],
            'visibility' => [
                'label' => 'إعدادات الظهور',
                'route' => 'admin.visibility.index',
            ],
            'hero-slides' => [
                'label' => 'سلايدر الهيرو',
                'route' => 'admin.hero-slides.index',
            ],
            'contact-messages' => [
                'label' => 'رسائل التواصل',
                'route' => 'admin.contact-messages.index',
            ],
            'chefs' => [
                'label' => 'طلبات الشيفات',
                'route' => 'admin.chefs.requests',
            ],
        ];
    }

    /**
     * Map chef sections to readable labels and default routes.
     *
     * @return array<string, array{label: string, route?: string}>
     */
    protected static function chefSections(): array
    {
        return [
            'dashboard' => [
                'label' => self::translateLabel('breadcrumbs.chef.sections.dashboard', 'Peahskill dashboard'),
                'route' => 'chef.dashboard',
            ],
            'links' => [
                'label' => self::translateLabel(
                    'breadcrumbs.chef.sections.links',
                    config('app.name', 'Peahskill') . ' links'
                ),
                'route' => 'chef.links.edit',
            ],
            'recipes' => [
                'label' => self::translateLabel('breadcrumbs.chef.sections.recipes', 'My recipes'),
                'route' => 'chef.recipes.index',
            ],
            'workshops' => [
                'label' => self::translateLabel('breadcrumbs.chef.sections.workshops', 'Peahskill workshops'),
                'route' => 'chef.workshops.index',
            ],
        ];
    }

    /**
     * Convert a section key into a breadcrumb entry.
     *
     * @param array<string, array{label: string, route?: string}> $map
     */
    protected static function sectionBreadcrumb(?string $sectionKey, array $map): ?array
    {
        if (!$sectionKey) {
            return null;
        }

        $section = $map[$sectionKey] ?? null;
        $label = $section['label'] ?? self::titleCaseLastSegment($sectionKey);
        $url = $section['route'] ?? null;

        return [
            'label' => $label,
            'url' => $url ? self::routeUrl($url) : null,
        ];
    }

    /**
     * Generate action-specific crumbs for admin sections.
     *
     * @param array<int, string> $segments
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function adminActionTrail(string $sectionKey, array $segments): array
    {
        $action = $segments[0] ?? null;

        if (!$action || in_array($action, ['index', 'store', 'update', 'destroy'], true)) {
            return [];
        }

        switch ($sectionKey) {
            case 'tools':
                return self::resourceActionTrail($action, 'tool', [
                    'create' => 'إضافة أداة جديدة',
                    'show' => fn ($tool) => 'تفاصيل: ' . self::modelLabel($tool, 'name', 'الأداة'),
                    'edit' => fn ($tool) => 'تعديل: ' . self::modelLabel($tool, 'name', 'الأداة'),
                ]);

            case 'workshops':
                return self::resourceActionTrail($action, 'workshop', [
                    'create' => 'إضافة ورشة جديدة',
                    'show' => fn ($workshop) => 'تفاصيل: ' . self::modelLabel($workshop, 'title', 'الورشة'),
                    'edit' => fn ($workshop) => 'تعديل: ' . self::modelLabel($workshop, 'title', 'الورشة'),
                    'meeting' => fn ($workshop) => 'رابط الاجتماع - ' . self::modelLabel($workshop, 'title', 'الورشة'),
                ]);

            case 'recipes':
                return self::resourceActionTrail($action, 'recipe', [
                    'create' => 'إضافة وصفة جديدة',
                    'show' => fn ($recipe) => 'تفاصيل: ' . self::modelLabel($recipe, 'title', 'الوصفة'),
                    'edit' => fn ($recipe) => 'تعديل: ' . self::modelLabel($recipe, 'title', 'الوصفة'),
                ]);

            case 'bookings':
                $trail = self::resourceActionTrail($action, 'booking', [
                    'show' => function ($booking) {
                        $id = $booking->id ?? null;
                        return $id ? "تفاصيل الحجز #{$id}" : 'تفاصيل الحجز';
                    },
                ]);

                if ($trail) {
                    return $trail;
                }

                if ($action === 'export') {
                    return [
                        [
                            'label' => 'تصدير الحجوزات',
                            'url' => null,
                        ],
                    ];
                }

                if ($action === 'manual') {
                    return [
                        [
                            'label' => 'إضافة حجز يدوي',
                            'url' => null,
                        ],
                    ];
                }

                if ($action === 'quick-add') {
                    return [
                        [
                            'label' => 'إضافة حجز سريع',
                            'url' => null,
                        ],
                    ];
                }
                break;

            case 'referrals':
                return self::resourceActionTrail($action, 'user', [
                    'show' => fn ($user) => self::translateLabel(
                        'breadcrumbs.admin.referrals.partner_show',
                        'شريك الإحالة: :name',
                        ['name' => self::modelLabel($user, 'name', 'الحساب')]
                    ),
                ]);

            case 'visibility':
                if ($action === 'config') {
                    return [
                        [
                            'label' => 'تهيئة الإعدادات',
                            'url' => null,
                        ],
                    ];
                }
                break;

            case 'hero-slides':
                return self::resourceActionTrail($action, 'heroSlide', [
                    'create' => 'إضافة شريحة جديدة',
                    'edit' => fn ($slide) => 'تعديل: ' . self::modelLabel($slide, 'title', 'الشريحة'),
                ]);

            case 'chefs':
                if ($action === 'requests') {
                    return [];
                }
                break;
        }

        return [
            [
                'label' => self::titleCaseLastSegment($action),
                'url' => null,
            ],
        ];
    }

    /**
     * Generate action-specific crumbs for chef sections.
     *
     * @param array<int, string> $segments
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function chefActionTrail(string $sectionKey, array $segments): array
    {
        $action = $segments[0] ?? null;

        if (!$action || in_array($action, ['index', 'store', 'update', 'destroy'], true)) {
            return [];
        }

        switch ($sectionKey) {
            case 'links':
                if ($action === 'edit') {
                    return [
                        [
                            'label' => self::translateLabel('breadcrumbs.chef.links.edit', 'Edit links page'),
                            'url' => null,
                        ],
                    ];
                }
                return [];

            case 'recipes':
                $recipeFallback = self::translateLabel('breadcrumbs.chef.recipes.fallback_title', 'the recipe');

                return self::resourceActionTrail($action, 'recipe', [
                    'create' => self::translateLabel('breadcrumbs.chef.recipes.create', 'Add new recipe'),
                    'edit' => static function ($recipe) use ($recipeFallback) {
                        $title = self::modelLabel($recipe, 'title', $recipeFallback);

                        return self::translateLabel(
                            'breadcrumbs.chef.recipes.edit',
                            'Edit :title',
                            ['title' => $title]
                        );
                    },
                ]);

            case 'workshops':
                $workshopFallback = self::translateLabel('breadcrumbs.chef.workshops.fallback_title', 'the workshop');

                return self::resourceActionTrail($action, 'workshop', [
                    'create' => self::translateLabel('breadcrumbs.chef.workshops.create', 'Create new workshop'),
                    'edit' => static function ($workshop) use ($workshopFallback) {
                        $title = self::modelLabel($workshop, 'title', $workshopFallback);

                        return self::translateLabel(
                            'breadcrumbs.chef.workshops.edit',
                            'Edit :title',
                            ['title' => $title]
                        );
                    },
                    'earnings' => self::translateLabel('breadcrumbs.chef.workshops.earnings', 'Workshop earnings'),
                    'join' => static function ($workshop) use ($workshopFallback) {
                        $title = self::modelLabel($workshop, 'title', $workshopFallback);

                        return self::translateLabel(
                            'breadcrumbs.chef.workshops.join',
                            'Join :title',
                            ['title' => $title]
                        );
                    },
                ]);
        }

        return [
            [
                'label' => self::titleCaseLastSegment($action),
                'url' => null,
            ],
        ];
    }

    /**
     * Helper to convert CRUD-like actions into breadcrumb entries.
     *
     * @param array<string, string|callable> $labels
     * @return array<int, array{label: string, url: string|null}>
     */
    protected static function resourceActionTrail(?string $action, ?string $paramName, array $labels): array
    {
        if (!$action || !array_key_exists($action, $labels)) {
            return [];
        }

        $labelResolver = $labels[$action];
        $model = $paramName ? request()->route($paramName) : null;
        $label = is_callable($labelResolver)
            ? $labelResolver($model)
            : $labelResolver;

        if (!$label) {
            return [];
        }

        return [
            [
                'label' => $label,
                'url' => null,
            ],
        ];
    }

    /**
     * Safely read a human value from the given model.
     */
    protected static function modelLabel($model, string $attribute, string $fallback): string
    {
        if ($model && isset($model->{$attribute}) && $model->{$attribute} !== '') {
            return (string) $model->{$attribute};
        }

        return $fallback;
    }

    /**
     * Translate a breadcrumb label with graceful fallback and optional replacements.
     *
     * @param array<string, string> $replace
     */
    protected static function translateLabel(string $key, string $fallback, array $replace = []): string
    {
        $translated = trans($key, $replace);

        if ($translated !== $key) {
            return $translated;
        }

        return self::applyReplacements($fallback, $replace);
    }

    /**
     * Apply Laravel-style placeholder replacements to a fallback string.
     *
     * @param array<string, string> $replace
     */
    protected static function applyReplacements(string $text, array $replace): string
    {
        if (empty($replace)) {
            return $text;
        }

        $search = [];
        $values = [];

        foreach ($replace as $key => $value) {
            $search[] = ':' . $key;
            $values[] = (string) $value;
        }

        return str_replace($search, $values, $text);
    }

    /**
     * Resolve a route reference to URL if it exists.
     *
     * @param string|array{name?: string, parameters?: array<string, mixed>}|null $route
     */
    protected static function routeUrl($route): ?string
    {
        if (!$route) {
            return null;
        }

        $name = $route;
        $parameters = [];

        if (is_array($route)) {
            $name = $route['name'] ?? ($route[0] ?? null);
            $parameters = $route['parameters'] ?? ($route[1] ?? []);
        }

        if (!$name || !Route::has($name)) {
            return null;
        }

        try {
            return route($name, $parameters);
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Provide a readable fallback title for unknown routes.
     */
    protected static function titleCaseLastSegment(string $routeName): string
    {
        $segments = explode('.', $routeName);
        $last = end($segments) ?: $routeName;

        return ucwords(str_replace(['-', '_'], ' ', $last));
    }
}
