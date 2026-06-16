<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\PolicyConsentController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\GoogleDriveAuthController;
use App\Http\Controllers\Chef\LinkItemController as ChefLinkItemController;
use App\Http\Controllers\Chef\LinkPageController as ChefLinkPageController;
use App\Http\Controllers\Chef\RecipeController as ChefRecipeController;
use App\Http\Controllers\Chef\GoogleCalendarController as ChefGoogleCalendarController;
use App\Http\Controllers\Chef\WorkshopController as ChefWorkshopController;
use App\Http\Controllers\ChefLinkPublicController;
use App\Http\Controllers\ChefPublicProfileController;
use App\Http\Controllers\ChefPublicWorkshopController;
use App\Http\Controllers\ChefFollowController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReferralDashboardController;
use App\Http\Controllers\MeetingRedirectController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserMeetingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Payments\StripeBookingController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\WorkshopReviewController;
use App\Models\Recipe;
use App\Models\Workshop;
use App\Support\IntendedUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// مسارات المصادقة عبر Google
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'callback'])->name('google.callback');
Route::middleware('auth')->group(function () {
    Route::get('/auth/google/drive', [GoogleDriveAuthController::class, 'redirect'])->name('google.drive.redirect');
    Route::get('/auth/google/drive/callback', [GoogleDriveAuthController::class, 'callback'])->name('google.drive.callback');
});

// المسار الرئيسي لعرض الصفحة الرئيسية
Route::get('/', [HomeController::class, 'index'])->name('home');

$legacyLocaleRedirect = static function (Request $request, string $locale) {
    $supportedLocales = ['ar', 'en'];

    if (! in_array($locale, $supportedLocales, true)) {
        abort(404);
    }

    $request->session()->put('app_locale', $locale);
    app()->setLocale($locale);

    return redirect()->route('home');
};

Route::get('/ar', function (Request $request) use ($legacyLocaleRedirect) {
    return $legacyLocaleRedirect($request, 'ar');
})->name('home.ar');

Route::get('/en', function (Request $request) use ($legacyLocaleRedirect) {
    return $legacyLocaleRedirect($request, 'en');
})->name('home.en');

Route::get('/redirect-to-meet', [MeetingRedirectController::class, 'redirect'])->name('meet.redirect');

// صفحات السياسات القانونية
Route::view('/terms', 'pages.legal.terms')->name('legal.terms');
Route::view('/privacy', 'pages.legal.privacy')->name('legal.privacy');

// تبديل اللغة
Route::post('/locale', [LanguageController::class, 'switch'])->name('locale.switch');

// صفحة روابط Peahskill للمنصات الاجتماعية
Route::permanentRedirect('/wasfah-links', '/peahskill-links');

Route::get('/peahskill-links', function () {
    $locale = app()->getLocale();
    $showAdminTools = auth()->user()?->isAdmin() ?? false;

    $fallbackSelections = collect([
        [
            'title' => __('links.fallback.recipe_box.title'),
            'image' => asset('image/works.webp'),
            'alt' => __('links.fallback.recipe_box.alt'),
            'url' => route('recipes'),
        ],
        [
            'title' => __('links.fallback.workshops.title'),
            'image' => asset('image/wterm.webp'),
            'alt' => __('links.fallback.workshops.alt'),
            'url' => url('/workshops'),
        ],
        $showAdminTools ? [
            'title' => __('links.fallback.tools.title'),
            'image' => asset('image/term.webp'),
            'alt' => __('links.fallback.tools.alt'),
            'url' => url('/tools'),
        ] : null,
    ])->filter()->values();

    $monthlySelections = Recipe::approved()
        ->public()
        ->inRandomOrder()
        ->take(3)
        ->get()
        ->map(function (Recipe $recipe) {
            return [
                'title' => $recipe->title,
                'image' => $recipe->image_url ?? asset('image/brownies.webp'),
                'alt' => $recipe->title,
                'url' => route('recipe.show', ['recipe' => $recipe->slug]),
            ];
        });

    if ($monthlySelections->isEmpty()) {
        $monthlySelections = $fallbackSelections->values();
    } elseif ($monthlySelections->count() < 3) {
        $monthlySelections = $monthlySelections->concat(
            $fallbackSelections->take(3 - $monthlySelections->count())
        )->values();
    } else {
        $monthlySelections = $monthlySelections->values();
    }

    $nextWorkshop = Workshop::query()
        ->active()
        ->upcoming()
        ->orderBy('start_date')
        ->first();

    $upcomingWorkshop = null;

    if ($nextWorkshop) {
        $upcomingWorkshop = [
            'title' => $nextWorkshop->title,
            'slug' => $nextWorkshop->slug,
            'instructor' => $nextWorkshop->instructor ?? __('links.upcoming.default_instructor'),
            'start_date' => $nextWorkshop->start_date?->locale($locale)->translatedFormat('d F Y • h:i a'),
            'mode' => $nextWorkshop->is_online
                ? __('links.upcoming.mode.online')
                : ($nextWorkshop->location ?? __('links.upcoming.mode.offline')),
            'image' => $nextWorkshop->image
                ? \Storage::disk('public')->url($nextWorkshop->image)
                : asset('image/wterm.webp'),
        ];
    }

    return view('links', [
        'monthlySelections' => $monthlySelections,
        'upcomingWorkshop' => $upcomingWorkshop,
    ]);
})->name('links');

Route::get('/peahskill-links/{chefLinkPage:slug}', [ChefLinkPublicController::class, 'show'])
    ->name('links.chef');
Route::get('/wasfah-links/{chefLinkPage:slug}', function ($chefLinkPage) {
    return redirect()->route('links.chef', $chefLinkPage, 301);
});

// صفحة عامة لعرض بروفايلات الشيف مع وصفاتهم
Route::get('/chefs/{chef}', [ChefPublicProfileController::class, 'show'])
    ->whereNumber('chef')
    ->name('chefs.show');

Route::get('/expert/{username}/workshops', [ChefPublicWorkshopController::class, 'show'])
    ->name('chef.public.workshops');

Route::get('/chef/{username}/workshops', function ($username) {
    return redirect()->to("/expert/{$username}/workshops", 301);
});

Route::middleware('auth')->group(function () {
    Route::post('/chefs/{chef}/follow', [ChefFollowController::class, 'store'])
        ->whereNumber('chef')
        ->name('chefs.follow');

    Route::delete('/chefs/{chef}/follow', [ChefFollowController::class, 'destroy'])
        ->whereNumber('chef')
        ->name('chefs.unfollow');
});

// مسار صفحة المصادقة الموحدة (تسجيل الدخول + إنشاء حساب)
Route::get('/adminlogin', function (Request $request) {
    // إذا كان المستخدم مسجل دخول، أعد توجيهه للصفحة الرئيسية
    if (Auth::check()) {
        return redirect('/')->with('info', 'أنت مسجل دخول بالفعل');
    }
    IntendedUrl::rememberFromRequest($request);
    $request->session()->regenerateToken();

    $response = response()
        ->view('auth')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

    $response->withCookie(
        cookie(
            'XSRF-TOKEN',
            csrf_token(),
            config('session.lifetime'),
            '/',
            config('session.domain'),
            (bool) config('session.secure', false),
            false,
            false,
            config('session.same_site', 'lax')
        )
    );

    return $response;
})->name('login');
Route::post('/adminlogin', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.password');

Route::get('/login', function () {
    return redirect('/');
});

Route::post('/login', function () {
    abort(404);
});

// مسارات المحفوظات
Route::prefix('saved')->middleware(['web'])->group(function () {
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\SavedController::class, 'index'])->name('saved.index');
        Route::post('/add', [App\Http\Controllers\SavedController::class, 'add'])->name('saved.add');
        Route::post('/remove', [App\Http\Controllers\SavedController::class, 'remove'])->name('saved.remove');
        Route::get('/status', [App\Http\Controllers\SavedController::class, 'status'])->name('saved.status');
    });
});


// مسار صفحة إنشاء الحساب (يوجه لنفس الصفحة)
Route::get('/register', function (Request $request) {
    // إذا كان المستخدم مسجل دخول، أعد توجيهه للصفحة الرئيسية
    if (Auth::check()) {
        return redirect('/')->with('info', 'أنت مسجل دخول بالفعل');
    }
    IntendedUrl::rememberFromRequest($request);
    $request->session()->regenerateToken();

    $response = response()
        ->view('auth')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

    $response->withCookie(
        cookie(
            'XSRF-TOKEN',
            csrf_token(),
            config('session.lifetime'),
            '/',
            config('session.domain'),
            (bool) config('session.secure', false),
            false,
            false,
            config('session.same_site', 'lax')
        )
    );

    return $response;
})->name('register');
Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('guest')
    ->name('register.password');
Route::get('/register/verify', [RegisterController::class, 'showVerificationForm'])
    ->middleware('guest')
    ->name('register.verify.show');
Route::post('/register/verify', [RegisterController::class, 'verifyCode'])
    ->middleware('guest')
    ->name('register.verify');
Route::post('/register/verify/resend', [RegisterController::class, 'resendCode'])
    ->middleware('guest')
    ->name('register.verify.resend');

// إكمال بيانات الشيف بعد تسجيل الدخول عبر Google
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::get('/welcome/policy-consent', [PolicyConsentController::class, 'show'])->name('policy-consent.show');
    Route::post('/welcome/policy-consent', [PolicyConsentController::class, 'store'])->name('policy-consent.store');
});

// منطقة الشيف - إدارة الوصفات الخاصة
Route::middleware(['auth', 'chef'])->prefix('expert')->name('chef.')->group(function () {
    Route::get('/', [ChefRecipeController::class, 'index'])->name('dashboard');
    Route::get('recipes', [ChefRecipeController::class, 'index'])->name('recipes.index');
    Route::get('google/calendar/connect', [ChefGoogleCalendarController::class, 'redirect'])->name('google.calendar.connect');
    Route::get('google/calendar/callback', [ChefGoogleCalendarController::class, 'callback'])->name('google.calendar.callback');
    Route::get('links', [ChefLinkPageController::class, 'edit'])->name('links.edit');
    Route::put('links', [ChefLinkPageController::class, 'update'])->name('links.update');
    Route::post('links/items', [ChefLinkItemController::class, 'store'])->name('links.items.store');
    Route::put('links/items/{item}', [ChefLinkItemController::class, 'update'])->name('links.items.update');
    Route::delete('links/items/{item}', [ChefLinkItemController::class, 'destroy'])->name('links.items.destroy');
    Route::post('recipes/{recipe}/submit', [ChefRecipeController::class, 'submit'])->name('recipes.submit');
    Route::resource('recipes', ChefRecipeController::class)->except(['show', 'index']);
    Route::get('workshops/{workshop}/join', [ChefWorkshopController::class, 'join'])->name('workshops.join');
    Route::post('workshops/{workshop}/start', [ChefWorkshopController::class, 'startMeeting'])->name('workshops.start');
    Route::post('workshops/{workshop}/recording', [ChefWorkshopController::class, 'syncRecording'])->name('workshops.recording');
    Route::post('workshops/{workshop}/presence', [ChefWorkshopController::class, 'updatePresence'])->name('workshops.presence');
    Route::post('workshops/{workshop}/reset-device', [ChefWorkshopController::class, 'resetHostDeviceLock'])->name('workshops.reset-device');
    Route::post('workshops/generate-meeting-link', [ChefWorkshopController::class, 'generateMeetingLink'])->name('workshops.generate-link');
    Route::get('workshops/earnings', [ChefWorkshopController::class, 'earnings'])->name('workshops.earnings');
    Route::resource('workshops', ChefWorkshopController::class)->except(['show']);
});

Route::get('/chef/{any?}', function ($any = null) {
    $suffix = $any ? '/' . ltrim($any, '/') : '';
    return redirect('/expert' . $suffix, 301);
})->where('any', '.*');

Route::get('/recipe/{recipe:slug}', [App\Http\Controllers\RecipeController::class, 'show'])->name('recipe.show');

// مسار ورشات العمل
Route::get('/workshops', [App\Http\Controllers\WorkshopController::class, 'index'])->name('workshops');
Route::get('/workshops/{workshop}', [App\Http\Controllers\WorkshopController::class, 'show'])->name('workshop.show');
Route::get('/workshops/search', [App\Http\Controllers\WorkshopController::class, 'search'])->name('workshops.search');

// مسار البحث
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');

// مسار الـ feed (الوصفات سابقاً)
Route::get('/feed', [App\Http\Controllers\RecipeController::class, 'index'])->name('recipes');
Route::get('/recipes', function (Request $request) {
    return redirect()->route('recipes', $request->query());
});
Route::post('/feed/quick-post', [App\Http\Controllers\RecipeController::class, 'storeQuickPost'])
    ->name('feed.quick-post')
    ->middleware(['auth', 'admin']);

// مسارات الأدوات والسلة (لأدمن فقط)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/tools', [App\Http\Controllers\ToolsController::class, 'index'])->name('tools');
    Route::get('/tools/{tool}', [App\Http\Controllers\ToolsController::class, 'show'])->name('tools.show');

    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/', [CartController::class, 'add'])->name('add');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/quantities', [CartController::class, 'quantities'])->name('quantities');
        Route::delete('/remove-by-tool', [CartController::class, 'removeByToolId'])->name('remove-by-tool');
        Route::patch('/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cart}', [CartController::class, 'remove'])->name('remove');
    });
});

// مسارات الحجوزات - محمية بـ middleware المصادقة (باستثناء الانضمام الذي يسمح للضيوف)
Route::middleware('auth')->group(function () {
    Route::get('/meetings', [UserMeetingController::class, 'index'])->name('meetings.index');
    Route::post('/workshops/{workshop:slug}/reviews', [WorkshopReviewController::class, 'store'])->name('workshops.reviews.store');
    Route::post('/record-management', [App\Http\Controllers\WorkshopBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [App\Http\Controllers\WorkshopBookingController::class, 'index'])
        ->name('bookings.index');
    Route::get('/record-management', [App\Http\Controllers\WorkshopBookingController::class, 'recordings'])
        ->middleware('chef')
        ->name('bookings.recordings');
    Route::get('/record-management/{booking}', [App\Http\Controllers\WorkshopBookingController::class, 'show'])->name('bookings.show');
    Route::post('/record-management/{booking}/cancel', [App\Http\Controllers\WorkshopBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/record-management/recordings/{workshop}/visibility', [App\Http\Controllers\WorkshopBookingController::class, 'toggleRecordingVisibility'])->name('bookings.recordings.visibility');

    Route::post('/payments/stripe/intent', [StripeBookingController::class, 'createIntent'])->name('payments.stripe.intent');
    Route::post('/payments/stripe/confirm', [StripeBookingController::class, 'confirm'])->name('payments.stripe.confirm');

    Route::prefix('recordings')->name('recordings.')->group(function () {
        Route::get('/drive', [RecordingController::class, 'listDrive'])->name('drive');
        Route::post('/from-drive', [RecordingController::class, 'storeFromDrive'])->name('store-from-drive');
    });
});

Route::get('/record-management/{booking:public_code}/status', [App\Http\Controllers\WorkshopBookingController::class, 'status'])
    ->name('bookings.status');
Route::get('/record-management/{booking:public_code}/join', [App\Http\Controllers\WorkshopBookingController::class, 'join'])
    ->name('bookings.join');
Route::get('/record-management/{booking:public_code}/launch', [App\Http\Controllers\WorkshopBookingController::class, 'launch'])
    ->name('bookings.launch');

// مسارات الإشعارات - محمية بـ middleware المصادقة
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/api', [App\Http\Controllers\NotificationController::class, 'api'])->name('api');
    Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    Route::post('/clear-read', [App\Http\Controllers\NotificationController::class, 'clearRead'])->name('clear-read');
});

// مسارات إدارة الأدوات (للإدمن فقط)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tools', App\Http\Controllers\Admin\AdminToolsController::class);
    Route::post('tools/{tool}/toggle', [App\Http\Controllers\Admin\AdminToolsController::class, 'toggle'])->name('tools.toggle');
    Route::post('tools/extract-amazon-data', [App\Http\Controllers\Admin\AdminToolsController::class, 'extractAmazonData'])->name('tools.extract-amazon-data');
});

// مسارات الملف الشخصي - محمية بـ middleware المصادقة
Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/statistics', [App\Http\Controllers\ProfileController::class, 'statistics'])->name('profile.statistics');
    Route::get('/profile/activity', [App\Http\Controllers\ProfileController::class, 'activity'])->name('profile.activity');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'referral.partner'])->prefix('referrals')->name('referrals.')->group(function () {
    Route::get('/', [ReferralDashboardController::class, 'index'])->name('dashboard');
});

// مسارات الإدارة - محمية بـ middleware المصادقة والادمن
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // لوحة التحكم
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // صفحة منطقة الإدمن الرئيسية
    Route::get('/admin-area', [App\Http\Controllers\Admin\AdminAreaController::class, 'index'])->name('admin-area');
    
    // إدارة الورشات
    Route::get('workshops/{workshop}/meeting', [App\Http\Controllers\Admin\WorkshopController::class, 'meeting'])->name('workshops.meeting');
    Route::post('workshops/generate-meeting-link', [App\Http\Controllers\Admin\WorkshopController::class, 'generateMeetingLink'])->name('workshops.generate-link');
    Route::resource('workshops', App\Http\Controllers\Admin\WorkshopController::class);
    Route::post('workshops/{workshop}/toggle-status', [App\Http\Controllers\Admin\WorkshopController::class, 'toggleStatus'])->name('workshops.toggle-status');
    Route::post('workshops/{workshop}/toggle-featured', [App\Http\Controllers\Admin\WorkshopController::class, 'toggleFeatured'])->name('workshops.toggle-featured');
    
    // إدارة الحجوزات
    Route::get('bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/export', [App\Http\Controllers\Admin\BookingController::class, 'export'])->name('bookings.export');
    Route::get('bookings/{booking}', [App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{booking}/confirm', [App\Http\Controllers\Admin\BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('bookings/{booking}/cancel', [App\Http\Controllers\Admin\BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/update-payment', [App\Http\Controllers\Admin\BookingController::class, 'updatePayment'])->name('bookings.update-payment');
    Route::post('bookings/{booking}/admin-note', [App\Http\Controllers\Admin\BookingController::class, 'updateAdminNote'])->name('bookings.admin-note');

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [FinanceController::class, 'showInvoice'])->name('invoices.show');
        Route::post('/invoices/{invoice}/issue', [FinanceController::class, 'issueInvoice'])->name('invoices.issue');
        Route::post('/invoices/{invoice}/mark-paid', [FinanceController::class, 'markInvoicePaid'])->name('invoices.mark-paid');
        Route::post('/invoices/{invoice}/void', [FinanceController::class, 'voidInvoice'])->name('invoices.void');
        Route::post('/bookings/{booking}/invoice/regenerate', [FinanceController::class, 'regenerateBookingInvoice'])->name('bookings.invoice.regenerate');
    });

    // إضافة الحجوزات يدوياً
    Route::get('bookings/manual/add', [App\Http\Controllers\Admin\ManualBookingController::class, 'index'])->name('bookings.manual');
    Route::post('bookings/manual/add', [App\Http\Controllers\Admin\ManualBookingController::class, 'store'])->name('bookings.manual.store');
    Route::post('bookings/quick-add', [App\Http\Controllers\Admin\ManualBookingController::class, 'quickAdd'])->name('bookings.quick-add');

    // إدارة طلبات الشيفات الجدد
    Route::get('chefs/requests', [App\Http\Controllers\Admin\ChefApprovalController::class, 'index'])->name('chefs.requests');
    Route::post('chefs/{user}/approve', [App\Http\Controllers\Admin\ChefApprovalController::class, 'approve'])->name('chefs.approve');
    Route::post('chefs/{user}/reject', [App\Http\Controllers\Admin\ChefApprovalController::class, 'reject'])->name('chefs.reject');

    // إدارة إعدادات الرؤية
    Route::get('visibility', [App\Http\Controllers\Admin\VisibilityController::class, 'index'])->name('visibility.index');
    Route::put('visibility/{section}', [App\Http\Controllers\Admin\VisibilityController::class, 'update'])->name('visibility.update');
    Route::post('visibility/{section}/toggle', [App\Http\Controllers\Admin\VisibilityController::class, 'toggle'])->name('visibility.toggle');
    Route::get('visibility/config', [App\Http\Controllers\Admin\VisibilityController::class, 'getConfig'])->name('visibility.config');
    Route::post('visibility/clear-cache', [App\Http\Controllers\Admin\VisibilityController::class, 'clearCache'])->name('visibility.clear-cache');
    Route::post('visibility/initialize-defaults', [App\Http\Controllers\Admin\VisibilityController::class, 'initializeDefaults'])->name('visibility.initialize-defaults');
    Route::post('visibility/bulk-update', [App\Http\Controllers\Admin\VisibilityController::class, 'bulkUpdate'])->name('visibility.bulk-update');

    // إدارة شرائح الهيرو
    Route::resource('hero-slides', HeroSlideController::class)->except(['show']);
    Route::post('hero-slides/{heroSlide}/toggle', [HeroSlideController::class, 'toggleStatus'])->name('hero-slides.toggle');
    Route::post('hero-slides/reorder', [HeroSlideController::class, 'reorder'])->name('hero-slides.reorder');
    Route::post('hero-slides/initialize-defaults', [HeroSlideController::class, 'initializeDefaults'])->name('hero-slides.initialize-defaults');

    // إدارة رسائل التواصل
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');

    // إدارة المستخدمين
    Route::get('users', [AdminUserManagementController::class, 'index'])->name('users.index');

    // إدارة برنامج الإحالات
    Route::get('referrals', [AdminReferralController::class, 'index'])->name('referrals.index');
    Route::post('referrals/activate', [AdminReferralController::class, 'activate'])->name('referrals.activate');
    Route::get('referrals/{user}', [AdminReferralController::class, 'show'])->name('referrals.show');
    Route::patch('referrals/{user}', [AdminReferralController::class, 'update'])->name('referrals.update');
    Route::patch('referrals/{user}/commissions/{commission}/status', [AdminReferralController::class, 'updateCommissionStatus'])->name('referrals.commissions.update-status');
});

// مسارات الصفحات العامة
Route::get('/about', [App\Http\Controllers\PageController::class, 'about'])
    ->middleware('admin')
    ->name('about');
Route::get('/baking-tips', [App\Http\Controllers\PageController::class, 'bakingTips'])->name('baking-tips');
Route::get('/advertising', [App\Http\Controllers\PageController::class, 'advertising'])->name('advertising');
Route::get('/partnership', [App\Http\Controllers\PageController::class, 'partnership'])->name('partnership');
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');

// Admin Routes - محمية بـ middleware الإدارة
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/recipes', [App\Http\Controllers\Admin\RecipeController::class, 'index'])->name('recipes.index');
    Route::get('/recipes/create', [App\Http\Controllers\Admin\RecipeController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [App\Http\Controllers\Admin\RecipeController::class, 'store'])->name('recipes.store');
    Route::post('/recipes/approve-all', [App\Http\Controllers\Admin\RecipeController::class, 'approveAll'])->name('recipes.approve-all');
    Route::post('/recipes/{recipe}/approve', [App\Http\Controllers\Admin\RecipeController::class, 'approve'])->name('recipes.approve');
    Route::post('/recipes/{recipe}/reject', [App\Http\Controllers\Admin\RecipeController::class, 'reject'])->name('recipes.reject');
    Route::get('/recipes/{recipe}', [App\Http\Controllers\Admin\RecipeController::class, 'show'])->name('recipes.show');
    Route::get('/recipes/{recipe}/edit', [App\Http\Controllers\Admin\RecipeController::class, 'edit'])->name('recipes.edit');
    Route::put('/recipes/{recipe}', [App\Http\Controllers\Admin\RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [App\Http\Controllers\Admin\RecipeController::class, 'destroy'])->name('recipes.destroy');
});

// مسار تسجيل الخروج
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'تم تسجيل الخروج بنجاح');
})->name('logout');

// مسارات التطوير والاختبار
Route::get('/debug', [App\Http\Controllers\DebugController::class, 'index'])->name('debug.index');

Route::get('/test-stats', [App\Http\Controllers\TestStatsController::class, 'index'])->name('test.stats');
Route::get('/debug-recipe', [App\Http\Controllers\DebugController::class, 'debugRecipe'])->name('debug.recipe');
Route::get('/test-recipe-js', [App\Http\Controllers\DebugController::class, 'testRecipeJs'])->name('test.recipe.js');
Route::get('/test-api', [App\Http\Controllers\DebugController::class, 'testApi'])->name('test.api');
Route::get('/test-recipe-debug', [App\Http\Controllers\DebugController::class, 'testRecipeDebug'])->name('test.recipe.debug');
Route::get('/test-recipe-page', [App\Http\Controllers\DebugController::class, 'testRecipePage'])->name('test.recipe.page');
Route::get('/check-script-loading', [App\Http\Controllers\DebugController::class, 'checkScriptLoading'])->name('check.script.loading');
Route::get('/check-dom-elements', [App\Http\Controllers\DebugController::class, 'checkDomElements'])->name('check.dom.elements');
Route::get('/check-tools', [App\Http\Controllers\DebugController::class, 'checkTools'])
    ->middleware(['auth', 'admin'])
    ->name('check.tools');
Route::get('/test-save', function() { return view('test-save'); })->name('test.save');
Route::get('/test-flip-cards', function() { return view('test-flip-cards'); })->name('test.flip.cards');
Route::get('/test-workshop-simple', [App\Http\Controllers\WorkshopController::class, 'testSimple'])->name('test.workshop.simple');

// Test Amazon extraction without CSRF
Route::post('/test-amazon-extraction', [App\Http\Controllers\Admin\AdminToolsController::class, 'extractAmazonData'])
    ->middleware(['auth', 'admin'])
    ->name('test.amazon.extraction');

// مسارات الاتصال
Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contact/send', [App\Http\Controllers\ContactController::class, 'sendMessage'])->name('contact.send');

// ملاحظة: تم تخصيص صفحة /bookings لعرض حجوزات المستخدمين


require __DIR__.'/thedolci.php';

