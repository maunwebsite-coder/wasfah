@extends('layouts.app')

@section('title', 'Ø§Ù„ÙˆØ±Ø´Ø© ØºÙŠØ± Ù…ØªØ§Ø­Ø© - Ù…ÙˆÙ‚Ø¹ ÙˆØµÙØ©')

@section('content')
<div class="bg-gray-50 min-h-screen py-12 md:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-2xl sm:px-10">
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <div class="absolute -left-10 top-10 h-32 w-32 rounded-full bg-gradient-to-r from-amber-300 to-orange-500 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-40 w-40 rounded-full bg-gradient-to-tr from-orange-200 to-pink-400 blur-3xl"></div>
            </div>

            <div class="relative z-10 text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-50 text-3xl">
                    ðŸ˜•
                </div>
                <p class="mb-2 text-sm font-semibold text-orange-600">Ø§Ù„Ø±Ø§Ø¨Ø· Ø§Ù„Ø°ÙŠ Ø­Ø§ÙˆÙ„Øª ÙØªØ­Ù‡</p>
                <p class="mx-auto mb-6 inline-flex items-center justify-center rounded-full bg-gray-100 px-4 py-2 font-mono text-sm text-gray-700">
                    /workshops/{{ $missingSlug }}
                </p>
                <h1 class="mb-4 text-2xl font-bold text-gray-900 sm:text-3xl">Ù„Ù… Ù†Ø¹Ø«Ø± Ø¹Ù„Ù‰ Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø©</h1>
                <p class="mx-auto max-w-2xl text-base text-gray-600 sm:text-lg">
                    Ø±Ø¨Ù…Ø§ ØªØºÙŠØ± Ø§Ø³Ù… Ø§Ù„ÙˆØ±Ø´Ø©ØŒ Ø§Ù†ØªÙ‡Ù‰ ÙˆÙ‚ØªÙ‡Ø§ Ø£Ùˆ Ø£Ù† Ø§Ù„Ø±Ø§Ø¨Ø· ÙŠØ­ØªÙˆÙŠ Ø¹Ù„Ù‰ Ø®Ø·Ø£ Ù…Ø·Ø¨Ø¹ÙŠ. Ù„Ø§ ØªÙ‚Ù„Ù‚ØŒ Ù…Ø§ Ø²Ø§Ù„ Ø¨Ø¥Ù…ÙƒØ§Ù†Ùƒ Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ ÙˆØ±Ø´ Ù…Ø´Ø§Ø¨Ù‡Ø© Ø¨Ø³Ù‡ÙˆÙ„Ø©.
                </p>
            </div>

            <form action="{{ route('workshops.search') }}" method="GET" class="mt-10">
                <label for="workshop-search" class="sr-only">Ø§Ø¨Ø­Ø« Ø¹Ù† ÙˆØ±Ø´Ø©</label>
                <div class="flex flex-col gap-3 md:flex-row">
                    <input
                        id="workshop-search"
                        type="text"
                        name="q"
                        value="{{ old('q', $missingSlug) }}"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-4 text-base shadow-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                        placeholder="Ø¬Ø±Ù‘Ø¨ Ø§Ù„Ø¨Ø­Ø« Ø¹Ù† Ø§Ø³Ù… Ø§Ù„ÙˆØ±Ø´Ø© Ø£Ùˆ Ø§Ù„Ø´ÙŠÙ Ø£Ùˆ Ø§Ù„ØªØµÙ†ÙŠÙ"
                    >
                    <button
                        type="submit"
                        class="flex items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 text-base font-semibold text-white shadow-lg transition hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-200 focus:ring-offset-2"
                    >
                        <i class="fas fa-search ml-2"></i>
                        Ø§Ø¨Ø­Ø« Ø§Ù„Ø¢Ù†
                    </button>
                </div>
            </form>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('workshops') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 text-right transition hover:border-orange-200 hover:bg-orange-50">
                    <div class="mb-2 inline-flex items-center justify-center rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-600">
                        Ø§Ø³ØªØ¹Ø±Ø¶ ÙƒÙ„ Ø§Ù„ÙˆØ±Ø´Ø§Øª
                    </div>
                    <p class="text-base text-gray-600">Ø§ÙØªØ­ ØµÙØ­Ø© Ø§Ù„ÙˆØ±Ø´Ø§Øª Ø§Ù„ÙƒØ§Ù…Ù„Ø© Ù…Ø¹ Ø¥Ù…ÙƒØ§Ù†ÙŠØ§Øª Ø§Ù„ÙÙ„ØªØ±Ø© Ø­Ø³Ø¨ Ø§Ù„Ø³Ø¹Ø±ØŒ Ø§Ù„Ù…ÙƒØ§Ù† ÙˆØ§Ù„Ù…Ø³ØªÙˆÙ‰.</p>
                    <div class="mt-3 flex items-center text-sm font-semibold text-orange-600">
                        Ø§Ù†ØªÙ‚Ù„ Ø§Ù„Ø¢Ù†
                        <i class="fas fa-arrow-left mr-2 transition group-hover:-translate-x-1"></i>
                    </div>
                </a>
                <a href="{{ route('contact') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 text-right transition hover:border-orange-200 hover:bg-orange-50">
                    <div class="mb-2 inline-flex items-center justify-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                        Ù†Ø³Ø§Ø¹Ø¯Ùƒ ÙÙŠ Ø¥ÙŠØ¬Ø§Ø¯Ù‡Ø§
                    </div>
                    <p class="text-base text-gray-600">Ø£Ø®Ø¨Ø± ÙØ±ÙŠÙ‚ ÙˆØµÙØ© Ø¨Ø§Ø³Ù… Ø§Ù„ÙˆØ±Ø´Ø© Ø£Ùˆ Ø§Ù„ØªØ¬Ø±Ø¨Ø© Ø§Ù„ØªÙŠ ØªØ¨Ø­Ø« Ø¹Ù†Ù‡Ø§ ÙˆØ³Ù†Ø±Ø´Ø¯Ùƒ Ù„Ø£Ù‚Ø±Ø¨ Ø®ÙŠØ§Ø±.</p>
                    <div class="mt-3 flex items-center text-sm font-semibold text-gray-700">
                        ØªÙˆØ§ØµÙ„ Ù…Ø¹Ù†Ø§
                        <i class="fas fa-arrow-left mr-2 transition group-hover:-translate-x-1"></i>
                    </div>
                </a>
            </div>

            @if($popularCategories->isNotEmpty())
            <div class="mt-10 rounded-3xl bg-gray-50 p-6">
                <p class="mb-4 text-sm font-semibold text-gray-500">ØªØµÙ†ÙŠÙØ§Øª Ø´Ø§Ø¦Ø¹Ø©</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($popularCategories as $category)
                        <a
                            href="{{ route('workshops', ['category' => $category->category]) }}"
                            class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:text-orange-600"
                        >
                            <span>{{ $category->category }}</span>
                            <span class="text-xs text-gray-400">{{ $category->total }} ÙˆØ±Ø´</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="mt-12">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-orange-600">ÙˆØ±Ø´ Ù…Ù‚ØªØ±Ø­Ø©</p>
                    <h2 class="text-2xl font-bold text-gray-900">Ø¬Ø±Ø¨ ÙˆØ§Ø­Ø¯Ø© Ù…Ù† Ù‡Ø°Ù‡ Ø§Ù„ØªØ¬Ø§Ø±Ø¨ Ø§Ù„Ù‚Ø§Ø¯Ù…Ø©</h2>
                </div>
                <a href="{{ route('workshops') }}" class="hidden text-sm font-semibold text-orange-600 hover:text-orange-700 md:inline-flex md:items-center">
                    Ø´Ø§Ù‡Ø¯ Ø§Ù„Ù…Ø²ÙŠØ¯
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>

            @if($suggestedWorkshops->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($suggestedWorkshops as $suggestedWorkshop)
                    @php
                        $imageUrl = $suggestedWorkshop->image
                            ? Storage::disk('public')->url($suggestedWorkshop->image)
                            : asset('image/wterm.webp');
                        $startsAt = $suggestedWorkshop->start_date
                            ? $suggestedWorkshop->start_date->locale('ar')->translatedFormat('d F Y â€¢ h:i a')
                            : 'Ø³ÙŠØªÙ… ØªØ­Ø¯ÙŠØ¯ Ø§Ù„Ù…ÙˆØ¹Ø¯';
                        $isOnline = $suggestedWorkshop->is_online;
                        $priceLabel = $suggestedWorkshop->price
                            ? ($suggestedWorkshop->formatted_price
                                ?? number_format((float) $suggestedWorkshop->price, 2) . ' ' . ($suggestedWorkshop->currency ?? config('finance.default_currency', 'JOD')))
                            : 'Ù…Ø¬Ø§Ù†Ø§Ù‹';
                    @endphp
                    <a href="{{ route('workshop.show', $suggestedWorkshop->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative">
                            <img src="{{ $imageUrl }}" alt="{{ $suggestedWorkshop->title }}" class="h-48 w-full object-cover" loading="lazy">
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ $isOnline ? 'Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†' : 'Ø­Ø¶ÙˆØ±ÙŠ' }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="mb-2 text-lg font-bold text-gray-900 group-hover:text-orange-600">{{ $suggestedWorkshop->title }}</h3>
                            <p class="mb-4 flex-1 text-sm text-gray-600">
                                {{ \Illuminate\Support\Str::limit($suggestedWorkshop->featured_description ?: $suggestedWorkshop->description, 110) }}
                            </p>
                            <div class="mt-auto space-y-2 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-orange-500"></i>
                                    <span>{{ $startsAt }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-orange-500"></i>
                                    <span>{{ $suggestedWorkshop->instructor }}</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold text-gray-900">
                                    <i class="fas fa-wallet text-orange-500"></i>
                                    <span>{{ $priceLabel }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            @else
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center">
                <p class="text-base text-gray-600">Ù„Ø§ ØªÙˆØ¬Ø¯ ÙˆØ±Ø´ Ù…Ù‚ØªØ±Ø­Ø© Ø­Ø§Ù„ÙŠØ§Ù‹ØŒ Ù„ÙƒÙ† ÙŠÙ…ÙƒÙ†Ùƒ ØªØµÙØ­ ÙƒÙ„ Ø§Ù„ÙˆØ±Ø´Ø§Øª Ø§Ù„Ù…ØªØ§Ø­Ø© Ù…Ù† Ø®Ù„Ø§Ù„ Ø§Ù„Ø²Ø± Ø£Ø¹Ù„Ø§Ù‡.</p>
            </div>
            @endif

            <a href="{{ route('workshops') }}" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl border border-gray-200 px-6 py-4 text-sm font-semibold text-gray-700 transition hover:border-orange-200 hover:bg-orange-50 md:hidden">
                Ø´Ø§Ù‡Ø¯ Ø¬Ù…ÙŠØ¹ Ø§Ù„ÙˆØ±Ø´Ø§Øª
            </a>
        </div>
    </div>
</div>
@endsection




