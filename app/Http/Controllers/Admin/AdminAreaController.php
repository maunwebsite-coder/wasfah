<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\ReferralCommission;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Carbon;

class AdminAreaController extends Controller
{
    /**
     * عرض صفحة منطقة الإدمن الرئيسية
     */
    public function index()
    {
        $recipeCount = Recipe::count();
        $pendingRecipeCount = Recipe::where('status', Recipe::STATUS_PENDING)->count();

        $chefCount = User::where('role', User::ROLE_CHEF)->count();

        $workshopCount = Workshop::count();
        $activeWorkshopCount = Workshop::where('is_active', true)->count();

        $referralPartnerCount = User::where('is_referral_partner', true)->count();
        $pendingReferralAmount = ReferralCommission::where('status', ReferralCommission::STATUS_READY)->sum('commission_amount');

        $metrics = [
            [
                'label' => 'إجمالي الوصفات',
                'value' => $recipeCount,
                'icon' => 'fa-utensils',
                'route' => 'admin.recipes.index',
                'hint' => 'الوصفات المنشورة على المنصة',
            ],
            [
                'label' => 'ورشات العمل',
                'value' => $workshopCount,
                'icon' => 'fa-chalkboard-teacher',
                'route' => 'admin.workshops.index',
                'hint' => 'جميع الورشات النشطة والمنتهية',
            ],
            [
                'label' => 'الشيفات المعتمدون',
                'value' => $chefCount,
                'icon' => 'fa-user-tie',
                'route' => 'admin.chefs.requests',
                'hint' => 'إدارة ملفات الشيفات',
            ],
            [
                'label' => 'ورشات نشطة',
                'value' => $activeWorkshopCount,
                'icon' => 'fa-bolt',
                'route' => 'admin.workshops.index',
                'hint' => 'ورشات متاحة للحجوزات',
            ],
            [
                'label' => 'شركاء الإحالات',
                'value' => $referralPartnerCount,
                'icon' => 'fa-link',
                'route' => 'admin.referrals.index',
                'hint' => 'برنامج الشركاء ومراقبة العمولات',
            ],
        ];

        $attentionItems = [
            [
                'label' => 'وصفات تنتظر الموافقة',
                'value' => $pendingRecipeCount,
                'icon' => 'fa-clipboard-list',
                'route' => 'admin.recipes.index',
                'route_params' => ['status' => Recipe::STATUS_PENDING],
                'cta' => 'مراجعة الوصفات',
                'empty_state' => 'كل الوصفات منشورة',
            ],
            [
                'label' => 'عمولات جاهزة للتحويل',
                'value' => $pendingReferralAmount,
                'icon' => 'fa-coins',
                'route' => 'admin.referrals.index',
                'cta' => 'مراجعة العمولات',
                'empty_state' => 'لا توجد مبالغ جاهزة',
                'format' => 'currency',
            ],
        ];

        $recentRecipes = Recipe::latest()
            ->take(4)
            ->get(['recipe_id', 'title', 'status', 'created_at', 'slug']);

        $upcomingWorkshops = Workshop::whereNotNull('start_date')
            ->where('start_date', '>=', Carbon::now()->subDay())
            ->orderBy('start_date')
            ->take(4)
            ->get(['id', 'title', 'start_date', 'is_online', 'slug']);

        $quickActions = [
            [
                'label' => 'وصفة جديدة',
                'icon' => 'fa-plus-circle',
                'route' => 'admin.recipes.create',
            ],
            [
                'label' => 'ورشة جديدة',
                'icon' => 'fa-chalkboard',
                'route' => 'admin.workshops.create',
            ],
            [
                'label' => 'حجز يدوي',
                'icon' => 'fa-user-plus',
                'route' => 'admin.bookings.manual',
            ],
            [
                'label' => 'إضافة أداة',
                'icon' => 'fa-tools',
                'route' => 'admin.tools.create',
            ],
            [
                'label' => 'شريحة هيرو',
                'icon' => 'fa-images',
                'route' => 'admin.hero-slides.create',
            ],
            [
                'label' => 'برنامج الإحالات',
                'icon' => 'fa-link',
                'route' => 'admin.referrals.index',
            ],
            [
                'label' => 'النظام المالي',
                'icon' => 'fa-file-invoice-dollar',
                'route' => 'admin.finance.dashboard',
            ],
            [
                'label' => 'إدارة المستخدمين',
                'icon' => 'fa-users',
                'route' => 'admin.users.index',
            ],
        ];

        $managementSections = [
            [
                'title' => 'الوصفات والمحتوى',
                'description' => 'إدارة الوصفات ومراجعة المحتوى التحريري قبل نشره.',
                'icon' => 'fa-book',
                'items' => [
                    [
                        'label' => 'عرض جميع الوصفات',
                        'route' => 'admin.recipes.index',
                    ],
                    [
                        'label' => 'الوصفات المعلقة',
                        'route' => 'admin.recipes.index',
                        'params' => ['status' => Recipe::STATUS_PENDING],
                    ],
                    [
                        'label' => 'إدارة أدوات الشيف',
                        'route' => 'admin.tools.index',
                    ],
                ],
            ],
            [
                'title' => 'ورشات العمل والحجوزات',
                'description' => 'متابعة الورشات والطلبات والحجوزات اليومية.',
                'icon' => 'fa-users',
                'items' => [
                    [
                        'label' => 'كل الورشات',
                        'route' => 'admin.workshops.index',
                    ],
                    [
                        'label' => 'إنشاء ورشة جديدة',
                        'route' => 'admin.workshops.create',
                    ],
                    [
                        'label' => 'الحجوزات',
                        'route' => 'admin.bookings.index',
                    ],
                    [
                        'label' => 'إضافة حجز يدوي',
                        'route' => 'admin.bookings.manual',
                    ],
                ],
            ],
            [
                'title' => 'إدارة المنصة',
                'description' => 'ضبط إعدادات المنصة السريعة والصفحات العامة.',
                'icon' => 'fa-sliders',
                'items' => [
                    [
                        'label' => 'لوحة التحكم',
                        'route' => 'admin.dashboard',
                    ],
                    [
                        'label' => 'إعدادات الرؤية',
                        'route' => 'admin.visibility.index',
                    ],
                    [
                        'label' => 'صفحة روابط Wasfah',
                        'url' => 'https://peahskill.com/wasfah-links',
                    ],
                    [
                        'label' => 'إدارة سلايدر الهيرو',
                        'route' => 'admin.hero-slides.index',
                    ],
                ],
            ],
            [
                'title' => 'المستخدمون',
                'description' => 'متابعة المشتركين، الشيفات، وشركاء الإحالات.',
                'icon' => 'fa-users',
                'items' => [
                    [
                        'label' => 'جميع المستخدمين',
                        'route' => 'admin.users.index',
                    ],
                    [
                        'label' => 'طلبات الشيفات',
                        'route' => 'admin.chefs.requests',
                    ],
                    [
                        'label' => 'شركاء الإحالات',
                        'route' => 'admin.referrals.index',
                    ],
                ],
            ],
            [
                'title' => 'برنامج الإحالات',
                'description' => 'إدارة الشركاء ومتابعة العمولات الجاهزة للدفع.',
                'icon' => 'fa-link',
                'items' => [
                    [
                        'label' => 'إحصائيات الشركاء',
                        'route' => 'admin.referrals.index',
                    ],
                    [
                        'label' => 'تفعيل شريك جديد',
                        'route' => 'admin.referrals.index',
                        'params' => ['open' => 'activate'],
                    ],
                ],
            ],
        ];

        $recipeStatusLabels = [
            Recipe::STATUS_APPROVED => 'منشورة',
            Recipe::STATUS_PENDING => 'بانتظار المراجعة',
            Recipe::STATUS_DRAFT => 'مسودة',
            Recipe::STATUS_REJECTED => 'مرفوضة',
        ];

        return view('admin.admin-area', compact(
            'metrics',
            'attentionItems',
            'recentRecipes',
            'upcomingWorkshops',
            'quickActions',
            'managementSections',
            'recipeStatusLabels'
        ));
    }
}

