@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $isRtl = $isRtl ?? ($currentLocale === 'ar');
    $brandLogoBase = \App\Support\BrandAssets::logoBase($currentLocale);
    $brandLogoUrl = \App\Support\BrandAssets::logoAsset('webp', $currentLocale);
    $showAdminTools = auth()->user()?->isAdmin() ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="margin:0;padding:0;">
<head>
    @php
        $vite = app(\Illuminate\Foundation\Vite::class);
        $isViteHot = \App\Support\ViteHot::shouldUseHotReload();
    @endphp

    @if ($isViteHot)
        @vite(['resources/css/app.css', 'resources/css/non-critical.css', 'resources/js/app.js'])
    @else
        @php
            $appCss = $vite->asset('resources/css/app.css');
            $nonCriticalCss = $vite->asset('resources/css/non-critical.css');
        @endphp
        <link rel="preload" href="{{ $appCss }}" as="style">
        <link rel="stylesheet" href="{{ $appCss }}" data-critical-style="true">
        <noscript><link rel="stylesheet" href="{{ $appCss }}"></noscript>
        @vite(['resources/js/app.js'])
        <link rel="preload" href="{{ $nonCriticalCss }}" as="style">
        <link rel="stylesheet" href="{{ $nonCriticalCss }}" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="{{ $nonCriticalCss }}"></noscript>
    @endif
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="Peahskill">
    <meta name="theme-color" content="#0819ff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset($brandLogoBase . '.png') }}">
    <link rel="shortcut icon" href="{{ asset($brandLogoBase . '.png') }}">
    <title>@yield('title', 'Peahskill Platform')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap">
    </noscript>
    <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    </noscript>
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>
    <link rel="preload" as="image" href="{{ $brandLogoUrl }}" fetchpriority="high" type="image/webp">
    @stack('preloads')
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js" defer></script>

    @include('layouts.partials.critical-css')

    <!-- Simple dropdown fix -->
    <script>
        function toggleUserMenu() {
            const container = document.getElementById('user-menu-container');
            const dropdown = document.getElementById('user-menu-dropdown') || document.getElementById('dropdown-menu');
            const button = document.getElementById('user-menu-button');

            if (!dropdown || (container && container.dataset.dropdownInitialized === 'true')) {
                return;
            }

            const isHidden = dropdown.classList.contains('hidden');

            if (isHidden) {
                dropdown.classList.remove('hidden');
                dropdown.classList.add('show');
                dropdown.setAttribute('aria-hidden', 'false');
                if (button) {
                    button.setAttribute('aria-expanded', 'true');
                }
            } else {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('show');
                dropdown.setAttribute('aria-hidden', 'true');
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            }
        }
        window.toggleUserMenu = toggleUserMenu;
    </script>
    
    <script>
        window.__APP_LOCALE = "{{ $currentLocale }}";
        window.__CONTENT_TRANSLATIONS = @json($globalContentTranslations ?? []);
    </script>

    <!-- PAGE_STYLES:START -->
    @stack('styles')
    <!-- PAGE_STYLES:END -->
</head>
<body class="bg-gray-100 font-sans pb-24 md:pb-0" data-user-id="@auth{{ Auth::id() }}@endauth" style="margin:0;padding:0;">

    <!-- Header -->
    @php
        $showNavbarSearch = !($hideNavbarSearch ?? false);
        $hideNavbar = (bool) ($hideNavbar ?? false);
        $lockMobileTabBar = (bool) ($lockMobileTabBar ?? false);
        $hideBreadcrumbs = (bool) ($hideBreadcrumbs ?? false);
    @endphp
    @unless($hideNavbar)
        @include('partials.navbar', ['showNavbarSearch' => $showNavbarSearch, 'showBreadcrumbs' => !$hideBreadcrumbs])
    @endunless

    @if(!$hideNavbar && !$hideBreadcrumbs)
        @include('components.breadcrumbs', ['inNavbar' => true])
    @endif

    <!-- Main Navigation Bar -->
    <!-- <nav class="bg-white border-t border-gray-200 shadow-sm ">
        <div class="container mx-auto px-4 py-3">
            <ul class="flex justify-center space-x-8 rtl:space-x-reverse text-gray-700 font-semibold">
                <li><a href="{{ route('home') }}" class="hover:text-orange-500 transition-colors">Home</a></li>
                <li><a href="#" class="hover:text-orange-500 transition-colors">Workshops</a></li>
                <li><a href="#" class="hover:text-orange-500 transition-colors">Tools</a></li>
                <li><a href="#" class="hover:text-orange-500 transition-colors">Dessert recipes</a></li>
            </ul>
        </div>
    </nav> -->


    <!-- Page Content -->
    <main>
        @if($hideNavbar && !$hideBreadcrumbs)
            @include('components.breadcrumbs')
        @endif
        @yield('content')
    </main>

    @include('partials.mobile-tab-bar', ['lockVisibility' => $lockMobileTabBar])

    @if($showAdminTools)
        <!-- Load Cart Count Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load cart count on page load
            loadCartCount();
        });
        
        function loadCartCount() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    const cartCountEl = document.getElementById('cart-count');
                    if (cartCountEl) {
                        const oldCount = parseInt(cartCountEl.textContent) || 0;
                        const newCount = data.count;
                        
                        cartCountEl.textContent = newCount;
                        
                        if (newCount > 0) {
                            cartCountEl.classList.remove('hidden');
                            
                            // Adjust width based on number of digits
                            if (newCount > 9) {
                                cartCountEl.classList.remove('h-5', 'w-5');
                                cartCountEl.classList.add('h-6', 'w-6', 'px-1');
                            } else {
                                cartCountEl.classList.remove('h-6', 'w-6', 'px-1');
                                cartCountEl.classList.add('h-5', 'w-5');
                            }
                            
                            // Add animation if count increased
                            if (newCount > oldCount) {
                                cartCountEl.classList.add('animate-pulse');
                                setTimeout(() => {
                                    cartCountEl.classList.remove('animate-pulse');
                                }, 1000);
                            }
                        } else {
                            cartCountEl.classList.add('hidden');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }
        
        // Make function globally available
        window.loadCartCount = loadCartCount;
        </script>
    @endif

    <!-- Footer -->
    @unless(isset($hideFooter) && $hideFooter)
        @include('layouts.partials.footer')
    @endunless

    @include('components.confirmation-modal')
    
    @php
        $notificationDropdownCopy = [
            'loading_title' => __('navbar.notifications_dropdown.loading_title'),
            'loading_message' => __('navbar.notifications_dropdown.loading_message'),
            'empty_title' => __('navbar.notifications_dropdown.empty_title'),
            'empty_message' => __('navbar.notifications_dropdown.empty_message'),
            'error_title' => __('navbar.notifications_dropdown.error_title'),
            'error_message' => __('navbar.notifications_dropdown.error_message'),
            'new_badge' => __('navbar.notifications_dropdown.new_badge'),
            'status_labels' => [
                'workshop_booking' => __('navbar.notifications_dropdown.status_labels.workshop_booking'),
                'workshop_confirmed' => __('navbar.notifications_dropdown.status_labels.workshop_confirmed'),
                'workshop_cancelled' => __('navbar.notifications_dropdown.status_labels.workshop_cancelled'),
                'default' => __('navbar.notifications_dropdown.status_labels.default'),
            ],
            'unread_summary' => [
                'zero' => __('navbar.notifications_dropdown.unread_summary.zero'),
                'one' => __('navbar.notifications_dropdown.unread_summary.one'),
                'two' => __('navbar.notifications_dropdown.unread_summary.two'),
                'few' => __('navbar.notifications_dropdown.unread_summary.few'),
                'many' => __('navbar.notifications_dropdown.unread_summary.many'),
                'other' => __('navbar.notifications_dropdown.unread_summary.other'),
            ],
        ];
    @endphp

    <!-- Notification System JavaScript -->
    <script>
        const NOTIFICATION_DROPDOWN_I18N = @js($notificationDropdownCopy);
        const NOTIFICATION_TYPE_META = {
            workshop_booking: {
                icon: 'fa-calendar-plus',
                iconWrapper: 'bg-gradient-to-br from-white via-sky-50 to-sky-100 text-sky-600 border border-sky-100/80 shadow-[0_12px_30px_rgba(14,165,233,0.18)]',
                iconColor: 'text-sky-600',
                badgeClass: 'bg-white text-sky-700 border border-sky-100/70 shadow-sm',
                timelineAccent: 'from-sky-200/80 via-sky-100/20 to-transparent',
            },
            workshop_confirmed: {
                icon: 'fa-check-circle',
                iconWrapper: 'bg-gradient-to-br from-white via-emerald-50 to-emerald-100 text-emerald-600 border border-emerald-100/80 shadow-[0_12px_30px_rgba(16,185,129,0.18)]',
                iconColor: 'text-emerald-600',
                badgeClass: 'bg-white text-emerald-700 border border-emerald-100/70 shadow-sm',
                timelineAccent: 'from-emerald-200/80 via-emerald-100/20 to-transparent',
            },
            workshop_cancelled: {
                icon: 'fa-times-circle',
                iconWrapper: 'bg-gradient-to-br from-white via-rose-50 to-rose-100 text-rose-600 border border-rose-100/80 shadow-[0_12px_30px_rgba(244,63,94,0.18)]',
                iconColor: 'text-rose-600',
                badgeClass: 'bg-white text-rose-700 border border-rose-100/70 shadow-sm',
                timelineAccent: 'from-rose-200/80 via-rose-100/20 to-transparent',
            },
            default: {
                icon: 'fa-bell',
                iconWrapper: 'bg-gradient-to-br from-white via-slate-50 to-slate-100 text-slate-600 border border-slate-100/80 shadow-[0_12px_30px_rgba(15,23,42,0.12)]',
                iconColor: 'text-slate-600',
                badgeClass: 'bg-white text-slate-700 border border-slate-100/80 shadow-sm',
                timelineAccent: 'from-orange-200/70 via-orange-100/20 to-transparent',
            },
        };

        function escapeHtml(value = '') {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttribute(value = '') {
            return escapeHtml(value);
        }

        function getNotificationTypeMeta(type) {
            const baseMeta = NOTIFICATION_TYPE_META[type] || NOTIFICATION_TYPE_META.default;
            const labels = NOTIFICATION_DROPDOWN_I18N.status_labels || {};
            return {
                ...baseMeta,
                label: labels[type] || labels.default || '',
            };
        }

        function renderNotificationDropdownState(state) {
            const stateCopy = {
                loading: {
                    icon: 'fa-spinner fa-spin',
                    iconClasses: 'text-orange-500',
                    title: NOTIFICATION_DROPDOWN_I18N.loading_title,
                    message: NOTIFICATION_DROPDOWN_I18N.loading_message,
                },
                empty: {
                    icon: 'fa-bell-slash',
                    iconClasses: 'text-slate-400',
                    title: NOTIFICATION_DROPDOWN_I18N.empty_title,
                    message: NOTIFICATION_DROPDOWN_I18N.empty_message,
                },
                error: {
                    icon: 'fa-triangle-exclamation',
                    iconClasses: 'text-rose-500',
                    title: NOTIFICATION_DROPDOWN_I18N.error_title,
                    message: NOTIFICATION_DROPDOWN_I18N.error_message,
                },
            }[state];

            if (!stateCopy) {
                return '';
            }

            return `
                <div class="relative overflow-hidden rounded-3xl border border-dashed border-slate-200/70 bg-white/95 px-5 py-6 text-center shadow-sm">
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(251,146,60,0.08),_transparent_65%)]"></div>
                    <div class="relative mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-orange-500 shadow-inner">
                        <i class="fas ${stateCopy.icon} ${stateCopy.iconClasses} text-xl"></i>
                    </div>
                    <div class="relative mt-3 space-y-1">
                        <p class="text-sm font-semibold text-slate-900">${escapeHtml(stateCopy.title || '')}</p>
                        <p class="text-xs text-slate-500">${escapeHtml(stateCopy.message || '')}</p>
                    </div>
                </div>
            `;
        }

        function applyCountTemplate(template, count) {
            return (template || '').replace(':count', count);
        }

        function formatUnreadSummary(count = 0) {
            const summary = NOTIFICATION_DROPDOWN_I18N.unread_summary || {};
            if (count <= 0) {
                return summary.zero || '';
            }
            if (count === 1) {
                return applyCountTemplate(summary.one, count) || applyCountTemplate(summary.other, count);
            }
            if (count === 2) {
                return applyCountTemplate(summary.two, count) || applyCountTemplate(summary.other, count);
            }
            if (count >= 3 && count <= 10) {
                return applyCountTemplate(summary.few, count) || applyCountTemplate(summary.other, count);
            }
            if (count > 10) {
                return applyCountTemplate(summary.many, count) || applyCountTemplate(summary.other, count);
            }
            return applyCountTemplate(summary.other, count) || '';
        }

        function updateNotificationSummary(count = 0) {
            const summaryElement = document.getElementById('notification-unread-summary');
            if (summaryElement) {
                summaryElement.textContent = formatUnreadSummary(count);
            }
        }

        function formatNotificationTimestamp(value) {
            if (!value) {
                return '';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const locale = document.documentElement?.lang || 'ar';
            let absolute = '';

            try {
                const absoluteFormatter = new Intl.DateTimeFormat(locale === 'ar' ? 'ar-SA' : locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                absolute = absoluteFormatter.format(date);
            } catch (error) {
                absolute = date.toLocaleString();
            }

            let relativePart = '';
            if (typeof Intl !== 'undefined' && typeof Intl.RelativeTimeFormat !== 'undefined') {
                const now = Date.now();
                const diffMs = date.getTime() - now;
                const diffSeconds = Math.round(diffMs / 1000);
                const divisions = [
                    { amount: 60, unit: 'second' },
                    { amount: 60, unit: 'minute' },
                    { amount: 24, unit: 'hour' },
                    { amount: 7, unit: 'day' },
                    { amount: 4.34524, unit: 'week' },
                    { amount: 12, unit: 'month' },
                    { amount: Number.POSITIVE_INFINITY, unit: 'year' },
                ];

                let duration = diffSeconds;
                let unit = 'second';

                for (const division of divisions) {
                    if (Math.abs(duration) < division.amount) {
                        unit = division.unit;
                        break;
                    }
                    duration /= division.amount;
                }

                const rtf = new Intl.RelativeTimeFormat(locale === 'ar' ? 'ar' : locale, { numeric: 'auto' });
                relativePart = rtf.format(Math.round(duration), unit);
            }

            return relativePart ? `${relativePart} · ${absolute}` : absolute;
        }

        function buildNotificationCard(notification = {}) {
            const meta = getNotificationTypeMeta(notification.type);
            const safeId = escapeAttribute(notification.id ?? '');
            const safeUrl = escapeAttribute(notification.action_url ?? '');
            const safeTitle = escapeHtml(notification.title || '');
            const rawMessage = String(notification.message ?? '');
            const truncatedMessage = rawMessage.length > 140
                ? `${rawMessage.slice(0, 137)}…`
                : rawMessage;
            const safeMessage = escapeHtml(truncatedMessage);
            const isRead = Boolean(notification.is_read);

            const cardStateClasses = isRead
                ? 'bg-white/95 shadow-[0_6px_16px_rgba(15,23,42,0.08)] hover:-translate-y-0.5'
                : 'bg-gradient-to-r from-white via-orange-50/40 to-white shadow-[0_12px_24px_rgba(251,146,60,0.12)] hover:-translate-y-0.5';

            const newBadge = !isRead && NOTIFICATION_DROPDOWN_I18N.new_badge
                ? `<span class="inline-flex items-center gap-1 rounded-full bg-orange-100/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.25em] text-orange-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                         ${escapeHtml(NOTIFICATION_DROPDOWN_I18N.new_badge)}
                    </span>`
                : '';

            const unreadIndicator = !isRead
                ? '<span class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-orange-500 ring-2 ring-white shadow-[0_0_0_3px_rgba(251,146,60,0.35)] animate-pulse"></span>'
                : '';

            return `
                <article class="notification-dropdown-item group relative flex cursor-pointer items-center gap-2 rounded-[14px] px-3 py-1 transition-all duration-200 ${cardStateClasses}" data-notification-id="${safeId}" data-action-url="${safeUrl}">
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-xl ${meta.iconWrapper}">
                        <i class="fas ${meta.icon} ${meta.iconColor} text-[13px]"></i>
                        ${unreadIndicator}
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5">
                        <div class="flex items-center justify-between gap-1.5">
                            <p class="text-[13px] font-semibold leading-tight text-slate-900 ${isRead ? 'opacity-80' : ''}">${safeTitle}</p>
                            <span class="inline-flex flex-shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${meta.badgeClass}">
                                <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                ${escapeHtml(meta.label || '')}
                            </span>
                        </div>
                        <p class="text-[11px] leading-tight text-slate-600 max-h-[2rem] overflow-hidden">${safeMessage}</p>
                        <div class="flex items-center justify-between text-[10px] font-semibold text-slate-400">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-clock text-[9px]"></i>
                                ${formatNotificationTimestamp(notification.created_at)}
                            </span>
                            ${newBadge}
                        </div>
                    </div>
                </article>
            `;
        }

        function attachNotificationDropdownHandlers(rootElement) {
            if (!rootElement) {
                return;
            }

            rootElement.querySelectorAll('.notification-dropdown-item').forEach(item => {
                item.addEventListener('click', (event) => {
                    if (event.target.closest('button')) {
                        return;
                    }

                    const notificationId = item.dataset.notificationId;
                    const actionUrl = item.dataset.actionUrl || '';

                    if (typeof openNotification === 'function') {
                        openNotification(notificationId, actionUrl);
                        return;
                    }

                    if (actionUrl) {
                        window.location.href = actionUrl;
                    } else if (notificationId) {
                        markNotificationAsRead(notificationId);
                    }
                });
            });
        }

        let latestDropdownData = {
            notifications: [],
            unreadCount: 0,
        };
        let notificationAutoMarkPromise = null;

        window.updateNotificationsUI = function(notifications = [], unreadCount = 0) {
            const notificationList = document.getElementById('notification-list');

            latestDropdownData = {
                notifications: Array.isArray(notifications) ? [...notifications] : [],
                unreadCount,
            };

            updateNotificationSummary(unreadCount);

            if (!notificationList) {
                return;
            }

            if (!Array.isArray(notifications) || notifications.length === 0) {
                notificationList.innerHTML = renderNotificationDropdownState('empty');
                return;
            }

            const limitedNotifications = notifications.slice(0, 4);
            notificationList.innerHTML = limitedNotifications.map(buildNotificationCard).join('');
            attachNotificationDropdownHandlers(notificationList);
        };

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notification-dropdown');
            const bellButton = document.getElementById('notification-bell');
            if (!dropdown) {
                return;
            }

            dropdown.classList.toggle('hidden');
            const isOpen = !dropdown.classList.contains('hidden');
            if (bellButton) {
                bellButton.setAttribute('aria-expanded', String(isOpen));
            }

            if (isOpen) {
                loadNotificationDropdown();
            }
        }

        // Close notification dropdown when clicking outside
        document.addEventListener('click', (event) => {
            const notificationContainer = document.getElementById('notification-container');
            const notificationDropdown = document.getElementById('notification-dropdown');
            
            if (notificationContainer && notificationDropdown && 
                !notificationContainer.contains(event.target)) {
                notificationDropdown.classList.add('hidden');
                const bellButton = document.getElementById('notification-bell');
                if (bellButton) {
                    bellButton.setAttribute('aria-expanded', 'false');
                }
            }
        });

        // Load notifications in dropdown
        function loadNotificationDropdown() {
            const notificationList = document.getElementById('notification-list');
            if (!notificationList) return;

            notificationList.innerHTML = renderNotificationDropdownState('loading');

            const handleSuccess = (data) => {
                window.updateNotificationsUI(data?.notifications || [], data?.unreadCount || 0);
                markDropdownNotificationsAsRead();
            };

            const handleError = () => {
                notificationList.innerHTML = renderNotificationDropdownState('error');
            };

            if (window.NotificationManager) {
                window.NotificationManager.getNotifications((data, error) => {
                    if (error) {
                        console.error('Error loading notifications:', error);
                        handleError();
                        return;
                    }

                    handleSuccess(data);
                }, true);
                return;
            }

            fetch('/notifications/api', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(handleSuccess)
            .catch(error => {
                console.error('Error loading notifications:', error);
                handleError();
            });
        }

        function markDropdownNotificationsAsRead() {
            if (notificationAutoMarkPromise) {
                return notificationAutoMarkPromise;
            }

            if (window.NotificationManager && typeof window.NotificationManager.markAllAsRead === 'function') {
                notificationAutoMarkPromise = window.NotificationManager.markAllAsRead()
                    .then(() => new Promise(resolve => {
                        window.NotificationManager.getNotifications((freshData) => {
                            const unread = freshData?.unreadCount || 0;
                            const notifications = freshData?.notifications || [];
                            latestDropdownData = {
                                notifications,
                                unreadCount: unread,
                            };
                            window.updateNotificationsUI(notifications, unread);
                            resolve(freshData);
                        });
                    }))
                    .catch(error => {
                        console.error('Error marking notifications as read:', error);
                    })
                    .finally(() => {
                        notificationAutoMarkPromise = null;
                    });

                return notificationAutoMarkPromise;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const timestamp = new Date().toISOString();

            notificationAutoMarkPromise = fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    const unreadCount = data?.unreadCount || 0;
                    latestDropdownData = {
                        notifications: latestDropdownData.notifications.map(notification => ({
                            ...notification,
                            is_read: true,
                            read_at: notification.read_at || timestamp,
                        })),
                        unreadCount,
                    };
                    window.updateNotificationsUI(latestDropdownData.notifications, unreadCount);

                    const badges = document.querySelectorAll('[data-notification-badge]');
                    badges.forEach(badge => {
                        badge.textContent = unreadCount;
                        badge.dataset.previousCount = unreadCount;
                        if (unreadCount > 0) {
                            badge.classList.remove('hidden');
                            badge.setAttribute('aria-hidden', 'false');
                        } else {
                            badge.classList.add('hidden');
                            badge.setAttribute('aria-hidden', 'true');
                        }
                    });
                })
                .catch(error => {
                    console.error('Error marking notifications as read:', error);
                })
                .finally(() => {
                    notificationAutoMarkPromise = null;
                });

            return notificationAutoMarkPromise;
        }

        // Mark notification as read
        function markNotificationAsRead(notificationId) {
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
                    updateNotificationCounts();
                    loadNotificationDropdown();
                }

                return data;
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                throw error;
            });
        }

        function openNotification(notificationId, actionUrl = '') {
            const navigate = () => {
                if (actionUrl) {
                    window.location.href = actionUrl;
                }
            };

            if (!notificationId) {
                navigate();
                return;
            }

            markNotificationAsRead(notificationId)
                .finally(navigate);
        }

        window.openNotification = openNotification;

        // Update notification counts
        function updateNotificationCounts() {
            if (window.NotificationManager) {
                window.NotificationManager.getNotifications((data, error) => {
                    if (error) {
                        console.error('Error updating notification counts:', error);
                        return;
                    }

                    const count = data?.unreadCount || 0;
                    updateNotificationSummary(count);
                    if (typeof window.NotificationManager.updateBadgeElements === 'function') {
                        window.NotificationManager.updateBadgeElements(count);
                    } else {
                        const badges = document.querySelectorAll('[data-notification-badge]');
                        badges.forEach(element => {
                            element.textContent = count;
                            if (count > 0) {
                                element.classList.remove('hidden');
                                element.setAttribute('aria-hidden', 'false');
                            } else {
                                element.classList.add('hidden');
                                element.setAttribute('aria-hidden', 'true');
                            }
                        });
                    }
                });
            }
        }

        // Mark all notifications as read
        document.addEventListener('click', (event) => {
            const markAllButton = typeof event.target.closest === 'function'
                ? event.target.closest('#mark-all-read-btn')
                : null;
            if (markAllButton) {
                event.preventDefault();
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
                        updateNotificationCounts();
                        loadNotificationDropdown();
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
            }
        });

        // Initialize notification system
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationCounts();
            setInterval(updateNotificationCounts, 30000);
        });
    </script>
    
    <script>
        // Simple dropdown function (backup)
        function toggleUserMenuBackup() {
            const container = document.getElementById('user-menu-container');
            const dropdownMenu = document.getElementById('user-menu-dropdown') || document.getElementById('dropdown-menu');
            const userMenuButton = document.getElementById('user-menu-button');

            if (!dropdownMenu || !userMenuButton || (container && container.dataset.dropdownInitialized === 'true')) {
                return;
            }

            const isHidden = dropdownMenu.classList.contains('hidden');

            if (isHidden) {
                dropdownMenu.classList.remove('hidden');
                dropdownMenu.classList.add('show');
                dropdownMenu.setAttribute('aria-hidden', 'false');
                userMenuButton.setAttribute('aria-expanded', 'true');
            } else {
                dropdownMenu.classList.add('hidden');
                dropdownMenu.classList.remove('show');
                dropdownMenu.setAttribute('aria-hidden', 'true');
                userMenuButton.setAttribute('aria-expanded', 'false');
            }
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('user-menu-container');
            const dropdownMenu = document.getElementById('user-menu-dropdown') || document.getElementById('dropdown-menu');
            const userMenuButton = document.getElementById('user-menu-button');
            
            if (!dropdownMenu || !userMenuButton || (container && container.dataset.dropdownInitialized === 'true')) {
                return;
            }
            
            if (
                !userMenuButton.contains(e.target) &&
                !dropdownMenu.contains(e.target)
            ) {
                dropdownMenu.classList.add('hidden');
                dropdownMenu.classList.remove('show');
                dropdownMenu.setAttribute('aria-hidden', 'true');
                userMenuButton.setAttribute('aria-expanded', 'false');
                console.log('Dropdown closed - clicked outside');
            }
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM Content Loaded - Initializing mobile menu...');
            
            // Simple dropdown setup (fallback)
            const container = document.getElementById('user-menu-container');
            const userMenuButton = document.getElementById('user-menu-button');
            const dropdownMenu = document.getElementById('user-menu-dropdown') || document.getElementById('dropdown-menu');
            
            if (container && userMenuButton && dropdownMenu && container.dataset.dropdownInitialized !== 'true') {
                dropdownMenu.classList.add('hidden');
                dropdownMenu.classList.remove('show');
                dropdownMenu.setAttribute('aria-hidden', 'true');
                userMenuButton.setAttribute('aria-expanded', 'false');
                container.dataset.dropdownInitialized = 'fallback';
                
                const fallbackClickHandler = function(e) {
                    if (container.dataset.dropdownInitialized === 'true') {
                        userMenuButton.removeEventListener('click', fallbackClickHandler);
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();
                    toggleUserMenuBackup();
                };

                userMenuButton.addEventListener('click', fallbackClickHandler);
            }
            
        });
        
        // Mobile menu is now handled by mobile-menu.js
            
            // Mobile search functionality
            const searchInput = document.getElementById('search-input');
            const mobileSearchInput = document.getElementById('mobile-search-input');
            const searchSubmit = document.getElementById('search-submit');
            const mobileSearchSubmit = document.getElementById('mobile-search-submit');
            
            function performSearch(query) {
                if (query.trim()) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
            
            if (searchInput && searchSubmit) {
                searchSubmit.addEventListener('click', (e) => {
                    e.preventDefault();
                    performSearch(searchInput.value);
                });
                
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch(searchInput.value);
                    }
                });
            }
            
            if (mobileSearchInput && mobileSearchSubmit) {
                mobileSearchSubmit.addEventListener('click', (e) => {
                    e.preventDefault();
                    performSearch(mobileSearchInput.value);
                });
                
                mobileSearchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch(mobileSearchInput.value);
                    }
                });
            }
            
            // Touch improvements
            if ('ontouchstart' in window) {
                // Add touch class to body for CSS targeting
                document.body.classList.add('touch-device');
                
                // Improve touch targets
                const touchElements = document.querySelectorAll('a, button, input, select, textarea');
                touchElements.forEach(element => {
                    if (element.offsetHeight < 44) {
                        element.classList.add('btn-touch');
                    }
                });
            }
            
            // Prevent zoom on input focus (iOS)
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    if (window.innerWidth < 768) {
                        const viewport = document.querySelector('meta[name="viewport"]');
                        if (viewport) {
                            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
                        }
                    }
                });
                
                input.addEventListener('blur', () => {
                    if (window.innerWidth < 768) {
                        const viewport = document.querySelector('meta[name="viewport"]');
                        if (viewport) {
                            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
                        }
                    }
                });
            });
            
            // Smooth scrolling for mobile
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    const targetId = link.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Lazy loading for images
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.classList.remove('lazy');
                                observer.unobserve(img);
                            }
                        }
                    });
                });
                
                const lazyImages = document.querySelectorAll('img[data-src]');
                lazyImages.forEach(img => imageObserver.observe(img));
            }
        });
        });
    </script>
    <script>
        (function () {
            const KB_IN_BYTES = 1024;

            function handleFileInput(event) {
                const target = event.target;
                if (!target || target.tagName !== 'INPUT' || target.type !== 'file') {
                    return;
                }

                const maxSizeKb = parseInt(target.dataset.maxSize || '', 10);
                if (!maxSizeKb) {
                    return;
                }

                const files = target.files;
                if (!files || files.length === 0) {
                    hideMessage(target);
                    return;
                }

                const maxBytes = maxSizeKb * KB_IN_BYTES;
                const oversizeFile = Array.from(files).find(file => file.size > maxBytes);

                if (oversizeFile) {
                    target.value = '';
                    const message = target.dataset.maxSizeMessage
                        || `You cannot upload a file larger than ${formatMegabytes(maxSizeKb)} MB.`;
                    showMessage(target, message);
                    return;
                }

                hideMessage(target);
            }

            function showMessage(input, message) {
                const selector = input.dataset.errorTarget;
                if (selector) {
                    document.querySelectorAll(selector).forEach(el => {
                        el.textContent = message;
                        el.classList.remove('hidden');
                    });
                } else {
                    window.alert(message);
                }
            }

            function hideMessage(input) {
                const selector = input.dataset.errorTarget;
                if (!selector) {
                    return;
                }

                document.querySelectorAll(selector).forEach(el => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });
            }

            function formatMegabytes(valueKb) {
                const value = valueKb / KB_IN_BYTES;
                return Number.isInteger(value) ? value : value.toFixed(1);
            }

            document.addEventListener('change', handleFileInput);
            document.addEventListener('input', handleFileInput);
        })();
    </script>
    <div id="page-script-stack" data-page-scripts>
        @stack('scripts')
    </div>
    
    <script>
        function runMobileMenuSetup() {
            if (typeof setupMobileMenu === 'function') {
                setupMobileMenu();
                return true;
            }

            if (typeof window.setupMobileMenu === 'function') {
                window.setupMobileMenu();
                return true;
            }

            console.warn('setupMobileMenu is not available yet');
            return false;
        }

        function initializeMobileMenu() {
            runMobileMenuSetup();
        }

        function testMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            if (mobileMenu && menuIcon && closeIcon) {
                mobileMenu.classList.toggle('hidden');
                mobileMenu.classList.toggle('show');
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            } else {
                console.warn('Mobile menu elements not found for testing');
            }
        }

        function diagnoseMobileMenu() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            console.log('Mobile menu diagnostics', {
                mobileMenuBtn: !!mobileMenuBtn,
                mobileMenu: !!mobileMenu,
                menuIcon: !!menuIcon,
                closeIcon: !!closeIcon,
            });
        }

        window.initializeMobileMenu = initializeMobileMenu;
        window.testMobileMenu = testMobileMenu;
        window.diagnoseMobileMenu = diagnoseMobileMenu;
        if (typeof setupMobileMenu === 'function') {
            window.setupMobileMenu = setupMobileMenu;
        }

        window.addEventListener('load', function() {
            if (window.initDesktopDropdown) {
                window.initDesktopDropdown();
            }
            runMobileMenuSetup();
        });

        document.addEventListener('DOMContentLoaded', runMobileMenuSetup);

        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                setTimeout(runMobileMenuSetup, 100);
            }
        });

        setTimeout(runMobileMenuSetup, 1000);
    </script>

    
    @php
        $logoutSuccessNeedles = array_filter(['تسجيل الخروج', __('flash.success.auth.logout')]);
    @endphp
    <!-- رسالة تسجيل الخروج -->
    @if(session('success') && \Illuminate\Support\Str::contains(session('success'), $logoutSuccessNeedles))
        <div id="logout-toast" class="logout-toast">
            <i class="fas fa-check-circle icon"></i>
            <div>
                <div class="font-semibold">Signed out successfully!</div>
                <div class="text-sm opacity-90">See you soon</div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('logout-toast');
                if (toast) {
                    // إظهار الرسالة فوراً
                    toast.classList.add('show');
                    
                    // إخفاء الرسالة بعد 2 ثانية بدلاً من 3
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    }, 2000);
                }
            });
        </script>
    @endif
    
    @php
        $authSuccessNeedles = array_filter([
            'تسجيل الدخول',
            'إنشاء حساب',
            'تم تسجيل الدخول',
            'تم إنشاء حساب',
            'تم إرسال الرمز',
            'تم التحقق',
            'تم إنشاء',
            'تم تفعيل',
            'تمت إضافة',
            'تم إرسال',
            __('flash.success.auth.register.welcome'),
            __('flash.success.auth.register.email_verified'),
            __('flash.success.auth.register.complete_chef_profile'),
            __('flash.success.auth.register.workshop_flow'),
            __('flash.success.auth.social.new_generic'),
            __('flash.success.auth.social.existing_generic'),
            __('flash.success.auth.social.new_customer'),
            __('flash.success.auth.social.existing_customer'),
            __('flash.success.auth.social.new_chef'),
            __('flash.success.auth.social.existing_chef'),
        ]);
    @endphp
    <!-- رسالة نجاح تسجيل الدخول/التسجيل -->
    @if(session('success') && \Illuminate\Support\Str::contains(session('success'), $authSuccessNeedles))
        <div id="auth-success-toast" class="auth-success-toast">
            <i class="fas fa-check-circle icon"></i>
            <div>
                <div class="font-semibold">{{ session('success') }}</div>
                <div class="text-sm opacity-90">Enjoy your Wasfah experience</div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('auth-success-toast');
                if (toast) {
                    // إظهار الرسالة فوراً
                    toast.classList.add('show');
                    
                    // إخفاء الرسالة بعد 2 ثانية بدلاً من 4
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    }, 2000);
                }
            });
        </script>
    @endif
    
    <!-- رسالة إعلامية -->
    @if(session('info'))
        <div id="info-toast" class="info-toast">
            <i class="fas fa-info-circle icon"></i>
            <div>
                <div class="font-semibold">{{ session('info') }}</div>
                <div class="text-sm opacity-90">You were redirected to the homepage</div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('info-toast');
                if (toast) {
                    // إظهار الرسالة فوراً
                    toast.classList.add('show');
                    
                    // إخفاء الرسالة بعد 2 ثانية بدلاً من 4
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    }, 2000);
                }
            });
        </script>
    @endif
    
    <!-- رسالة خطأ -->
    @if(session('error'))
        <div id="error-toast" class="error-toast">
            <i class="fas fa-exclamation-triangle icon"></i>
            <div>
                <div class="font-semibold">{{ session('error') }}</div>
                <div class="text-sm opacity-90">Please try again</div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('error-toast');
                if (toast) {
                    // إظهار الرسالة فوراً
                    toast.classList.add('show');
                    
                    // إخفاء الرسالة بعد 2 ثانية بدلاً من 4
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    }, 2000);
                }
            });
        </script>
    @endif
    
    <!-- دالة معالجة روابط Google Drive -->
    <script>
        function convertGoogleDriveUrl(url) {
            if (!url || !url.includes('drive.google.com')) {
                return url;
            }
            
            try {
                // تنسيق 1: https://drive.google.com/file/d/FILE_ID/view
                let match = url.match(/\/file\/d\/([a-zA-Z0-9-_]+)/);
                if (match && match[1]) {
                    return `https://lh3.googleusercontent.com/d/${match[1]}`;
                }
                
                // تنسيق 2: https://drive.google.com/open?id=FILE_ID
                if (url.includes('id=')) {
                    const urlParams = new URLSearchParams(new URL(url).search);
                    const fileId = urlParams.get('id');
                    if (fileId) {
                        return `https://lh3.googleusercontent.com/d/${fileId}`;
                    }
                }
                
                // تنسيق 3: https://drive.google.com/uc?id=FILE_ID
                if (url.includes('uc?id=')) {
                    const urlParams = new URLSearchParams(new URL(url).search);
                    const fileId = urlParams.get('id');
                    if (fileId) {
                        return `https://lh3.googleusercontent.com/d/${fileId}`;
                    }
                }
                
                // تنسيق 4: https://drive.google.com/thumbnail?id=FILE_ID
                if (url.includes('thumbnail?id=')) {
                    const urlParams = new URLSearchParams(new URL(url).search);
                    const fileId = urlParams.get('id');
                    if (fileId) {
                        return `https://lh3.googleusercontent.com/d/${fileId}`;
                    }
                }
                
                // تنسيق 5: استخراج ID من أي رابط Google Drive
                const idMatch = url.match(/[a-zA-Z0-9-_]{25,}/);
                if (idMatch) {
                    return `https://lh3.googleusercontent.com/d/${idMatch[0]}`;
                }
                
            } catch (error) {
                console.warn('Error converting Google Drive URL:', error);
            }
            
            return url;
        }
        
        // تطبيق التحويل على جميع الصور عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            // معالجة جميع الصور التي تحتوي على روابط Google Drive
            const images = document.querySelectorAll('img[src*="drive.google.com"]');
            images.forEach(function(img) {
                const originalSrc = img.src;
                const convertedSrc = convertGoogleDriveUrl(originalSrc);
                if (convertedSrc !== originalSrc) {
                    img.src = convertedSrc;
                }
            });
            
            // معالجة الصور التي يتم تحميلها ديناميكياً
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'IMG' && node.src && node.src.includes('drive.google.com')) {
                                const convertedSrc = convertGoogleDriveUrl(node.src);
                                if (convertedSrc !== node.src) {
                                    node.src = convertedSrc;
                                }
                            }
                            // معالجة الصور داخل العناصر المضافة
                            const images = node.querySelectorAll && node.querySelectorAll('img[src*="drive.google.com"]');
                            if (images) {
                                images.forEach(function(img) {
                                    const convertedSrc = convertGoogleDriveUrl(img.src);
                                    if (convertedSrc !== img.src) {
                                        img.src = convertedSrc;
                                    }
                                });
                            }
                        }
                    });
                });
            });
            
            // مراقبة التغييرات في DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
    </script>
    <script>
        // Fallback: shrink/expand navbar on mobile without relying on lazy modules
        (function () {
            if (typeof window === 'undefined' || typeof document === 'undefined' || window.__navbarCompactionInit) {
                return;
            }

            const navbar = document.querySelector('[data-navbar-layer]');
            if (!navbar) {
                return;
            }

            const mobileQuery = window.matchMedia('(max-width: 768px)');
            const THRESHOLD = 48;
            const DELTA = 6;
            let lastY = window.scrollY || window.pageYOffset || 0;
            let ticking = false;

            const applyState = () => {
                ticking = false;
                const isMobile = mobileQuery ? mobileQuery.matches : true;
                const y = window.scrollY || window.pageYOffset || 0;

                if (!isMobile) {
                    navbar.classList.remove('navbar-compact');
                    lastY = y;
                    return;
                }

                const diff = y - lastY;

                if (y <= THRESHOLD || diff < -DELTA) {
                    navbar.classList.remove('navbar-compact');
                } else if (diff > DELTA && y > THRESHOLD) {
                    navbar.classList.add('navbar-compact');
                }

                lastY = y;
            };

            const onScroll = () => {
                if (ticking) {
                    return;
                }
                ticking = true;
                (window.requestAnimationFrame || window.setTimeout)(() => {
                    ticking = false;
                    applyState();
                });
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', applyState);
            if (mobileQuery && typeof mobileQuery.addEventListener === 'function') {
                mobileQuery.addEventListener('change', applyState);
            }
            navbar.addEventListener('click', () => navbar.classList.remove('navbar-compact'));

            window.__navbarCompactionInit = true;
            applyState();
        })();
    </script>
</body>
</html>




