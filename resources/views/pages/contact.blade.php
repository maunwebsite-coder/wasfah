@extends('layouts.app')

@section('title', __('contact.meta.title'))

@php
    $hero = __('contact.hero');
    $form = __('contact.form');
    $guidanceCards = __('contact.guidance_cards');
    $faq = __('contact.faq');
    $contactInfo = __('contact.contact_info');
    $social = __('contact.social');
    $promo = __('contact.promo');
    $responseNotice = $form['response_notice'] ?? null;
@endphp

@section('content')
<div class="bg-gradient-to-b from-orange-50 via-white to-white">
    <div class="container mx-auto px-4 py-10 lg:py-16 space-y-10">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-orange-600 via-orange-500 to-amber-500 text-white shadow-2xl">
            <div class="absolute inset-y-0 -left-20 w-52 bg-white/10 blur-3xl rounded-full hidden lg:block"></div>
            <div class="grid lg:grid-cols-2 gap-10 p-8 md:p-12">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.4em] text-white/70 mb-6">
                        <span class="w-8 h-px bg-white/60"></span>
                        <span>{{ $hero['badge'] }}</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ $hero['title'] }}</h1>
                    <p class="text-lg md:text-xl text-white/90 leading-relaxed max-w-2xl">
                        {{ $hero['description'] }}
                    </p>
                    <div class="flex flex-wrap gap-3 text-sm mt-8">
                        @foreach ($hero['chips'] as $chip)
                            <span class="px-4 py-2 rounded-full border border-white/50 bg-white/10">{{ $chip }}</span>
                        @endforeach
                    </div>
                    <dl class="mt-10 grid sm:grid-cols-3 gap-4 text-sm">
                        @foreach ($hero['stats'] as $stat)
                            <div class="bg-white/10 rounded-2xl p-4">
                                <dt class="text-white/70 mb-1">{{ $stat['label'] }}</dt>
                                <dd class="text-2xl font-bold">{{ $stat['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>

        <div class="grid xl:grid-cols-[minmax(0,1.9fr)_minmax(0,1.1fr)] gap-10">
            <div class="space-y-8">
                <div id="form" class="bg-white rounded-3xl shadow-xl border border-orange-100 p-6 md:p-10">
                    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $form['title'] }}</h2>
                            <p class="text-gray-500">{{ $form['description'] }}</p>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-orange-600 font-semibold">
                            <i class="far fa-envelope-open-text"></i>
                            <span>{{ $form['badge'] }}</span>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl mb-6 flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl mb-6 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form id="contact-form" method="POST" action="{{ route('contact.send') }}" class="space-y-6">
                        @csrf
                        @include('pages.partials.contact-form-fields', [
                            'defaultSubject' => 'general',
                            'source' => 'contact-page',
                            'formCopy' => $form,
                        ])

                        @if($responseNotice)
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 pt-2 border-t border-gray-100">
                                <i class="far fa-clock text-orange-500"></i>
                                <p>{{ $responseNotice }}</p>
                            </div>
                        @endif
                    </form>
                </div>

                @php
                    $guidanceCardStyles = [
                        ['container' => 'bg-gradient-to-br from-orange-100 to-orange-50 border border-orange-100', 'label' => 'text-orange-600'],
                        ['container' => 'bg-gradient-to-br from-purple-100 to-purple-50 border border-purple-100', 'label' => 'text-purple-600'],
                        ['container' => 'bg-gradient-to-br from-emerald-100 to-emerald-50 border border-emerald-100', 'label' => 'text-emerald-600'],
                    ];
                @endphp

                <div class="grid md:grid-cols-3 gap-4">
                    @foreach ($guidanceCards as $index => $card)
                        @php
                            $style = $guidanceCardStyles[$index] ?? $guidanceCardStyles[0];
                        @endphp
                        <div class="rounded-2xl p-5 {{ $style['container'] }}">
                            <p class="text-xs font-semibold mb-2 {{ $style['label'] }}">{{ $card['title'] }}</p>
                            <p class="text-gray-700 text-sm">{{ $card['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $faq['title'] }}</h2>
                    <div class="space-y-5">
                        @foreach ($faq['items'] as $index => $item)
                            <div class="group {{ $index > 0 ? 'pt-5 border-t border-dashed border-gray-200' : '' }}">
                                <div class="flex items-center justify-between cursor-pointer">
                                    <h3 class="font-semibold text-gray-800">{{ $item['question'] }}</h3>
                                    <i class="fas fa-angle-down text-orange-500 group-hover:translate-y-1 transition"></i>
                                </div>
                                <p class="text-gray-600 text-sm mt-2">{{ $item['answer'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-xl">
                    <h2 class="text-2xl font-bold mb-4">{{ $contactInfo['title'] }}</h2>
                    <p class="text-white/80 mb-6">{{ $contactInfo['description'] }}</p>
                    <div class="space-y-5 text-sm">
                        @php
                            $contactIcons = [
                                'fas fa-map-marker-alt text-orange-300',
                                'fas fa-headset text-purple-200',
                                'fas fa-comments text-emerald-200',
                                'fas fa-envelope text-sky-200',
                            ];
                        @endphp
                        @foreach ($contactInfo['items'] as $index => $info)
                            @php
                                $icon = $contactIcons[$index] ?? 'fas fa-circle text-white/70';
                            @endphp
                            <div class="flex items-start gap-4">
                                <div class="bg-white/15 p-3 rounded-2xl">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">{{ $info['title'] }}</h3>
                                    <p class="text-white/80">{{ $info['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $social['title'] }}</h2>
                    <p class="text-gray-600 mb-6 text-sm">{{ $social['description'] }}</p>
                    <div class="space-y-4">
                        @php
                            $channelStyles = [
                                ['wrapper' => 'bg-gradient-to-r from-pink-500 to-purple-600 text-white', 'icon' => 'fab fa-instagram text-2xl'],
                                ['wrapper' => 'bg-red-600 text-white', 'icon' => 'fab fa-youtube text-2xl'],
                            ];
                            $defaultChannelStyle = ['wrapper' => 'bg-slate-900 text-white', 'icon' => 'fas fa-share-alt text-2xl'];
                        @endphp
                        @foreach ($social['channels'] as $index => $channel)
                            @php
                                $style = $channelStyles[$index] ?? $defaultChannelStyle;
                            @endphp
                            <a
                                href="{{ $channel['url'] }}"
                                target="_blank"
                                rel="noreferrer"
                                class="flex items-center justify-between rounded-2xl p-4 {{ $style['wrapper'] }} shadow-lg hover:-translate-y-1 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <i class="{{ $style['icon'] }}"></i>
                                    <div>
                                        <h3 class="font-semibold text-lg">{{ $channel['title'] }}</h3>
                                        <p class="text-sm text-white/80">{{ $channel['handle'] }}</p>
                                    </div>
                                </div>
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fas fa-ribbon text-orange-500 text-2xl"></i>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $promo['title'] }}</h3>
                            <p class="text-gray-600 text-sm">{{ $promo['description'] }}</p>
                        </div>
                    </div>
                    <div class="border-t border-dashed border-gray-200 pt-4">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-[0.3em] mb-3">{{ $promo['discover'] }}</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                            @foreach ($promo['links'] as $link)
                                <span>{{ $link }}</span>
                            @endforeach
                        </div>
                        <div class="mt-4 text-xs text-gray-500 space-y-2">
                            @foreach ($promo['footnotes'] as $note)
                                <p>{{ $note }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

