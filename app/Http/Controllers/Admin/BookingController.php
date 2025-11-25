<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkshopBooking;
use App\Models\Workshop;
use App\Models\Notification;
use App\Models\BookingRevenueShare;
use App\Support\Currency;
use App\Support\NotificationCopy;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $query = $this->filteredBookingsQuery($request);
        $query = $this->applySorting($query, $request);

        $bookings = $query->paginate(20);

        $stats = cache()->remember('booking_stats', 300, function () {
            $paidBookingQuery = WorkshopBooking::where('payment_status', 'paid');

            $financialCounts = [
                WorkshopBooking::FINANCIAL_STATUS_PENDING => WorkshopBooking::where('financial_status', WorkshopBooking::FINANCIAL_STATUS_PENDING)->count(),
                WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED => WorkshopBooking::where('financial_status', WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED)->count(),
                WorkshopBooking::FINANCIAL_STATUS_VOID => WorkshopBooking::where('financial_status', WorkshopBooking::FINANCIAL_STATUS_VOID)->count(),
            ];

            $shareTotals = BookingRevenueShare::selectRaw('recipient_type, SUM(amount) as total')
                ->where('status', BookingRevenueShare::STATUS_DISTRIBUTED)
                ->groupBy('recipient_type')
                ->pluck('total', 'recipient_type');

            return [
                'total' => WorkshopBooking::count(),
                'pending' => WorkshopBooking::where('status', 'pending')->count(),
                'confirmed' => WorkshopBooking::where('status', 'confirmed')->count(),
                'cancelled' => WorkshopBooking::where('status', 'cancelled')->count(),
                'paid' => (clone $paidBookingQuery)->count(),
                'paid_amount' => (float) (clone $paidBookingQuery)->sum('payment_amount_usd'),
                'unpaid' => WorkshopBooking::where('payment_status', 'pending')->count(),
                'financial' => [
                    'pending' => $financialCounts[WorkshopBooking::FINANCIAL_STATUS_PENDING],
                    'distributed' => $financialCounts[WorkshopBooking::FINANCIAL_STATUS_DISTRIBUTED],
                    'void' => $financialCounts[WorkshopBooking::FINANCIAL_STATUS_VOID],
                ],
                'distributed_amount' => (float) BookingRevenueShare::where('status', BookingRevenueShare::STATUS_DISTRIBUTED)->sum('amount'),
                'distributed_bookings' => BookingRevenueShare::where('status', BookingRevenueShare::STATUS_DISTRIBUTED)
                    ->distinct('workshop_booking_id')
                    ->count('workshop_booking_id'),
                'share_totals' => [
                    'admin' => (float) ($shareTotals[BookingRevenueShare::TYPE_ADMIN] ?? 0),
                    'chef' => (float) ($shareTotals[BookingRevenueShare::TYPE_CHEF] ?? 0),
                    'partner' => (float) ($shareTotals[BookingRevenueShare::TYPE_PARTNER] ?? 0),
                ],
                'paid_currencies' => (clone $paidBookingQuery)
                    ->selectRaw('UPPER(payment_currency) as payment_currency, COUNT(*) as total_bookings, SUM(payment_amount) as total_amount, SUM(payment_amount_usd) as total_amount_usd')
                    ->groupBy('payment_currency')
                    ->orderByDesc('total_amount_usd')
                    ->get()
                    ->map(function ($row) {
                        return [
                            'currency' => $row->payment_currency,
                            'total_bookings' => (int) $row->total_bookings,
                            'total_amount' => (float) $row->total_amount,
                            'total_amount_usd' => (float) $row->total_amount_usd,
                        ];
                    })
                    ->toArray(),
            ];
        });

        $workshops = Workshop::select('id', 'title')
            ->orderBy('title')
            ->get();

        $paymentMethods = WorkshopBooking::select('payment_method')
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->filter()
            ->values();

        $currencyOptions = Currency::all();

        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $previousWeekStart = $startOfWeek->copy()->subWeek();
        $previousWeekEnd = $endOfWeek->copy()->subWeek();

        $todayBookings = WorkshopBooking::whereDate('created_at', $now->toDateString())->count();

        $thisWeekBookings = WorkshopBooking::whereBetween('created_at', [
            $startOfWeek,
            $endOfWeek,
        ])->count();

        $previousWeekBookings = WorkshopBooking::whereBetween('created_at', [
            $previousWeekStart,
            $previousWeekEnd,
        ])->count();

        $weekDelta = $previousWeekBookings > 0
            ? round((($thisWeekBookings - $previousWeekBookings) / max($previousWeekBookings, 1)) * 100, 1)
            : null;

        $pendingFollowUpCount = WorkshopBooking::where('status', 'pending')
            ->where('created_at', '<=', $now->copy()->subHours(48))
            ->count();

        $unpaidConfirmedCount = WorkshopBooking::where('status', 'confirmed')
            ->where('payment_status', '!=', 'paid')
            ->count();

        $insightMetrics = [
            [
                'label' => 'حجوزات اليوم',
                'icon' => 'fa-sun',
                'value' => $todayBookings,
                'description' => 'طلبات جديدة خلال آخر 24 ساعة',
                'badge' => $now->format('d M'),
                'badge_class' => 'bg-blue-100 text-blue-700',
            ],
            [
                'label' => 'حجوزات هذا الأسبوع',
                'icon' => 'fa-calendar-week',
                'value' => $thisWeekBookings,
                'description' => 'مقارنة بالأسبوع الماضي',
                'badge' => $weekDelta === null
                    ? 'جديد'
                    : ($weekDelta >= 0 ? '+' . $weekDelta . '%' : $weekDelta . '%'),
                'badge_class' => $weekDelta === null
                    ? 'bg-blue-100 text-blue-700'
                    : ($weekDelta >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'),
            ],
            [
                'label' => 'حجوزات تنتظر المتابعة',
                'icon' => 'fa-bell',
                'value' => $pendingFollowUpCount,
                'description' => 'طلبات أقدم من 48 ساعة ما زالت قيد المراجعة',
                'badge' => $pendingFollowUpCount > 0 ? 'بحاجة لإجراء' : 'مكتمل',
                'badge_class' => $pendingFollowUpCount > 0
                    ? 'bg-amber-100 text-amber-700'
                    : 'bg-emerald-100 text-emerald-700',
            ],
            [
                'label' => 'مدفوعات تحتاج تحصيل',
                'icon' => 'fa-credit-card',
                'value' => $unpaidConfirmedCount,
                'description' => 'حجوزات مؤكدة ما زالت غير مدفوعة بالكامل',
                'badge' => $unpaidConfirmedCount > 0 ? 'متابعة مالية' : 'لا يوجد متأخرات',
                'badge_class' => $unpaidConfirmedCount > 0
                    ? 'bg-rose-100 text-rose-700'
                    : 'bg-emerald-100 text-emerald-700',
            ],
        ];

        $recentBookings = WorkshopBooking::with([
                'user:id,name,email',
                'workshop:id,title,start_date',
            ])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $followUpBookings = WorkshopBooking::with([
                'user:id,name,email',
                'workshop:id,title,start_date',
            ])
            ->where('status', 'pending')
            ->where('created_at', '<=', $now->copy()->subHours(48))
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $pendingApprovalBookings = WorkshopBooking::with([
                'user:id,name,email',
                'workshop:id,title,start_date,currency',
            ])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $upcomingWorkshops = Workshop::withCount([
                'bookings as confirmed_bookings_count' => function ($query) {
                    $query->where('status', 'confirmed');
                },
                'bookings as pending_bookings_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->whereNotNull('start_date')
            ->where('start_date', '>=', $now->copy()->subDay())
            ->orderBy('start_date')
            ->limit(3)
            ->get();

        $topWorkshops = Workshop::select('id', 'title', 'start_date', 'max_participants')
            ->withCount([
                'bookings as confirmed_bookings_count' => function ($query) {
                    $query->where('status', 'confirmed');
                },
                'bookings as pending_bookings_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->orderByDesc('confirmed_bookings_count')
            ->limit(5)
            ->get();

        return view('admin.bookings.index', compact(
            'bookings',
            'stats',
            'workshops',
            'paymentMethods',
            'currencyOptions',
            'insightMetrics',
            'recentBookings',
            'followUpBookings',
            'pendingApprovalBookings',
            'upcomingWorkshops',
            'topWorkshops'
        ));
    }

    protected function filteredBookingsQuery(Request $request): Builder
    {
        $query = WorkshopBooking::with([
            'workshop',
            'user',
            'invoice',
            'revenueShares' => function ($relation) {
                $relation->orderBy('recipient_type');
            },
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('financial_status')) {
            $query->where('financial_status', $request->financial_status);
        }

        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }

        if ($request->filled('date_from')) {
            try {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح
            }
        }

        if ($request->filled('date_to')) {
            try {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->where('created_at', '<=', $dateTo);
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح
            }
        }

        if ($request->filled('workshop_type')) {
            $query->whereHas('workshop', function ($q) use ($request) {
                if ($request->workshop_type === 'online') {
                    $q->where('is_online', true);
                } elseif ($request->workshop_type === 'offline') {
                    $q->where('is_online', false);
                }
            });
        }

        if ($request->filled('price_range')) {
            $query->whereHas('workshop', function ($q) use ($request) {
                switch ($request->price_range) {
                    case '0-50':
                        $q->where('price', '<=', 50);
                        break;
                    case '50-100':
                        $q->whereBetween('price', [50, 100]);
                        break;
                    case '100-200':
                        $q->whereBetween('price', [100, 200]);
                        break;
                    case '200-500':
                        $q->whereBetween('price', [200, 500]);
                        break;
                    case '500+':
                        $q->where('price', '>', 500);
                        break;
                }
            });
        }

        if ($request->filled('workshop_date_from')) {
            try {
                $workshopDateFrom = Carbon::parse($request->workshop_date_from)->startOfDay();
                $query->whereHas('workshop', function ($q) use ($workshopDateFrom) {
                    $q->where('start_date', '>=', $workshopDateFrom);
                });
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح
            }
        }

        if ($request->filled('workshop_date_to')) {
            try {
                $workshopDateTo = Carbon::parse($request->workshop_date_to)->endOfDay();
                $query->whereHas('workshop', function ($q) use ($workshopDateTo) {
                    $q->where('start_date', '<=', $workshopDateTo);
                });
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح
            }
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_currency')) {
            $query->where('payment_currency', strtoupper($request->payment_currency));
        }

        if ($request->filled('booking_count')) {
            if ($request->booking_count === 'single') {
                $query->whereHas('user', function ($q) {
                    $q->has('workshopBookings', '=', 1);
                });
            } elseif ($request->booking_count === 'multiple') {
                $query->whereHas('user', function ($q) {
                    $q->has('workshopBookings', '>', 1);
                });
            }
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                })->orWhereHas('workshop', function ($workshopQuery) use ($searchTerm) {
                    $workshopQuery->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('instructor', 'like', "%{$searchTerm}%");
                })->orWhere('payment_method', 'like', "%{$searchTerm}%");
            });
        }

        return $query;
    }

    protected function applySorting(Builder $query, Request $request): Builder
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'payment_amount':
                $query->orderBy('payment_amount', $sortDirection);
                break;
            case 'workshop_start_date':
                $query->join('workshops', 'workshop_bookings.workshop_id', '=', 'workshops.id')
                    ->orderBy('workshops.start_date', $sortDirection)
                    ->select('workshop_bookings.*');
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        return $query;
    }






    public function export(Request $request)
    {
        $query = $this->filteredBookingsQuery($request);
        $query = $this->applySorting($query, $request);

        $bookings = $query->get();

        $statusLabels = [
            'pending' => 'قيد المراجعة',
            'confirmed' => 'مؤكدة',
            'cancelled' => 'ملغية',
        ];

        $paymentStatusLabels = [
            'pending' => 'بانتظار الدفع',
            'paid' => 'مدفوعة',
            'refunded' => 'مستردة',
        ];

        $filename = 'bookings-' . Carbon::now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($bookings, $statusLabels, $paymentStatusLabels) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "ï»¿");

            fputcsv($handle, [
                'معرّف الحجز',
                'المستخدم',
                'البريد الإلكتروني',
                'الورشة',
                'تاريخ الورشة',
                'الحالة',
                'حالة الدفع',
                'المبلغ',
                'طريقة الدفع',
                'تاريخ الإنشاء',
                'ملاحظات الإدارة',
            ]);

            foreach ($bookings as $booking) {
                $workshop = $booking->workshop;
                $workshopDate = $workshop && $workshop->start_date
                    ? $workshop->start_date->format('Y-m-d H:i')
                    : '-';

                $cleanNotes = $booking->admin_notes
                    ? preg_replace('/\s+/', ' ', strip_tags($booking->admin_notes))
                    : '';

                fputcsv($handle, [
                    $booking->id,
                    optional($booking->user)->name ?? '-',
                    optional($booking->user)->email ?? '-',
                    $workshop->title ?? '-',
                    $workshopDate,
                    $statusLabels[$booking->status] ?? $booking->status,
                    $paymentStatusLabels[$booking->payment_status] ?? $booking->payment_status,
                    number_format((float) $booking->payment_amount, 2),
                    $booking->payment_method ?? '-',
                    $booking->created_at?->format('Y-m-d H:i'),
                    $cleanNotes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(WorkshopBooking $booking)
    {
        $booking->load([
            'workshop.chef',
            'user',
            'revenueShares.recipient',
        ]);

        $revenueShares = $booking->revenueShares
            ->sortBy('recipient_type')
            ->values();

        return view('admin.bookings.show', [
            'booking' => $booking,
            'revenueShares' => $revenueShares,
        ]);
    }


    public function updateAdminNote(WorkshopBooking $booking, Request $request)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update([
            'admin_notes' => $validated['admin_note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ ملاحظة الإدارة بنجاح',
            'admin_notes' => $booking->admin_notes,
        ]);
    }

    public function confirm(WorkshopBooking $booking)
    {
        if ($booking->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'الحجز مؤكد بالفعل'
            ], 400);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تأكيد حجز ملغي'
            ], 400);
        }

        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now()
        ]);

        $booking->loadMissing('workshop');

        $profileUrl = route('profile');
        $bookingShowUrl = route('bookings.show', ['booking' => $booking->id]);
        $workshopSlug = $booking->workshop?->slug;

        [$notificationTitle, $notificationMessage] = NotificationCopy::bookingConfirmed($booking, $booking->workshop);

        // إنشاء إشعار للمستخدم عند تأكيد الحجز
        Notification::createNotification(
            $booking->user_id,
            'workshop_confirmed',
            $notificationTitle,
            $notificationMessage,
            [
                'workshop_id' => $booking->workshop_id, 
                'workshop_slug' => $workshopSlug,
                'booking_id' => $booking->id,
                'profile_url' => $profileUrl,
                'action_url' => $bookingShowUrl,
            ]
        );

        // مسح cache الإحصائيات
        cache()->forget('booking_stats');

        return response()->json([
            'success' => true,
            'message' => 'تم تأكيد الحجز بنجاح'
        ]);
    }

    public function cancel(WorkshopBooking $booking, Request $request)
    {
        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'الحجز ملغي بالفعل'
            ], 400);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason ?? 'تم الإلغاء من قبل الإدارة'
        ]);

        $booking->loadMissing('workshop');

        // إنشاء إشعار للمستخدم عند إلغاء الحجز
        $profileUrl = route('profile');
        $bookingShowUrl = route('bookings.show', ['booking' => $booking->id]);
        $workshopSlug = $booking->workshop?->slug;

        [$notificationTitle, $notificationMessage] = NotificationCopy::bookingCancelled(
            $booking,
            $request->cancellation_reason ?? null,
            $booking->workshop
        );

        Notification::createNotification(
            $booking->user_id,
            'workshop_cancelled',
            $notificationTitle,
            $notificationMessage,
            [
                'workshop_id' => $booking->workshop_id, 
                'workshop_slug' => $workshopSlug,
                'booking_id' => $booking->id,
                'profile_url' => $profileUrl,
                'cancellation_reason' => $request->cancellation_reason ?? ('Cancelled by the ' . config('app.name', 'Peahskill') . ' team'),
                'action_url' => $bookingShowUrl,
            ]
        );

        // سيتم تحديث عدد الحجوزات تلقائياً عبر event listeners

        // مسح cache الإحصائيات
        cache()->forget('booking_stats');

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الحجز بنجاح'
        ]);
    }

    public function updatePayment(WorkshopBooking $booking, Request $request)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,refunded',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $booking->update([
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
        ]);

        // مسح cache الإحصائيات
        cache()->forget('booking_stats');

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الدفع بنجاح'
        ]);
    }
}
