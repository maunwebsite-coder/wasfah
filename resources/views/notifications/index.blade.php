@extends('layouts.app')

@section('title', __('notifications.page.title'))

@section('content')
@php
    $notificationsCollection = $notifications instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($notifications->items())
        : collect($notifications);
    $workshopTypes = ['workshop_booking', 'workshop_confirmed', 'workshop_cancelled'];
    $todayCount = $notificationsCollection->filter(fn($notification) => optional($notification->created_at)->isToday())->count();
    $workshopAlerts = $notificationsCollection->whereIn('type', $workshopTypes)->count();
    $readCount = $notificationsCollection->where('is_read', true)->count();
    $hasNotifications = $notifications->count() > 0;
    $isArabic = app()->isLocale('ar');
    $heroTagline = $isArabic ? 'كل تحديثات وصفة في لوحة واحدة أنيقة' : 'Every Wasfah update in one elegant hub';
    $heroSubline = $isArabic ? 'تابع حجوزاتك، تأكيدات الورش، وأي تذكير جديد في ثوانٍ.' : 'Track bookings, confirmations, and friendly nudges in seconds.';
    $filterLabels = [
        'all' => $isArabic ? 'الكل' : 'All',
        'unread' => $isArabic ? 'غير مقروء' : 'Unread',
        'workshops' => $isArabic ? 'ورش وصفة' : 'Workshops',
    ];
    $statsCopy = [
        [
            'label' => $isArabic ? 'إشعارات اليوم' : "Today's alerts",
            'value' => $todayCount,
            'description' => $isArabic ? 'تم تسليمها خلال 24 ساعة' : 'Delivered in the last 24h',
        ],
        [
            'label' => $isArabic ? 'تنبيهات الورش' : 'Workshop alerts',
            'value' => $workshopAlerts,
            'description' => $isArabic ? 'طلبات وتأكيدات الورش' : 'Bookings & confirmations',
        ],
        [
            'label' => $isArabic ? 'مُراجعة' : 'Reviewed',
            'value' => $readCount,
            'description' => $isArabic ? 'تمت مراجعتها مؤخراً' : 'Checked recently',
        ],
    ];
    $statusLabels = [
        'workshop_booking' => __('navbar.notifications_dropdown.status_labels.workshop_booking'),
        'workshop_confirmed' => __('navbar.notifications_dropdown.status_labels.workshop_confirmed'),
        'workshop_cancelled' => __('navbar.notifications_dropdown.status_labels.workshop_cancelled'),
        'default' => __('navbar.notifications_dropdown.status_labels.default'),
    ];
    $typeMeta = [
        'workshop_booking' => [
            'icon' => 'calendar-plus',
            'wrapper' => 'bg-sky-50 text-sky-600 ring-1 ring-sky-100 shadow-[0_12px_24px_rgba(14,165,233,0.18)]',
            'badge' => $statusLabels['workshop_booking'],
            'badgeClasses' => 'bg-sky-50 text-sky-700',
            'timeline' => 'from-sky-200/70 via-sky-100/20 to-transparent',
        ],
        'workshop_confirmed' => [
            'icon' => 'check-circle',
            'wrapper' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 shadow-[0_12px_24px_rgba(16,185,129,0.18)]',
            'badge' => $statusLabels['workshop_confirmed'],
            'badgeClasses' => 'bg-emerald-50 text-emerald-700',
            'timeline' => 'from-emerald-200/70 via-emerald-100/20 to-transparent',
        ],
        'workshop_cancelled' => [
            'icon' => 'times-circle',
            'wrapper' => 'bg-rose-50 text-rose-600 ring-1 ring-rose-100 shadow-[0_12px_24px_rgba(244,63,94,0.18)]',
            'badge' => $statusLabels['workshop_cancelled'],
            'badgeClasses' => 'bg-rose-50 text-rose-700',
            'timeline' => 'from-rose-200/70 via-rose-100/20 to-transparent',
        ],
        'default' => [
            'icon' => 'info-circle',
            'wrapper' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200 shadow-[0_12px_24px_rgba(15,23,42,0.1)]',
            'badge' => $statusLabels['default'],
            'badgeClasses' => 'bg-slate-100 text-slate-700',
            'timeline' => 'from-orange-200/60 via-orange-100/20 to-transparent',
        ],
    ];
@endphp
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <section class="border-b border-slate-100 bg-white/95">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto py-10 space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="notifications-hero relative overflow-hidden rounded-[32px] border border-slate-100 bg-white p-8 shadow-xl shadow-slate-100 lg:col-span-2">
                        <div class="flex flex-col gap-5">
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.45em] text-slate-400">{{ __('notifications.page.title') }}</p>
                                <h1 class="text-3xl font-black leading-tight text-slate-900">{{ __('notifications.page.description') }}</h1>
                                <p class="text-sm text-slate-500">{{ $heroTagline }}</p>
                                <p class="text-sm text-slate-400">{{ $heroSubline }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-slate-900">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ $isArabic ? 'غير المقروء' : 'Unread' }}</p>
                                    <p class="text-4xl font-black">{{ $unreadCount }}</p>
                                </div>
                                <span class="h-10 w-px bg-slate-200"></span>
                                <div class="flex flex-wrap gap-2">
                                    <button id="mark-all-read" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-orange-200 hover:text-orange-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-100 {{ $hasNotifications ? '' : 'opacity-40 cursor-not-allowed' }}" {{ $hasNotifications ? '' : 'disabled' }}>
                                        <i class="fas fa-check-double text-orange-500"></i>
                                        {{ __('notifications.buttons.mark_all_read') }}
                                    </button>
                                    <button id="clear-read" class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-5 py-2 text-sm font-semibold text-rose-500 shadow-sm transition hover:border-rose-300 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-100 {{ $hasNotifications ? '' : 'opacity-40 cursor-not-allowed' }}" {{ $hasNotifications ? '' : 'disabled' }}>
                                        <i class="fas fa-trash"></i>
                                        {{ __('notifications.buttons.clear_read') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="pointer-events-none absolute -right-10 top-8 h-48 w-48 rounded-full bg-orange-100/60 blur-3xl"></span>
                        <span class="pointer-events-none absolute -left-8 bottom-0 h-32 w-32 rounded-full bg-rose-100/60 blur-3xl"></span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="sm:hidden" data-stats-container>
                            <div class="mb-2 flex items-center justify-between px-1 text-slate-900">
                                <p class="text-sm font-semibold">{{ $isArabic ? 'ملخص اليوم' : 'Today at a glance' }}</p>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="stats-nav-btn" data-stats-prev aria-label="{{ $isArabic ? 'السابق' : 'Previous' }}">
                                        <i class="fas {{ $isArabic ? 'fa-chevron-right' : 'fa-chevron-left' }}"></i>
                                    </button>
                                    <button type="button" class="stats-nav-btn" data-stats-next aria-label="{{ $isArabic ? 'التالي' : 'Next' }}">
                                        <i class="fas {{ $isArabic ? 'fa-chevron-left' : 'fa-chevron-right' }}"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-slider" data-stats-slider>
                                <div class="stats-slider-track" data-stats-track>
                                    @foreach($statsCopy as $stat)
                                        <div class="stats-slide" data-stat-slide>
                                            <div class="rounded-3xl border border-slate-100 bg-white px-5 py-4 text-slate-700 shadow-sm">
                                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $stat['label'] }}</p>
                                                <p class="mt-1 text-3xl font-black text-slate-900">{{ $stat['value'] }}</p>
                                                <p class="text-xs text-slate-400">{{ $stat['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="stats-dots" data-stats-dots></div>
                            </div>
                        </div>

                        <div class="hidden gap-4 sm:grid sm:grid-cols-3 lg:grid-cols-1">
                            @foreach($statsCopy as $stat)
                                <div class="rounded-3xl border border-slate-100 bg-white px-5 py-4 text-slate-700 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $stat['label'] }}</p>
                                    <p class="mt-1 text-3xl font-black text-slate-900">{{ $stat['value'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $stat['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-10">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto space-y-6">
                <div class="flex flex-col gap-4 rounded-[28px] border border-slate-100 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-wrap gap-2" role="tablist">
                        @foreach($filterLabels as $filterKey => $filterLabel)
                            <button type="button" data-filter-button data-filter="{{ $filterKey }}" class="filter-pill inline-flex items-center gap-2 rounded-full border border-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:text-orange-600 {{ $loop->first ? 'is-active' : '' }}">
                                <span class="h-2 w-2 rounded-full bg-orange-300"></span>
                                {{ $filterLabel }}
                            </button>
                        @endforeach
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                        <div class="relative w-full sm:w-64">
                            <i class="fas fa-search pointer-events-none absolute inset-y-0 flex items-center text-slate-400" style="{{ $isArabic ? 'right:1rem' : 'left:1rem' }}"></i>
                            <input id="notification-search" type="search" class="w-full rounded-full border border-slate-100 bg-white/90 px-10 py-2 text-sm text-slate-700 shadow-inner focus:border-orange-300 focus:ring-2 focus:ring-orange-200" placeholder="{{ $isArabic ? 'ابحث في الإشعارات' : 'Search notifications' }}">
                        </div>
                        <span class="text-xs font-medium text-slate-400">{{ $isArabic ? 'اكتب للبحث أو استخدم المرشحات المتاحة' : 'Type to search or use one of the filters.' }}</span>
                    </div>
                </div>

                @if($hasNotifications)
                    <div class="rounded-[32px] border border-slate-100 bg-white p-6 shadow-xl shadow-slate-100">
                        <div id="notification-list-wrapper" class="space-y-4">
                            <div id="notification-timeline" class="notification-timeline space-y-4">
                                @php $currentGroup = null; @endphp
                                @foreach($notifications as $notification)
                                    @php
                                        $meta = $typeMeta[$notification->type] ?? $typeMeta['default'];
                                        $searchText = \Illuminate\Support\Str::lower($notification->title . ' ' . $notification->message);
                                        $datasetType = in_array($notification->type, $workshopTypes, true) ? 'workshops' : 'general';
                                        $groupKey = $datasetType === 'workshops' ? 'workshops' : 'general';
                                        $groupLabel = $groupKey === 'workshops'
                                            ? ($isArabic ? 'تنبيهات ورش Wasfah' : 'Wasfah workshop alerts')
                                            : ($isArabic ? 'تنبيهات عامة' : 'General alerts');
                                    @endphp

                                    @if($currentGroup !== $groupKey)
                                        @php $currentGroup = $groupKey; @endphp
                                        <div class="flex items-center gap-3 pt-2 text-slate-400">
                                            <span class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></span>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">{{ $groupLabel }}</p>
                                            <span class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></span>
                                        </div>
                                    @endif

                                    <article class="notification-item group relative overflow-hidden rounded-[28px] border border-white/60 bg-white/80 p-5 text-slate-700 shadow-[0_18px_35px_rgba(15,23,42,0.07)] ring-1 ring-slate-100/70 backdrop-blur-sm transition hover:-translate-y-1 hover:shadow-[0_32px_60px_rgba(15,23,42,0.12)] {{ $notification->is_read ? 'opacity-80' : '' }}" data-id="{{ $notification->id }}" data-action-url="{{ $notification->action_url }}" data-status="{{ $notification->is_read ? 'read' : 'unread' }}" data-type="{{ $datasetType }}" data-search="{{ $searchText }}">
                                        <span class="notification-timeline-line pointer-events-none" style="{{ $isArabic ? 'right:1.35rem' : 'left:1.35rem' }}"></span>
                                        <span class="notification-timeline-dot {{ $notification->is_read ? '' : 'notification-timeline-dot--active' }}" style="{{ $isArabic ? 'right:1.1rem' : 'left:1.1rem' }}"></span>
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                                            <div class="flex flex-1 gap-4">
                                                <div class="notification-icon relative flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-100/70 {{ $meta['wrapper'] }}">
                                                    <i class="fas fa-{{ $meta['icon'] }} text-base"></i>
                                                    @unless($notification->is_read)
                                                        <span class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-orange-500 ring-2 ring-white animate-pulse"></span>
                                                    @endunless
                                                </div>
                                                <div class="min-w-0 space-y-3">
                                                    <div class="flex flex-wrap items-center gap-2 text-slate-900">
                                                        <h3 class="text-base font-semibold leading-snug">{{ $notification->title }}</h3>
                                                        <span class="notification-status inline-flex items-center gap-1 rounded-full border border-white/70 px-2.5 py-0.5 text-[11px] font-semibold shadow-sm {{ $meta['badgeClasses'] }}">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                                                            {{ $meta['badge'] }}
                                                        </span>
                                                        <span class="flex items-center gap-1 text-xs font-medium text-slate-400">
                                                            <i class="fas fa-clock text-[10px]"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                    <p class="notification-message text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($notification->message), 140) }}</p>
                                                    @if($notification->action_url)
                                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600">
                                                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                                            {{ $isArabic ? 'عرض التفاصيل' : 'Open details' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="notification-actions flex flex-wrap gap-2">
                                                @unless($notification->is_read)
                                                    <button type="button" class="mark-read-btn inline-flex items-center gap-1.5 rounded-full border border-orange-100/70 bg-white/80 px-3 py-1.5 text-[11px] font-semibold text-orange-600 shadow-inner shadow-orange-100/40 transition hover:bg-orange-50" data-id="{{ $notification->id }}">
                                                        <i class="fas fa-check text-xs"></i>
                                                        {{ __('notifications.tooltips.mark_as_read') }}
                                                    </button>
                                                @endunless
                                                <button type="button" class="delete-notification-btn inline-flex items-center gap-1.5 rounded-full border border-rose-100/70 bg-white/80 px-3 py-1.5 text-[11px] font-semibold text-rose-600 shadow-inner shadow-rose-100/40 transition hover:bg-rose-50" data-id="{{ $notification->id }}">
                                                    <i class="fas fa-trash text-xs"></i>
                                                    {{ __('notifications.tooltips.delete') }}
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div id="filtered-empty-state" class="hidden rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500">
                                <p class="text-base font-semibold text-slate-900">{{ $isArabic ? 'لا توجد نتائج مطابقة' : 'Nothing matches your filters' }}</p>
                                <p class="text-sm text-slate-500">{{ $isArabic ? 'حاول تعديل المرشحات أو مسح البحث لإظهار الإشعارات.' : 'Try adjusting the filters or clearing the search query.' }}</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @else
                    <div class="rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-12 text-center shadow-inner">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-orange-500">
                            <i class="fas fa-bell-slash text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('notifications.page.empty_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ __('notifications.page.empty_description') }}</p>
                        <a href="{{ route('workshops') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-l from-orange-500 to-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-95 rtl:flex-row-reverse">
                            <i class="fas {{ $isArabic ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
                            <span>{{ $isArabic ? 'استكشف ورش وصفة' : 'Explore Wasfah workshops' }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .notifications-hero {
        position: relative;
        overflow: hidden;
    }

    .notifications-hero::after {
        content: '';
        position: absolute;
        inset-inline-end: -25%;
        top: -35%;
        width: 70%;
        height: 150%;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.25), transparent 65%);
        filter: blur(40px);
    }

    .filter-pill {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.2s ease;
    }

    .filter-pill.is-active {
        color: #fff;
        border-color: transparent;
        background-image: linear-gradient(135deg, #0f4c73, #0fb39a);
        box-shadow: 0 15px 30px rgba(15, 76, 115, 0.25);
    }

    .notification-item {
        position: relative;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .notification-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 32px 50px rgba(15, 23, 42, 0.12);
        border-color: rgba(15, 76, 115, 0.45);
    }

    .notification-message {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notification-timeline-line {
        position: absolute;
        top: 1.5rem;
        bottom: 1.5rem;
        width: 2px;
        background: linear-gradient(180deg, rgba(251, 191, 36, 0.25), rgba(15, 23, 42, 0.05));
        opacity: 0.5;
    }

    .notification-timeline-dot {
        position: absolute;
        top: 1.4rem;
        width: 12px;
        height: 12px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: rgba(148, 163, 184, 0.9);
        box-shadow: 0 6px 15px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        pointer-events: none;
    }

    .notification-timeline-dot--active {
        background: #0f4c73;
        box-shadow: 0 8px 20px rgba(15, 76, 115, 0.35);
        transform: scale(1.05);
    }

    .notification-item:hover .notification-timeline-dot {
        transform: translateY(-1px);
    }

    .notification-actions button {
        backdrop-filter: blur(10px);
    }

    .stats-slider {
        position: relative;
        overflow: hidden;
    }

    .stats-slider-track {
        display: flex;
        gap: 1rem;
        transition: transform 0.35s ease;
        will-change: transform;
        touch-action: pan-y;
    }

    .stats-slide {
        flex: 0 0 100%;
    }

    .stats-nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #0f4c73;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        transition: all 0.2s ease;
    }

    .stats-nav-btn:hover {
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .stats-nav-btn:active {
        transform: translateY(0);
    }

    .stats-dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .stats-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        transition: all 0.2s ease;
    }

    .stats-dot.is-active {
        background: #0f4c73;
        border-color: #0f4c73;
        transform: scale(1.05);
    }

    @media (max-width: 640px) {
        .notifications-hero::after {
            inset-inline-end: -60%;
            top: -10%;
            width: 120%;
        }
    }
</style>
@endpush

@push('scripts')
@php
    $notificationI18n = [
        'buttons' => [
            'cancel' => __('notifications.buttons.cancel'),
        ],
        'modals' => [
            'delete_single' => [
                'title' => __('notifications.modals.delete_single.title'),
                'message' => __('notifications.modals.delete_single.message'),
                'note' => __('notifications.modals.delete_single.note'),
                'confirm' => __('notifications.modals.delete_single.confirm'),
            ],
            'mark_all' => [
                'title' => __('notifications.modals.mark_all.title'),
                'message' => __('notifications.modals.mark_all.message'),
                'note' => __('notifications.modals.mark_all.note'),
                'confirm' => __('notifications.modals.mark_all.confirm'),
            ],
            'clear_read' => [
                'title' => __('notifications.modals.clear_read.title'),
                'message' => __('notifications.modals.clear_read.message'),
                'note' => __('notifications.modals.clear_read.note'),
                'confirm' => __('notifications.modals.clear_read.confirm'),
            ],
        ],
        'status' => [
            'deleting' => __('notifications.status.deleting'),
            'updating' => __('notifications.status.updating'),
        ],
        'messages' => [
            'delete_success' => __('notifications.messages.delete_success'),
            'delete_error' => __('notifications.messages.delete_error'),
            'mark_all_success' => __('notifications.messages.mark_all_success'),
            'mark_all_error' => __('notifications.messages.mark_all_error'),
            'clear_read_success' => __('notifications.messages.clear_read_success'),
            'clear_read_error' => __('notifications.messages.clear_read_error'),
        ],
    ];
@endphp
<script>
const NOTIFICATION_BADGE_SELECTOR = '[data-notification-badge]';
const FALLBACK_BADGE_ANIMATION_DURATION = 2000;
const NOTIFICATIONS_I18N = @js($notificationI18n);

function updateUnreadBadge(unreadCount) {
    if (window.NotificationManager && typeof window.NotificationManager.updateBadgeElements === 'function') {
        window.NotificationManager.updateBadgeElements(unreadCount);
        return;
    }

    const badges = document.querySelectorAll(NOTIFICATION_BADGE_SELECTOR);
    badges.forEach(badge => {
        const previousCount = parseInt(badge.dataset.previousCount || badge.textContent || '0', 10) || 0;
        badge.textContent = unreadCount;
        badge.dataset.previousCount = unreadCount;

        if (unreadCount > 0) {
            badge.classList.remove('hidden');
            badge.setAttribute('aria-hidden', 'false');

            if (unreadCount > previousCount) {
                badge.classList.add('animate-bounce');
                setTimeout(() => {
                    badge.classList.remove('animate-bounce');
                }, FALLBACK_BADGE_ANIMATION_DURATION);
            }
        } else {
            badge.classList.add('hidden');
            badge.setAttribute('aria-hidden', 'true');
        }
    });
}

function refreshNotificationCounts(force = false) {
    if (window.NotificationManager) {
        if (force && typeof window.NotificationManager.clearCache === 'function') {
            window.NotificationManager.clearCache();
        }

        window.NotificationManager.getNotifications((data, error) => {
            if (error) {
                console.error('Error refreshing notifications:', error);
                return;
            }

            const unreadCount = data?.unreadCount || 0;
            updateUnreadBadge(unreadCount);
        }, force);
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/notifications/api', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            const unreadCount = data.unreadCount || 0;
            updateUnreadBadge(unreadCount);
        })
        .catch(error => {
            console.error('Error refreshing notifications:', error);
        });
}

function initStatsSlider() {
    const sliderContainer = document.querySelector('[data-stats-container]');
    if (!sliderContainer) return;

    const slider = sliderContainer.querySelector('[data-stats-slider]');
    const track = slider?.querySelector('[data-stats-track]');
    const slides = slider ? Array.from(slider.querySelectorAll('[data-stat-slide]')) : [];
    const dotsContainer = sliderContainer.querySelector('[data-stats-dots]');
    const prevBtn = sliderContainer.querySelector('[data-stats-prev]');
    const nextBtn = sliderContainer.querySelector('[data-stats-next]');

    if (!slider || !track || !slides.length) return;

    let activeIndex = 0;
    let isDragging = false;
    let dragStartX = 0;
    const SLIDER_TRANSITION = 'transform 0.35s ease';

    const getGapSize = () => {
        const styles = getComputedStyle(track);
        const gapValue = parseFloat(styles.gap || styles.columnGap || '0');
        return Number.isNaN(gapValue) ? 0 : gapValue;
    };

    const getOffset = () => {
        const slideWidth = slider.getBoundingClientRect().width;
        return activeIndex * (slideWidth + getGapSize());
    };

    const goTo = newIndex => {
        activeIndex = (newIndex + slides.length) % slides.length;
        updateSlides();
    };

    const buildDots = () => {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = `stats-dot${index === activeIndex ? ' is-active' : ''}`;
            dot.setAttribute('aria-label', `${index + 1}`);
            dot.addEventListener('click', () => goTo(index));
            dotsContainer.appendChild(dot);
        });
    };

    const updateSlides = () => {
        if (window.matchMedia('(min-width: 640px)').matches) {
            track.style.transform = 'translateX(0)';
            return;
        }

        track.style.transition = track.style.transition || SLIDER_TRANSITION;
        track.style.transform = `translateX(-${getOffset()}px)`;

        dotsContainer?.querySelectorAll('.stats-dot').forEach((dot, idx) => {
            dot.classList.toggle('is-active', idx === activeIndex);
        });
    };

    const endDrag = clientX => {
        if (!isDragging) return;
        const delta = clientX - dragStartX;
        track.style.transition = SLIDER_TRANSITION;

        if (Math.abs(delta) > 45) {
            goTo(delta > 0 ? activeIndex - 1 : activeIndex + 1);
        } else {
            updateSlides();
        }

        isDragging = false;
    };

    track.addEventListener('pointerdown', event => {
        if (window.matchMedia('(min-width: 640px)').matches) return;
        isDragging = true;
        dragStartX = event.clientX;
        track.style.transition = 'none';
        track.setPointerCapture(event.pointerId);
    });

    track.addEventListener('pointermove', event => {
        if (!isDragging) return;
        const delta = event.clientX - dragStartX;
        track.style.transform = `translateX(${-(getOffset()) + delta}px)`;
    });

    ['pointerup', 'pointercancel', 'pointerleave'].forEach(eventName => {
        track.addEventListener(eventName, event => {
            endDrag(event.clientX || 0);
        });
    });

    prevBtn?.addEventListener('click', () => goTo(activeIndex - 1));
    nextBtn?.addEventListener('click', () => goTo(activeIndex + 1));

    window.addEventListener('resize', () => updateSlides());

    buildDots();
    updateSlides();
}

document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = Array.from(document.querySelectorAll('[data-filter-button]'));
    const searchInput = document.getElementById('notification-search');
    const filteredEmptyState = document.getElementById('filtered-empty-state');
    const notificationTimeline = document.getElementById('notification-timeline');

    initStatsSlider();

    const applyFilters = () => {
        const activeFilter = document.querySelector('[data-filter-button].is-active')?.dataset.filter || 'all';
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.notification-item').forEach(item => {
            const matchesFilter =
                activeFilter === 'all' ||
                (activeFilter === 'unread' && item.dataset.status === 'unread') ||
                (activeFilter === 'workshops' && item.dataset.type === 'workshops');

            const matchesSearch = !query || (item.dataset.search || '').includes(query);
            const shouldShow = matchesFilter && matchesSearch;

            item.classList.toggle('hidden', !shouldShow);
            if (shouldShow) {
                visibleCount += 1;
            }
        });

        if (notificationTimeline) {
            notificationTimeline.classList.toggle('hidden', visibleCount === 0);
        }
        if (filteredEmptyState) {
            filteredEmptyState.classList.toggle('hidden', visibleCount !== 0);
        }
    };

    window.applyNotificationFilters = applyFilters;

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            applyFilters();
        });
    });

    if (searchInput) {
        let searchDebounce;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(applyFilters, 200);
        });
    }

    applyFilters();

    const pollInterval = setInterval(() => refreshNotificationCounts(), 30000);
    refreshNotificationCounts();

    window.addEventListener('beforeunload', () => clearInterval(pollInterval));

    document.addEventListener('notifications:updated', event => {
        if (event.detail && typeof event.detail.unreadCount === 'number') {
            updateUnreadBadge(event.detail.unreadCount);
        }
    });

    window.addEventListener('focus', () => refreshNotificationCounts(true));

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshNotificationCounts(true);
        }
    });
    
    // تحديد إشعار كمقروء
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            markAsRead(notificationId);
        });
    });
    
    // حذف إشعار
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            deleteNotification(notificationId);
        });
    });
    
    const markAllButton = document.getElementById('mark-all-read');
    if (markAllButton) {
        markAllButton.addEventListener('click', () => markAllAsRead());
    }
    
    const clearReadButton = document.getElementById('clear-read');
    if (clearReadButton) {
        clearReadButton.addEventListener('click', () => clearReadNotifications());
    }
    
    // النقر على إشعار لتحديده كمقروء والتنقل
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;

            const notificationId = this.dataset.id;
            const actionUrl = this.dataset.actionUrl || '';

            if (typeof openNotification === 'function') {
                openNotification(notificationId, actionUrl);
                return;
            }

            const navigate = () => {
                if (actionUrl) {
                    window.location.href = actionUrl;
                }
            };

            if (notificationId) {
                markAsRead(notificationId).finally(navigate);
            } else {
                navigate();
            }
        });
    });
});

function markAsRead(notificationId) {
    return fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.add('opacity-90');
                notificationItem.dataset.status = 'read';
                
                const dot = notificationItem.querySelector('.animate-pulse');
                if (dot) dot.remove();
                
                const markReadBtn = notificationItem.querySelector('.mark-read-btn');
                if (markReadBtn) markReadBtn.remove();
            }

            refreshNotificationCounts(true);
            if (typeof window.applyNotificationFilters === 'function') {
                window.applyNotificationFilters();
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function deleteNotification(notificationId) {
    // إنشاء modal جميل للتأكيد
    const modalHTML = `
        <div id="delete-notification-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-100">
                <div class="text-center">
                    <!-- الأيقونة -->
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trash-alt text-red-600 text-3xl"></i>
                    </div>
                    
                    <!-- العنوان -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">${NOTIFICATIONS_I18N.modals.delete_single.title}</h3>
                    
                    <!-- الرسالة -->
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        ${NOTIFICATIONS_I18N.modals.delete_single.message}<br>
                        <span class="text-sm text-gray-500 mt-2 block">
                            ${NOTIFICATIONS_I18N.modals.delete_single.note}
                        </span>
                    </p>
                    
                    <!-- الأزرار -->
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <button id="cancel-delete-notification" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-times ml-2"></i>
                            ${NOTIFICATIONS_I18N.buttons.cancel}
                        </button>
                        <button id="confirm-delete-notification" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-trash ml-2"></i>
                            ${NOTIFICATIONS_I18N.modals.delete_single.confirm}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // إضافة الـ modal للصفحة
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // إضافة event listeners
    document.getElementById('cancel-delete-notification').addEventListener('click', function() {
        document.getElementById('delete-notification-modal').remove();
    });
    
    document.getElementById('confirm-delete-notification').addEventListener('click', function() {
        // إظهار حالة التحميل
        this.disabled = true;
        this.innerHTML = `<i class="fas fa-spinner fa-spin ml-2"></i>${NOTIFICATIONS_I18N.status.deleting}`;
        
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إظهار رسالة نجاح
                showSuccessMessage(NOTIFICATIONS_I18N.messages.delete_success);
                
                // حذف العنصر من الصفحة
                const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.style.transition = 'all 0.3s ease';
                    notificationItem.style.transform = 'translateX(100%)';
                    notificationItem.style.opacity = '0';
                    setTimeout(() => {
                        notificationItem.remove();
                        if (typeof window.applyNotificationFilters === 'function') {
                            window.applyNotificationFilters();
                        }
                    }, 300);
                }
                
                // إغلاق الـ modal
                document.getElementById('delete-notification-modal').remove();

                refreshNotificationCounts(true);
            } else {
                showErrorMessage(NOTIFICATIONS_I18N.messages.delete_error);
                document.getElementById('delete-notification-modal').remove();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
            showErrorMessage(NOTIFICATIONS_I18N.messages.delete_error);
            document.getElementById('delete-notification-modal').remove();
        });
    });
    
    // إغلاق الـ modal عند النقر خارجها
    document.getElementById('delete-notification-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.remove();
        }
    });
}

function markAllAsRead() {
    // إنشاء modal جميل للتأكيد
    const modalHTML = `
        <div id="mark-all-read-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-100">
                <div class="text-center">
                    <!-- الأيقونة -->
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check-double text-orange-600 text-3xl"></i>
                    </div>
                    
                    <!-- العنوان -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">${NOTIFICATIONS_I18N.modals.mark_all.title}</h3>
                    
                    <!-- الرسالة -->
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        ${NOTIFICATIONS_I18N.modals.mark_all.message}<br>
                        <span class="text-sm text-gray-500 mt-2 block">
                            ${NOTIFICATIONS_I18N.modals.mark_all.note}
                        </span>
                    </p>
                    
                    <!-- الأزرار -->
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <button id="cancel-mark-all-read" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-times ml-2"></i>
                            ${NOTIFICATIONS_I18N.buttons.cancel}
                        </button>
                        <button id="confirm-mark-all-read" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-check-double ml-2"></i>
                            ${NOTIFICATIONS_I18N.modals.mark_all.confirm}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // إضافة الـ modal للصفحة
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // إضافة event listeners
    document.getElementById('cancel-mark-all-read').addEventListener('click', function() {
        document.getElementById('mark-all-read-modal').remove();
    });
    
    document.getElementById('confirm-mark-all-read').addEventListener('click', function() {
        // إظهار حالة التحميل
        this.disabled = true;
        this.innerHTML = `<i class="fas fa-spinner fa-spin ml-2"></i>${NOTIFICATIONS_I18N.status.updating}`;
        
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إظهار رسالة نجاح
                showSuccessMessage(NOTIFICATIONS_I18N.messages.mark_all_success);
                refreshNotificationCounts(true);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showErrorMessage(NOTIFICATIONS_I18N.messages.mark_all_error);
                document.getElementById('mark-all-read-modal').remove();
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
            showErrorMessage(NOTIFICATIONS_I18N.messages.mark_all_error);
            document.getElementById('mark-all-read-modal').remove();
        });
    });
    
    // إغلاق الـ modal عند النقر خارجها
    document.getElementById('mark-all-read-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.remove();
        }
    });
}

function clearReadNotifications() {
    // إنشاء modal جميل للتأكيد
    const modalHTML = `
        <div id="clear-read-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-100">
                <div class="text-center">
                    <!-- الأيقونة -->
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trash-alt text-red-600 text-3xl"></i>
                    </div>
                    
                    <!-- العنوان -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">${NOTIFICATIONS_I18N.modals.clear_read.title}</h3>
                    
                    <!-- الرسالة -->
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        ${NOTIFICATIONS_I18N.modals.clear_read.message}<br>
                        <span class="text-sm text-gray-500 mt-2 block">
                            ${NOTIFICATIONS_I18N.modals.clear_read.note}
                        </span>
                    </p>
                    
                    <!-- الأزرار -->
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <button id="cancel-clear-read" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-times ml-2"></i>
                            ${NOTIFICATIONS_I18N.buttons.cancel}
                        </button>
                        <button id="confirm-clear-read" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                            <i class="fas fa-trash ml-2"></i>
                            ${NOTIFICATIONS_I18N.modals.clear_read.confirm}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // إضافة الـ modal للصفحة
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // إضافة event listeners
    document.getElementById('cancel-clear-read').addEventListener('click', function() {
        document.getElementById('clear-read-modal').remove();
    });
    
    document.getElementById('confirm-clear-read').addEventListener('click', function() {
        // إظهار حالة التحميل
        this.disabled = true;
        this.innerHTML = `<i class="fas fa-spinner fa-spin ml-2"></i>${NOTIFICATIONS_I18N.status.deleting}`;
        
        fetch('/notifications/clear-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إظهار رسالة نجاح
                showSuccessMessage(NOTIFICATIONS_I18N.messages.clear_read_success);
                refreshNotificationCounts(true);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showErrorMessage(NOTIFICATIONS_I18N.messages.clear_read_error);
                document.getElementById('clear-read-modal').remove();
            }
        })
        .catch(error => {
            console.error('Error clearing read notifications:', error);
            showErrorMessage(NOTIFICATIONS_I18N.messages.clear_read_error);
            document.getElementById('clear-read-modal').remove();
        });
    });
    
    // إغلاق الـ modal عند النقر خارجها
    document.getElementById('clear-read-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.remove();
        }
    });
}

// دالة إظهار رسالة النجاح
function showSuccessMessage(message) {
    const toastHTML = `
        <div id="success-toast" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-xl ml-3"></i>
                <span class="font-medium">${message}</span>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    
    // إظهار الرسالة
    setTimeout(() => {
        const toast = document.getElementById('success-toast');
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // إخفاء الرسالة بعد 3 ثوان
    setTimeout(() => {
        const toast = document.getElementById('success-toast');
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// دالة إظهار رسالة الخطأ
function showErrorMessage(message) {
    const toastHTML = `
        <div id="error-toast" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-xl ml-3"></i>
                <span class="font-medium">${message}</span>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    
    // إظهار الرسالة
    setTimeout(() => {
        const toast = document.getElementById('error-toast');
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // إخفاء الرسالة بعد 4 ثوان
    setTimeout(() => {
        const toast = document.getElementById('error-toast');
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 4000);
}
</script>
@endpush



