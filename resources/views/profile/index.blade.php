@extends('layouts.app')

@section('title', 'Profile - Wasfah')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Support\ImageUploadConstraints;
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            @if (session('success'))
                <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                    <i class="fas fa-check-circle ml-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <i class="fas fa-exclamation-triangle ml-2"></i>
                    There were some issues. Please review the highlighted fields.
                </div>
            @endif

            @include('profile.partials.hero')

            <div class="mt-6">
                @include('profile.partials.nav', ['active' => 'overview'])
            </div>

            <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                @php
    $limitedBookings = $bookedWorkshops->take(3);
    $totalBookings = $bookedWorkshops->count();
    $statusLabels = [
        'pending' => 'Pending review',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ];
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'confirmed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $statusDecorations = [
        'pending' => [
            'wrapper' => 'border-yellow-100 bg-yellow-50',
            'bar' => 'bg-yellow-200',
            'dot' => 'bg-yellow-500',
        ],
        'confirmed' => [
            'wrapper' => 'border-green-100 bg-green-50',
            'bar' => 'bg-green-200',
            'dot' => 'bg-green-500',
        ],
        'cancelled' => [
            'wrapper' => 'border-red-100 bg-red-50',
            'bar' => 'bg-red-200',
            'dot' => 'bg-red-500',
        ],
        'default' => [
            'wrapper' => 'border-gray-100 bg-white',
            'bar' => 'bg-gray-200',
            'dot' => 'bg-gray-400',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Booking history</h2>
                        <p class="text-sm text-gray-500">
                            Bookings are sorted from newest to oldest. Use the details button to review or manage each one.
                        </p>
                    </div>
                    <div class="flex flex-col items-start gap-2 text-sm text-gray-500 md:items-end">
                        @if ($totalBookings > 0)
                            <span class="flex flex-wrap gap-1 whitespace-nowrap">
                                <span>Showing</span>
                                <span>1 - {{ $limitedBookings->count() }}</span>
                                <span>of total</span>
                                <span>{{ $totalBookings }}</span>
                            </span>
                        @endif
                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-3 py-1 font-semibold text-orange-600 hover:border-orange-300 hover:text-orange-700">
                            <i class="fas fa-arrow-left text-xs"></i>
                            View all bookings
                        </a>
                    </div>
</div>

@if ($totalBookings > 0)
    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($limitedBookings as $booking)
            @php
                $workshop = $booking->workshop;
                $start = optional($workshop?->start_date);
                $status = $booking->status;
                $decor = $statusDecorations[$status] ?? $statusDecorations['default'];
            @endphp
            <article class="relative flex h-full flex-col gap-4 rounded-3xl border {{ $decor['wrapper'] }} p-5 shadow-sm">
                <span class="pointer-events-none absolute inset-x-6 -top-1 h-1 rounded-full {{ $decor['bar'] }}"></span>
                <div class="flex items-start justify-between pt-2">
                    <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500">
                        Workshop
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-600' }}">
                        <span class="h-2 w-2 rounded-full {{ $decor['dot'] }}"></span>
                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $workshop?->title ?? 'Workshop without a title' }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Booking ID: {{ $booking->public_code ?? $booking->id }}
                    </p>
                </div>
                <div class="grid gap-3 text-sm text-gray-600 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 shadow-inner">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Date
                        </span>
                        <p class="mt-1 font-semibold text-gray-800">
                            {{ $start ? $start->locale(app()->getLocale())->translatedFormat('d F Y • h:i a') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 shadow-inner">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Format
                        </span>
                        <p class="mt-1 font-semibold text-gray-800">
                            {{ $workshop?->is_online ? 'Online' : 'In person' }}
                        </p>
                    </div>
                </div>
                <div class="mt-auto flex flex-wrap gap-3 pt-2 text-sm font-semibold">
                    <a href="{{ route('bookings.show', $booking) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-4 py-2 text-orange-600 transition hover:border-orange-300 hover:text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-1 focus:ring-offset-white">
                        <i class="fas fa-circle-info text-xs"></i>
                        Details
                    </a>
                    @if ($status === 'confirmed' && $workshop?->is_online)
                        <a href="{{ $booking->secure_join_url }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-4 py-2 text-white shadow-sm transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-1 focus:ring-offset-white">
                            <i class="fas fa-door-open text-xs"></i>
                            Enter workshop room
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="mt-6 rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-gray-500">
        You haven't booked a workshop yet. Explore the available sessions and reserve your first seat.
        <div class="mt-4">
                            <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-orange-600">
                                <i class="fas fa-search text-xs"></i>
                                Browse workshops now
                            </a>
                        </div>
                    </div>
                @endif
            </section>

            <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Saved recipes</span>
                        <i class="fas fa-bookmark text-orange-500"></i>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['saved_recipes_count']) }}
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ $stats['saved_recipes_count'] > 0 ? 'Your personal recipe library grows every day.' : 'Start saving favorite recipes for quick access.' }}
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('saved.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            View saved recipes
                        </a>
                    </div>
                </article>

                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Recipes you tried</span>
                        <i class="fas fa-utensils text-orange-500"></i>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['made_recipes_count']) }}
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ $stats['made_recipes_count'] > 0 ? 'Your feedback helps the rest of the community.' : 'Share the first Wasfah recipe you make.' }}
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('saved.index') }}#made" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            Tasting archive
                        </a>
                    </div>
                </article>

                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Upcoming workshops</span>
                        <i class="fas fa-calendar-alt text-orange-500"></i>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['upcoming_workshops_count']) }}
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ $stats['upcoming_workshops_count'] > 0 ? 'We are ready for your next sessions.' : 'No workshops scheduled. Browse what is available now.' }}
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('workshops') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            Explore available workshops
                        </a>
                    </div>
                </article>

                <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Engagement score</span>
                        <i class="fas fa-star text-orange-500"></i>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold text-gray-800">
                        {{ number_format($stats['engagement_score']) }}
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Your score increases when you save recipes, try them, and attend workshops. Stay active to unlock upcoming rewards.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('profile.statistics') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            Engagement details
                        </a>
                    </div>
                </article>
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-3">
                <article class="lg:col-span-2 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">Latest activity</h2>
                        <a href="{{ route('profile.activity') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                            View all activity
                        </a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @php
                            $latestActivities = $activityFeed->take(4);
                        @endphp
                        @forelse ($latestActivities as $activity)
                            @php
                                $timestamp = $activity['timestamp'] instanceof Carbon
                                    ? $activity['timestamp']
                                    : Carbon::parse($activity['timestamp']);
                            @endphp
                            <div class="flex flex-col gap-2 rounded-2xl border border-orange-100 bg-orange-50/60 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-orange-600">
                                        @switch($activity['type'])
                                            @case('saved_recipe')
                                                <i class="fas fa-bookmark ml-1"></i>
                                                Saved a recipe
                                                @break
                                            @case('made_recipe')
                                                <i class="fas fa-check-circle ml-1"></i>
                                                Marked a recipe as made
                                                @break
                                            @case('workshop_booking')
                                                <i class="fas fa-calendar-check ml-1"></i>
                                                Booked a workshop
                                                @break
                                            @default
                                                New activity
                                        @endswitch
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $timestamp->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="text-sm font-medium text-gray-800">
                                    {{ $activity['title'] }}
                                </div>
                                @if (!empty($activity['meta']['category']))
                                    <span class="text-xs text-gray-500 flex flex-wrap items-center gap-1">
                                        <span>Category:</span>
                                        <span>{{ $activity['meta']['category'] }}</span>
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                                No activity recorded yet. Once you save a recipe or book a workshop it will appear here.
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-800">Upcoming workshop</h2>
                    @if ($nextWorkshop)
                        @php
                            $workshopModel = optional($nextWorkshop)->workshop;
                            $workshopStart = optional($workshopModel?->start_date);
                        @endphp
                        <div class="mt-4 rounded-2xl border border-orange-100 bg-orange-50/50 p-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ $workshopModel?->title ?? 'Workshop without a title' }}
                            </h3>
                            @if ($workshopStart)
                                <p class="mt-2 text-sm text-gray-600">
                                    <i class="fas fa-clock ml-2 text-orange-500"></i>
                                    {{ $workshopStart->locale('ar')->translatedFormat('d F Y • h:i a') }}
                                </p>
                            @endif
                            <p class="mt-2 text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt ml-2 text-orange-500"></i>
                                {{ $workshopModel?->is_online ? 'Online via Google Meet' : ($workshopModel?->location ?? 'Location will be announced soon') }}
                            </p>
                            <a href="{{ route('bookings.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-orange-600 hover:text-orange-700">
                                Booking details
                                <i class="fas fa-arrow-left text-xs"></i>
                            </a>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-gray-200 p-5 text-center text-gray-500">
                            No upcoming workshops for now. Explore the current sessions and book your next seat.
                            <div class="mt-4">
                                <a href="{{ route('workshops') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-orange-600">
                                    <i class="fas fa-search text-xs"></i>
                                    Explore workshops
                                </a>
                            </div>
                        </div>
                    @endif
                </article>
            </section>

            <section class="mt-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Recently saved recipes</h2>
                    <a href="{{ route('saved.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                        Manage all saved recipes
                    </a>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @php
                        $savedPreview = $savedRecipes->take(3);
                    @endphp
                    @forelse ($savedPreview as $recipe)
                        <article class="rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="block">
                                <div class="aspect-w-16 aspect-h-10 overflow-hidden rounded-t-2xl bg-gray-100">
                                    <img
                                        src="{{ $recipe->image_url ?? $recipe->image ?? asset('image/brownies.webp') }}"
                                        alt="{{ $recipe->title }}"
                                        class="h-full w-full object-cover" loading="lazy"
                                    >
                                </div>
                                <div class="p-4 space-y-2">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $recipe->title }}</h3>
                                    @if ($recipe->category?->name)
                                        <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-600">
                                            {{ $recipe->category->name }}
                                        </span>
                                    @endif
                                        <p class="text-xs text-gray-500 flex flex-wrap items-center gap-1">
                                            <span>Added on</span>
                                            <span>{{ optional($recipe->userInteraction->updated_at ?? $recipe->userInteraction->created_at)->diffForHumans() }}</span>
                                        </p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-gray-500">
                            You have not saved a recipe yet. Browse our collection and start building your list.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-800">Your achievements</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Track your progress and keep engaging to reach higher levels.
                </p>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach ($achievements as $achievement)
                        @php
                            $progress = $achievement['progress'] ?? 0;
                        @endphp
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/50 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600">
                                    <i class="fas fa-{{ $achievement['icon'] }} text-sm"></i>
                                    {{ $achievement['title'] }}
                                </span>
                                <span class="text-xs uppercase text-gray-500">
                                    {{ $achievement['current_level'] }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-gray-600 flex flex-wrap gap-1">
                                @if (!empty($achievement['description_prefix']) || !empty($achievement['description_suffix']))
                                    @if (!empty($achievement['description_prefix']))
                                        <span>{{ $achievement['description_prefix'] }}</span>
                                    @endif
                                    <span>{{ number_format($achievement['count']) }}</span>
                                    @if (!empty($achievement['description_suffix']))
                                        <span>{{ $achievement['description_suffix'] }}</span>
                                    @endif
                                @elseif (!empty($achievement['description']))
                                    <span>{{ $achievement['description'] }}</span>
                                @endif
                            </p>
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>Progress</span>
                                    @if (!empty($achievement['next_goal']))
                                        <span class="flex flex-wrap items-center gap-1">
                                            <span>Next milestone:</span>
                                            <span>{{ number_format($achievement['next_goal']) }}</span>
                                        </span>
                                    @else
                                        <span>Highest level</span>
                                    @endif
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white shadow-inner">
                                    <div class="h-full rounded-full bg-orange-500" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="mt-2 text-sm font-semibold text-gray-800">
                                    {{ number_format($achievement['count']) }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($user->isChef() && $chefOverview)
                <section class="mt-8 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Chef performance snapshot</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Metrics that help you monitor how your recipes and workshops perform.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $chefOverview['dashboard_url'] }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-medium text-orange-600 hover:border-orange-300 hover:bg-orange-50">
                                <i class="fas fa-th-large text-xs"></i>
                                Dashboard
                            </a>
                            <a href="{{ $chefOverview['links_url'] }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50">
                                <i class="fas fa-link text-xs"></i>
                                Wasfah Links
                            </a>
                            @if ($chefOverview['public_profile_url'])
                                <a href="{{ $chefOverview['public_profile_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                                    <i class="fas fa-eye text-xs"></i>
                                    View public page
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">Published recipes</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['public_recipes']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">Exclusive recipes</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['exclusive_recipes']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">Total saves</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($chefOverview['total_saves']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                            <p class="text-sm text-gray-500">Average rating</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-800">
                                {{ $chefOverview['average_rating'] ? number_format($chefOverview['average_rating'], 1) : '—' }}
                            </p>
                        </div>
                    </div>
                    @if (!empty($chefOverview['popular_recipes']))
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800">Top performing recipes</h3>
                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                @foreach ($chefOverview['popular_recipes'] as $recipe)
                                    <article class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                                        <h4 class="text-md font-semibold text-gray-800">{{ $recipe->title }}</h4>
                                        <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-600">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-bookmark ml-1 text-orange-500"></i>
                                                <span>{{ number_format($recipe->saved_count) }}</span>
                                                <span>Saved</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-utensils ml-1 text-orange-500"></i>
                                                <span>{{ number_format($recipe->made_count) }}</span>
                                                <span>Made</span>
                                            </span>
                                            <span><i class="fas fa-star ml-1 text-orange-500"></i> {{ number_format($recipe->interactions_avg_rating ?? 0, 1) }} ({{ number_format($recipe->rating_count ?? 0) }})</span>
                                        </div>
                                        <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-orange-600 hover:text-orange-700">
                                            View recipe
                                            <i class="fas fa-arrow-left text-xs"></i>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            <section id="profile-settings" class="mt-10 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Edit profile</h2>
                        <p class="text-sm text-gray-500">
                            Update your contact information and profile photo with instant saves.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="name" class="text-sm font-semibold text-gray-700">Full name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-800 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
                            required
                        >
                        @error('name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-semibold text-gray-700">Mobile number</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-800 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
                            placeholder="+966..."
                        >
                        @error('phone')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="timezone" class="text-sm font-semibold text-gray-700">
                            Preferred timezone
                        </label>
                        <select
                            id="timezone"
                            name="timezone"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-800 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
                            @if($user->role === 'chef') required @endif
                        >
                            @foreach ($timezoneOptions as $timezoneValue => $timezoneLabel)
                                <option value="{{ $timezoneValue }}" @selected(old('timezone', $preferredTimezone) === $timezoneValue)>
                                    {{ $timezoneLabel }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">
                            We will convert your workshop schedules from this timezone.
                            @if($detectedTimezone)
                                Detected on this device: <span class="font-medium">{{ $detectedTimezone }}</span>.
                            @endif
                        </p>
                        @error('timezone')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Google Meet email</label>
                        <div class="flex items-start gap-3 rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $user->google_email ?? config('services.google_meet.organizer_email') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    This email is managed by the support team to keep Google Meet access consistent. Contact support if it needs an update.
                                </p>
                            </div>
                        </div>
                    </div>

                    @php
                        $profileAvatarUrl = null;
                        if ($user->avatar) {
                            $profileAvatarUrl = Str::startsWith($user->avatar, ['http://', 'https://'])
                                ? $user->avatar
                                : Storage::disk('public')->url($user->avatar);
                        }
                    @endphp

                    <div class="space-y-2 md:col-span-2">
                        <label for="avatar" class="text-sm font-semibold text-gray-700">Profile photo</label>
                        <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm text-gray-600 md:flex-row md:items-center">
                            <div class="flex items-center gap-4">
                                <div class="h-20 w-20 overflow-hidden rounded-2xl bg-white text-xl font-semibold text-gray-500 shadow-inner ring-1 ring-gray-200 flex items-center justify-center">
                                    @if ($profileAvatarUrl)
                                        <img src="{{ $profileAvatarUrl }}" alt="Current profile photo" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        {{ mb_substr($user->name, 0, 1) }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input
                                    type="file"
                                    name="avatar"
                                    id="avatar"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-orange-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-600 hover:file:bg-orange-100"
                                >
                                <p class="text-xs text-gray-500">
                                    Upload a clear square image (up to {{ ImageUploadConstraints::maxMegabytes() }} MB, {{ ImageUploadConstraints::allowedExtensionsList() }}). Changes sync instantly across your public chef profile.
                                </p>
                                @error('avatar')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-orange-600"
                        >
                            <i class="fas fa-save text-xs"></i>
                            Save changes
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection



