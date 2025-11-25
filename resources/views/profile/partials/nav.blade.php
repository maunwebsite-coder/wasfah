@php
    $activeTab = $active ?? 'overview';
    $navItems = [
        [
            'key' => 'overview',
            'label' => 'نظرة عامة',
            'route' => route('profile'),
            'icon' => 'fas fa-home',
        ],
        [
            'key' => 'statistics',
            'label' => 'الإحصاءات',
            'route' => route('profile.statistics'),
            'icon' => 'fas fa-chart-bar',
        ],
        [
            'key' => 'activity',
            'label' => 'النشاط',
            'route' => route('profile.activity'),
            'icon' => 'fas fa-stream',
        ],
    ];
@endphp

<nav class="bg-white border border-gray-100 rounded-2xl shadow-sm px-4 md:px-6 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 md:gap-3">
        @foreach ($navItems as $item)
            @php
                $isActive = $activeTab === $item['key'];
            @endphp
            <a
                href="{{ $item['route'] }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition {{ $isActive ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}"
            >
                <i class="{{ $item['icon'] }} text-sm"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="text-center md:text-left text-sm text-gray-500">
        @switch($activeTab)
            @case('statistics')
                <span class="flex flex-wrap items-center justify-center gap-1 md:justify-start">
                    <span>آخر تحديث:</span>
                    <span>{{ now()->format('d/m/Y h:i a') }}</span>
                </span>
                @break
            @case('activity')
                يتم تحديث الأنشطة عند حدوثها مباشرة
                @break
            @default
                اختر القسم الذي ترغب في استعراضه
        @endswitch
    </div>
</nav>

