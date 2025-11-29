@php
    $items = $breadcrumbs ?? \App\Helpers\Breadcrumbs::generate();
    $itemsCount = count($items);
    $inNavbar = (bool) ($inNavbar ?? false);
    $navClasses = $inNavbar
        ? 'bg-white/95 backdrop-blur border-y border-orange-100'
        : 'bg-white border-b border-gray-200';
@endphp

@if ($itemsCount > 0)
    <nav class="{{ $navClasses }}" aria-label="مسار التنقل" itemscope itemtype="https://schema.org/BreadcrumbList">
        <div class="container mx-auto px-4 py-3">
            <ol class="flex flex-wrap items-center text-sm text-gray-500" data-crumb-scroll>
                @foreach ($items as $index => $item)
                    @php
                        $isLast = $index === $itemsCount - 1;
                        $itemUrl = $item['url'] ?? null;
                    @endphp
                    <li class="flex items-center min-w-0" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        @if ($itemUrl && !$isLast)
                            <a href="{{ $itemUrl }}" class="hover:text-orange-500 transition-colors" itemprop="item">
                                <span itemprop="name">{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span
                                class="{{ $isLast ? 'text-gray-700 font-medium truncate block max-w-[11rem] sm:max-w-none' : 'text-gray-500' }}"
                                itemprop="name"
                            >
                                {{ $item['label'] }}
                            </span>
                            <meta itemprop="item" content="{{ $itemUrl ?: url()->current() }}">
                        @endif
                        <meta itemprop="position" content="{{ $index + 1 }}">

                        @if (!$isLast)
                            <span class="mx-2 text-gray-400">/</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>
@endif

@once
    @push('styles')
        <style>
            /* Breadcrumb float animation on scroll */
            [data-crumb-scroll] {
                transition: transform 180ms ease-out;
                will-change: transform;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const crumbList = document.querySelector('[data-crumb-scroll]');
                if (!crumbList || crumbList.dataset.scrollEnhanced) return;

                crumbList.dataset.scrollEnhanced = 'true';
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

                const updateCrumbOffset = () => {
                    if (reduceMotion.matches) {
                        crumbList.style.transform = '';
                        return;
                    }
                    // Creates a gentle up/down drift tied to scroll position
                    const offset = Math.sin(window.scrollY / 120) * 8;
                    crumbList.style.transform = `translateY(${offset}px)`;
                };

                updateCrumbOffset();
                window.addEventListener('scroll', updateCrumbOffset, { passive: true });
            })();
        </script>
    @endpush
@endonce

