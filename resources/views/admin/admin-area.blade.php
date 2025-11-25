@extends('layouts.app')

@section('title', 'Admin Area')

@section('content')
<div class="min-h-screen bg-gray-50/50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl blur opacity-30 group-hover:opacity-60 transition duration-200"></div>
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white border border-gray-100 shadow-sm">
                            <i class="fas fa-crown text-2xl text-transparent bg-clip-text bg-gradient-to-br from-orange-500 to-amber-600"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Admin Area</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage your platform, recipes, and workshops.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm">
                        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8 space-y-10">
        
        <!-- Attention Section -->
        <section>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-8 w-1 bg-orange-500 rounded-full"></div>
                <h2 class="text-lg font-bold text-gray-900">Needs Attention</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($attentionItems as $item)
                    @php
                        $format = $item['format'] ?? 'number';
                        $rawValue = $item['value'] ?? 0;
                        $isEmpty = (float) $rawValue === 0.0;
                        $destination = $item['route'] ?? null;
                        $params = $item['route_params'] ?? [];
                        $url = $item['url'] ?? ($destination ? route($destination, $params) : '#');

                        $displayValue = $format === 'currency'
                            ? number_format((float) $rawValue, 2) . ' AED'
                            : number_format((int) $rawValue);
                    @endphp
                    <a href="{{ $url }}" class="group relative overflow-hidden bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 {{ $isEmpty ? 'opacity-75' : '' }}">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas {{ $item['icon'] }} text-6xl text-gray-900"></i>
                        </div>
                        
                        <div class="flex items-start justify-between relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $isEmpty ? 'bg-gray-100 text-gray-400' : 'bg-orange-50 text-orange-600' }} group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas {{ $item['icon'] }} text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ $item['label'] }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $displayValue }}</p>
                                </div>
                            </div>
                            @if(!$isEmpty)
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-50 text-orange-600 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-4 flex items-center gap-2 text-sm">
                            @if($isEmpty)
                                <span class="inline-flex items-center gap-1.5 text-gray-400 bg-gray-50 px-2.5 py-1 rounded-lg">
                                    <i class="fas fa-check-circle text-xs"></i>
                                    {{ $item['empty_state'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2.5 py-1 rounded-lg font-medium group-hover:bg-orange-100 transition-colors">
                                    {{ $item['cta'] }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Quick Glance Metrics -->
        <section>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-8 w-1 bg-teal-500 rounded-full"></div>
                <h2 class="text-lg font-bold text-gray-900">Quick Glance</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                @foreach($metrics as $metric)
                    @php
                        $metricUrl = $metric['url'] ?? route($metric['route'], $metric['route_params'] ?? []);
                    @endphp
                    <a href="{{ $metricUrl }}" class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-teal-100 transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-50 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300">
                                <i class="fas {{ $metric['icon'] }}"></i>
                            </div>
                            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md group-hover:bg-teal-50 group-hover:text-teal-600 transition-colors">
                                View
                            </span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ number_format($metric['value']) }}</h3>
                            <p class="text-sm font-medium text-gray-600 mt-1">{{ $metric['label'] }}</p>
                            @if(!empty($metric['hint']))
                                <p class="text-xs text-gray-400 mt-2 line-clamp-1">{{ $metric['hint'] }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Content Panels -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Recipes -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-100 text-orange-600 rounded-lg">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Latest Recipes</h3>
                            <p class="text-xs text-gray-500">Recently added content</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.recipes.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:bg-orange-50 px-3 py-1.5 rounded-lg transition-colors">
                        View All
                    </a>
                </div>
                <div class="divide-y divide-gray-50 flex-1">
                    @forelse($recentRecipes as $recipe)
                        <div class="p-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.recipes.edit', $recipe) }}" class="block text-sm font-semibold text-gray-900 truncate group-hover:text-orange-600 transition-colors">
                                        {{ $recipe->title }}
                                    </a>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="flex items-center text-xs text-gray-400">
                                            <i class="far fa-clock mr-1.5"></i>
                                            {{ $recipe->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                @php
                                    $statusKey = $recipe->status ?? 'default';
                                    $badgeClasses = match($statusKey) {
                                        \App\Models\Recipe::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-100',
                                        \App\Models\Recipe::STATUS_APPROVED => 'bg-green-50 text-green-700 border-green-100',
                                        \App\Models\Recipe::STATUS_REJECTED => 'bg-red-50 text-red-700 border-red-100',
                                        \App\Models\Recipe::STATUS_DRAFT => 'bg-gray-100 text-gray-600 border-gray-200',
                                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badgeClasses }}">
                                    {{ $recipeStatusLabels[$statusKey] ?? 'Unspecified' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-300 mb-3">
                                <i class="fas fa-receipt text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">No recent recipes found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Workshops -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Upcoming Workshops</h3>
                            <p class="text-xs text-gray-500">Scheduled sessions</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.workshops.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">
                        View All
                    </a>
                </div>
                <div class="divide-y divide-gray-50 flex-1">
                    @forelse($upcomingWorkshops as $workshop)
                        <div class="p-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.workshops.edit', $workshop) }}" class="block text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                        {{ $workshop->title }}
                                    </a>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="flex items-center text-xs text-gray-500">
                                            <i class="far fa-calendar mr-1.5"></i>
                                            {{ optional($workshop->start_date)->translatedFormat('d M, h:i A') ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $workshop->is_online ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-purple-50 text-purple-700 border-purple-100' }}">
                                    <i class="fas {{ $workshop->is_online ? 'fa-video' : 'fa-map-marker-alt' }} mr-1.5 text-[10px]"></i>
                                    {{ $workshop->is_online ? 'Online' : 'In Person' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-300 mb-3">
                                <i class="fas fa-calendar-times text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">No upcoming workshops.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Management Sections -->
        <section>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-8 w-1 bg-indigo-500 rounded-full"></div>
                <h2 class="text-lg font-bold text-gray-900">Platform Management</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($managementSections as $section)
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                        <div class="flex items-start gap-4 mb-5">
                            <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-gray-800 to-gray-900 text-white shadow-lg shadow-gray-200">
                                <i class="fas {{ $section['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $section['title'] }}</h3>
                                <p class="text-sm text-gray-500 leading-relaxed mt-1">{{ $section['description'] }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-auto flex flex-wrap gap-2">
                            @foreach($section['items'] as $item)
                                @php
                                    $itemUrl = $item['url'] ?? route($item['route'], $item['params'] ?? []);
                                @endphp
                                <a href="{{ $itemUrl }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-gray-50 border border-gray-200 hover:bg-gray-100 hover:border-gray-300 hover:text-gray-900 transition-all">
                                    {{ $item['label'] }}
                                    <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-800 to-slate-900 p-8 md:p-12 text-center">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10 pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-500 blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-purple-500 blur-3xl"></div>
            </div>

            <div class="relative z-10">
                <h2 class="text-2xl font-bold text-white mb-2">Quick Actions</h2>
                <p class="text-slate-300 mb-8 max-w-xl mx-auto">Frequently used shortcuts to help you manage content faster.</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    @foreach($quickActions as $action)
                        <a href="{{ route($action['route'], $action['params'] ?? []) }}" class="group flex flex-col items-center gap-3 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 hover:scale-105 transition-all duration-200 backdrop-blur-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white group-hover:bg-white group-hover:text-slate-900 transition-colors">
                                <i class="fas {{ $action['icon'] }}"></i>
                            </div>
                            <span class="text-xs font-medium text-slate-200 group-hover:text-white text-center leading-tight">
                                {{ $action['label'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

    </div>
</div>
@endsection







