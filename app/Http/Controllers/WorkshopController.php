<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\WorkshopView;
use App\Support\Concerns\HandlesFullTextSearchFallback;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    use HandlesFullTextSearchFallback;

    /**
     * عرض صفحة الورشات
     */
    public function index(Request $request)
    {
        $query = Workshop::active();

        // فلترة حسب الفئة
        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        // فلترة حسب المستوى
        if ($request->has('level') && $request->level !== 'all') {
            $query->byLevel($request->level);
        }

        // فلترة حسب النوع (أونلاين/أوفلاين)
        if ($request->has('type')) {
            if ($request->type === 'online') {
                $query->online();
            } elseif ($request->type === 'offline') {
                $query->offline();
            }
        }

        // فلترة حسب السعر
        if ($request->has('price_range') && $request->price_range) {
            switch ($request->price_range) {
                case '0-100':
                    $query->where('price', '<=', 100);
                    break;
                case '100-200':
                    $query->whereBetween('price', [100, 200]);
                    break;
                case '200-300':
                    $query->whereBetween('price', [200, 300]);
                    break;
                case '300+':
                    $query->where('price', '>', 300);
                    break;
            }
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort', 'start_date');
        $sortDirection = $request->get('direction', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortDirection);
                break;
            case 'rating':
                $query->orderBy('rating', $sortDirection);
                break;
            case 'popularity':
                $query->orderBy('bookings_count', 'desc');
                break;
            case 'start_date':
            default:
                $query->orderBy('start_date', $sortDirection);
                break;
        }

        $workshops = $query->withCount(['bookings' => function ($query) {
            $query->where('status', 'confirmed');
        }])->get();

        // جلب الورشة المميزة (الورشة القادمة) مع التعامل مع حال
        // عدم وجود ورشة قادمة مستقبلية ولكن تم تحديد ورشة مميزة
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

        // إحصائيات للفلترة
        $categories = Workshop::active()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $levels = Workshop::active()
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get();

        $bookedWorkshopIds = [];

        if (auth()->check()) {
            $bookedWorkshopIds = auth()->user()
                ->workshopBookings()
                ->pluck('workshop_id')
                ->unique()
                ->values()
                ->all();
        }

        $whatsappBookingConfig = $this->buildWhatsappBookingConfig();

        return view('workshops', compact(
            'workshops',
            'featuredWorkshop',
            'categories',
            'levels',
            'bookedWorkshopIds',
            'whatsappBookingConfig'
        ));
    }

    /**
     * عرض تفاصيل ورشة محددة
     */
    public function show(string $slug)
    {
        $workshop = Workshop::active()
            ->with(['recipes', 'chef'])
            ->where('slug', $slug)
            ->first();

        if (!$workshop) {
            return $this->renderWorkshopNotFoundResponse($slug);
        }

        if ($currentRoute = request()->route()) {
            // Share the resolved workshop instance with the route so helpers (e.g. breadcrumbs) can access it.
            $currentRoute->setParameter('workshop', $workshop);
        }

        // الحصول على عنوان IP للمستخدم
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        // فحص إذا كان هذا IP قد شاهد الورشة من قبل، وإذا لم يشاهد أضف مشاهدة جديدة
        $view = WorkshopView::firstOrCreate(
            [
                'workshop_id' => $workshop->id,
                'ip_address' => $ipAddress,
            ],
            [
                'user_agent' => $userAgent,
                'viewed_at' => now(),
            ]
        );

        // إذا كانت هذه مشاهدة جديدة (تم إنشاؤها للتو)، زد العداد
        if ($view->wasRecentlyCreated) {
            $workshop->increment('views_count');
        }

        // ورشات مشابهة
        $relatedWorkshops = Workshop::active()
            ->upcoming()
            ->withCount(['bookings' => function ($query) {
                $query->where('status', 'confirmed');
            }])
            ->where('id', '!=', $workshop->id)
            ->where('category', $workshop->category)
            ->limit(4)
            ->get();

        $userBooking = null;

        if (auth()->check()) {
            $userBooking = $workshop->bookings()
                ->where('user_id', auth()->id())
                ->latest()
                ->first();
        }

        $reviewSummary = [
            'average' => $workshop->rating ? round((float) $workshop->rating, 1) : null,
            'count' => (int) ($workshop->reviews_count ?? 0),
        ];

        $approvedStats = $workshop->reviews()
            ->approved()
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
            ->first();

        if ($approvedStats) {
            $reviewSummary['count'] = (int) ($approvedStats->total_reviews ?? $reviewSummary['count']);
            $reviewSummary['average'] = $approvedStats->average_rating !== null
                ? round((float) $approvedStats->average_rating, 1)
                : $reviewSummary['average'];
        }

        $userReview = null;
        $canReview = false;
        $reviewWindowOpen = $workshop->reviewWindowOpen();

        if (auth()->check()) {
            $userId = auth()->id();

            $userReview = $workshop->reviews()
                ->where('user_id', $userId)
                ->first();

            $canReview = $workshop->canBeReviewedBy($userId);
        }

        $whatsappBookingConfig = $this->buildWhatsappBookingConfig();

        return view('workshop-details', compact(
            'workshop',
            'relatedWorkshops',
            'userBooking',
            'whatsappBookingConfig',
            'reviewSummary',
            'userReview',
            'canReview',
            'reviewWindowOpen'
        ));
    }

    /**
     * واجهة صديقة للمستخدم عند عدم العثور على ورشة
     */
    protected function renderWorkshopNotFoundResponse(string $slug)
    {
        $suggestedWorkshops = Workshop::active()
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get();

        $popularCategories = Workshop::active()
            ->selectRaw('category, COUNT(*) as total')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return response()->view('workshops.not-found', [
            'missingSlug' => $slug,
            'suggestedWorkshops' => $suggestedWorkshops,
            'popularCategories' => $popularCategories,
        ], 404);
    }

    /**
     * البحث في الورشات
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return redirect()->route('workshops');
        }

        $searchQueryCallback = function (EloquentBuilder $builder) {
            $builder->active()
                ->withCount(['bookings' => function ($bookingQuery) {
                    $bookingQuery->where('status', 'confirmed');
                }])
                ->orderBy('start_date', 'asc');
        };

        $fallbackBuilder = function () use ($searchQueryCallback, $query) {
            $builder = Workshop::query();
            $searchQueryCallback($builder);
            $this->applyWorkshopLikeSearch($builder, $query);

            return $builder;
        };

        if (Workshop::hasDescriptionFullTextIndex()) {
            $workshops = $this->runFullTextAwareQuery(
                fn () => Workshop::search($query)
                    ->query($searchQueryCallback)
                    ->get(),
                function ($exception) use ($fallbackBuilder) {
                    Workshop::markDescriptionFullTextUnavailable();

                    return $fallbackBuilder()->get();
                },
                'workshops'
            );
        } else {
            $workshops = $fallbackBuilder()->get();
        }

        $whatsappBookingConfig = $this->buildWhatsappBookingConfig();

        return view('workshops', compact('workshops', 'whatsappBookingConfig'))->with('searchQuery', $query);
    }

    /**
     * API endpoint لجلب الورشات
     */
    public function apiIndex(Request $request)
    {
        $query = Workshop::active()->upcoming();

        // فلترة حسب الفئة
        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        // فلترة حسب المستوى
        if ($request->has('level') && $request->level !== 'all') {
            $query->byLevel($request->level);
        }

        // فلترة حسب النوع
        if ($request->has('type')) {
            if ($request->type === 'online') {
                $query->online();
            } elseif ($request->type === 'offline') {
                $query->offline();
            }
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort', 'start_date');
        $sortDirection = $request->get('direction', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortDirection);
                break;
            case 'rating':
                $query->orderBy('rating', $sortDirection);
                break;
            case 'start_date':
            default:
                $query->orderBy('start_date', $sortDirection);
                break;
        }

        $workshops = $query->withCount(['bookings' => function ($query) {
            $query->where('status', 'confirmed');
        }])->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $workshops,
            'categories' => Workshop::active()->upcoming()
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get(),
            'levels' => Workshop::active()->upcoming()
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->get(),
        ]);
    }

    /**
     * Test method for debugging workshops
     */
    public function testSimple()
    {
        $query = Workshop::active()->upcoming();

        // جلب الورشة المميزة (الورشة القادمة)
        $featuredWorkshop = Workshop::active()
            ->featured()
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->first();

        $workshops = $query->paginate(12);

        return view('test-workshop-simple', compact('workshops', 'featuredWorkshop'));
    }

    private function applyWorkshopLikeSearch(EloquentBuilder $builder, string $query): void
    {
        $escaped = $this->escapeLikeValue($query);

        $builder->where(function (EloquentBuilder $likeQuery) use ($escaped) {
            $likeQuery->where('workshops.title', 'like', "%{$escaped}%")
                ->orWhere('workshops.instructor', 'like', "%{$escaped}%")
                ->orWhere('workshops.category', 'like', "%{$escaped}%")
                ->orWhere('workshops.location', 'like', "%{$escaped}%")
                ->orWhere('workshops.description', 'like', "%{$escaped}%")
                ->orWhere('workshops.slug', 'like', "{$escaped}%");
        });
    }

    private function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    protected function buildWhatsappBookingConfig(): array
    {
        $rawNumber = (string) config('services.whatsapp_booking.number', '');
        $digitsOnly = preg_replace('/\D+/', '', $rawNumber);

        $enabled = (bool) config('services.whatsapp_booking.enabled', false) && !empty($digitsOnly);
        $user = auth()->user();

        return [
            'enabled' => $enabled,
            'number' => $digitsOnly,
            'notes' => (string) config('services.whatsapp_booking.notes', 'WhatsApp booking'),
            'isLoggedIn' => auth()->check(),
            'bookingEndpoint' => route('bookings.store'),
            'loginUrl' => route('login'),
            'registerUrl' => route('register'),
            'user' => [
                'name' => $user?->name ?? __('workshops.labels.unspecified'),
                'phone' => data_get($user, 'phone') ?? data_get($user, 'mobile') ?? __('workshops.labels.unspecified'),
                'email' => $user?->email ?? __('workshops.labels.unspecified'),
            ],
        ];
    }
}
