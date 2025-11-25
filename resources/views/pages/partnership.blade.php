@extends('layouts.app')

@section('title', __('partnership.title'))

@section('content')
@php
    $hero = \Illuminate\Support\Facades\Lang::get('partnership.hero');
    $stats = \Illuminate\Support\Facades\Lang::get('partnership.stats');
    $intro = \Illuminate\Support\Facades\Lang::get('partnership.intro');
    $pillars = \Illuminate\Support\Facades\Lang::get('partnership.pillars');
    $cta = \Illuminate\Support\Facades\Lang::get('partnership.cta');
    $guidance = \Illuminate\Support\Facades\Lang::get('partnership.guidance');
    $form = \Illuminate\Support\Facades\Lang::get('partnership.form');
    $footerCta = \Illuminate\Support\Facades\Lang::get('partnership.footer_cta');
@endphp
<div class="relative bg-gradient-to-b from-orange-50 via-white to-white overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -left-32 top-6 w-72 h-72 bg-orange-200 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute right-0 bottom-0 w-96 h-96 bg-rose-200 rounded-full blur-3xl opacity-40 translate-x-1/2 translate-y-1/3"></div>
        <div class="absolute left-1/2 top-1/3 -translate-x-1/2 w-96 h-96 bg-white/40 border border-orange-100 rounded-full blur-2xl"></div>
    </div>
    <div class="relative min-h-screen container mx-auto px-4 py-12 space-y-12 z-10">
        <!-- Hero -->
        <div class="relative bg-gradient-to-r from-orange-500 via-orange-600 to-rose-500 text-white rounded-[2.5rem] p-10 md:p-16 text-center shadow-[0_40px_80px_-40px_rgba(249,115,22,0.9)] overflow-hidden">
            <div class="absolute inset-x-16 inset-y-10 bg-white/10 blur-3xl rounded-[3rem]"></div>
            <div class="relative space-y-6">
                <div class="inline-flex items-center gap-2 bg-white/15 border border-white/30 text-white px-6 py-2 rounded-full text-sm tracking-wide backdrop-blur">
                    <i class="fas fa-fire text-lg"></i>
                    <span>{{ $hero['badge'] }}</span>
                </div>
                <div class="text-5xl">
                    <i class="fas fa-handshake text-white"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black leading-snug">
                    {{ $hero['title'] }}
                    <span class="block text-2xl md:text-3xl font-semibold text-orange-100 mt-2">{{ $hero['subtitle'] }}</span>
                </h1>
                <p class="text-lg md:text-xl max-w-4xl mx-auto text-white/90">
                    {{ $hero['description'] }}
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-3xl mx-auto mt-10 text-sm font-semibold">
                    @foreach ($hero['chips'] as $chip)
                        <div class="bg-white/15 rounded-2xl py-4 px-6 border border-white/20">{{ $chip }}</div>
                    @endforeach
                </div>
                <div class="mt-10 flex flex-wrap gap-4 justify-center">
                    <a href="#partner-form" class="bg-white text-orange-600 font-bold px-10 py-3 rounded-full shadow-2xl hover:-translate-y-0.5 hover:shadow-[0_20px_40px_rgba(255,255,255,0.35)] transition">{{ $hero['primary_cta'] }}</a>
                    <a href="#benefits" class="border border-white/60 text-white font-bold px-10 py-3 rounded-full hover:bg-white/10 transition">{{ $hero['secondary_cta'] }}</a>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-white/20 rounded-full blur-2xl opacity-70"></div>
        </div>

        <!-- Stats -->
        <section id="benefits" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($stats as $stat)
                <div class="group bg-white/80 backdrop-blur rounded-3xl shadow-xl p-8 text-center border border-orange-100 hover:-translate-y-1 transition">
                    <div class="text-4xl mb-3 text-orange-500">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                    <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-rose-500 mb-2">{{ $stat['value'] }}</div>
                    <p class="text-gray-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </section>

        <div class="text-center space-y-3">
            <span class="inline-flex items-center px-4 py-1 rounded-full bg-orange-100 text-orange-700 font-semibold text-sm">{{ $intro['badge'] }}</span>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900">{{ $intro['heading'] }}</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">{{ $intro['description'] }}</p>
        </div>

        <!-- Partner Features -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @foreach ($pillars as $pillar)
                <div class="group relative bg-white rounded-3xl shadow-2xl p-8 border border-orange-100 overflow-hidden hover:-translate-y-2 transition">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-white opacity-0 group-hover:opacity-100 transition"></div>
                    <div class="relative space-y-6">
                        <div class="text-5xl text-orange-500">
                            <i class="{{ $pillar['icon'] }}"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold mb-2">{{ $pillar['title'] }}</h2>
                            <p class="text-gray-600 leading-relaxed">
                                {{ $pillar['description'] }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-sm">
                            @foreach ($pillar['tags'] as $tag)
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <ul class="space-y-3 text-gray-700">
                            @foreach ($pillar['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- CTA -->
        <section class="relative bg-white rounded-3xl shadow-[0_25px_70px_-30px_rgba(249,115,22,0.7)] p-10 space-y-8 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-white opacity-60"></div>
            <div class="relative space-y-4 text-center">
                <div class="text-4xl text-orange-500">
                    <i class="fas fa-rocket"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">{{ $cta['heading'] }}</h2>
                <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                    {{ $cta['description'] }}
                </p>
            </div>
            <div class="relative grid grid-cols-1 md:grid-cols-3 gap-6 text-right">
                @foreach ($cta['steps'] as $index => $step)
                    <div class="relative bg-white rounded-2xl border border-orange-100 p-6 shadow-lg">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-rose-500 text-white font-black mb-4">{{ $index + 1 }}</div>
                        <h3 class="text-xl font-bold mb-2">{{ $step['title'] }}</h3>
                        <p class="text-gray-600">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="relative text-center">
                <a href="#partner-form" class="inline-flex items-center justify-center bg-orange-500 text-white font-bold px-12 py-3 rounded-full shadow-lg hover:bg-orange-600 transition">
                    {{ $cta['button'] }}
                </a>
            </div>
        </section>

        <!-- Guidance -->
        <section class="bg-gradient-to-br from-orange-100 via-white to-white rounded-3xl p-10 shadow-inner border border-orange-100">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $guidance['heading'] }}</h2>
            <p class="text-gray-700 text-lg mb-6">
                {{ $guidance['description'] }}
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-6">
                @foreach ($guidance['bullets'] as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
            <p class="text-gray-800 font-semibold inline-flex items-center gap-2">
                <i class="fas fa-lightbulb text-orange-500"></i>
                <span>{{ $guidance['note'] }}</span>
            </p>
        </section>

        <!-- Form -->
        <section id="partner-form" class="bg-white/95 backdrop-blur rounded-3xl shadow-[0_25px_60px_-35px_rgba(249,115,22,0.9)] p-10 space-y-6 border border-orange-100">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $form['heading'] }}</h2>
                <p class="text-gray-600">{{ $form['description'] }}</p>
            </div>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['first_name']['label'] }}</label>
                    <input type="text" placeholder="{{ $form['fields']['first_name']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['last_name']['label'] }}</label>
                    <input type="text" placeholder="{{ $form['fields']['last_name']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['email']['label'] }}</label>
                    <input type="email" placeholder="{{ $form['fields']['email']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['phone']['label'] }}</label>
                    <input type="text" placeholder="{{ $form['fields']['phone']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['subject']['label'] }}</label>
                    <input type="text" value="{{ $form['fields']['subject']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $form['fields']['message']['label'] }}</label>
                    <textarea rows="5" placeholder="{{ $form['fields']['message']['placeholder'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-orange-500 focus:ring-orange-500 focus:bg-white shadow-sm"></textarea>
                    <p class="text-sm text-gray-500 mt-2">{{ $form['reminder'] }}</p>
                </div>
                <div class="md:col-span-2 flex justify-between items-center flex-wrap gap-4">
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-full shadow-lg transition">{{ $form['submit'] }}</button>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">{{ $form['cta_heading'] }}</p>
                        <p class="text-gray-600">{{ $form['cta_description'] }}</p>
                    </div>
                </div>
            </form>
        </section>

        <!-- Footer CTA -->
        <section class="relative bg-gradient-to-r from-orange-500 via-orange-600 to-rose-500 text-white rounded-3xl p-10 text-center space-y-5 overflow-hidden">
            <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.6), transparent 45%), radial-gradient(circle at 80% 0%, rgba(255,255,255,0.4), transparent 40%);"></div>
            <div class="relative space-y-3">
                <h2 class="text-3xl font-bold">{{ $footerCta['heading'] }}</h2>
                <p class="text-lg text-white/90">{{ $footerCta['description'] }}</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#partner-form" class="bg-white text-orange-600 font-bold px-8 py-3 rounded-full shadow-lg hover:shadow-2xl transition">{{ $footerCta['primary'] }}</a>
                    <a href="#benefits" class="border border-white/60 px-8 py-3 rounded-full font-bold hover:bg-white/10 transition">{{ $footerCta['secondary'] }}</a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

