@php
    $items = $breadcrumbs ?? \App\Helpers\Breadcrumbs::generate();
    $itemsCount = count($items);
@endphp

@if ($itemsCount > 0)
    <nav class="bg-white border-b border-gray-200" aria-label="مسار التنقل" itemscope itemtype="https://schema.org/BreadcrumbList">
        <div class="container mx-auto px-4 py-3">
            <ol class="flex flex-wrap items-center text-sm text-gray-500">
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


