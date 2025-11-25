@extends('layouts.app')

@section('title', __('advertising.meta.title'))

@section('content')
@php
    $copy = \Illuminate\Support\Facades\Lang::get('advertising');
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 space-y-12">
        <!-- Hero -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl text-white p-12 text-center shadow-2xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ $copy['hero']['title'] }}</h1>
            <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">{{ $copy['hero']['subtitle'] }}</p>
        </div>

        <!-- Why advertise -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ $copy['why']['title'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($copy['why']['items'] as $index => $item)
                    @php
                        $colors = ['orange', 'green', 'blue'];
                        $icons = ['fas fa-users', 'fas fa-chart-line', 'fas fa-mobile-alt'];
                        $color = $colors[$index] ?? 'orange';
                        $icon = $icons[$index] ?? 'fas fa-bullseye';
                    @endphp
                    <div class="text-center">
                        <div class="bg-{{ $color }}-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="{{ $icon }} text-{{ $color }}-600 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $item['title'] }}</h3>
                        <p class="text-gray-600">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Packages -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ $copy['packages']['title'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($copy['packages']['items'] as $index => $package)
                    <div @class([
                        'border-2 rounded-xl p-8 relative',
                        'border-orange-500' => $index === 1,
                        'border-gray-200 hover:border-orange-500 transition-colors' => $index !== 1,
                    ])>
                        @if (! empty($package['popular']))
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                <span class="bg-orange-500 text-white px-4 py-2 rounded-full text-sm font-medium">
                                    {{ $package['popular'] }}
                                </span>
                            </div>
                        @endif
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $package['name'] }}</h3>
                            <div class="text-4xl font-bold text-orange-600 mb-2">{{ $package['price'] }}</div>
                            <div class="text-gray-500">{{ $package['billing'] }}</div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            @foreach ($package['features'] as $feature)
                                <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-medium transition-colors">
                            {{ $copy['packages']['cta'] }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Formats -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ $copy['formats']['title'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($copy['formats']['types'] as $type)
                    <div class="border border-gray-200 rounded-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $type['title'] }}</h3>
                        <div class="bg-gray-100 h-32 rounded-lg flex items-center justify-center mb-4">
                            @if ($type === end($copy['formats']['types']))
                                <i class="fas fa-play-circle text-4xl text-gray-400"></i>
                            @else
                                <span class="text-gray-500">{{ $type['placeholder'] }}</span>
                            @endif
                        </div>
                        <p class="text-gray-600">{{ $type['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Contact -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl text-white p-12 text-center">
            <h2 class="text-3xl font-bold mb-6">{{ $copy['contact']['title'] }}</h2>
            <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">{{ $copy['contact']['subtitle'] }}</p>

            <div class="grid grid-cols-1 md:grid-cols-{{ count($copy['contact']['channels']) }} gap-8 mb-8">
                @foreach ($copy['contact']['channels'] as $channel)
                    <div class="text-center">
                        @if ($channel['title'] === __('contact.contact_info.items.0.title'))
                            <i class="fas fa-envelope text-4xl mb-4"></i>
                        @else
                            <i class="fas fa-phone text-4xl mb-4"></i>
                        @endif
                        <h3 class="text-xl font-bold mb-2">{{ $channel['title'] }}</h3>
                        <p class="opacity-90">{{ $channel['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <button class="bg-white text-orange-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition-colors">
                {{ $copy['contact']['cta'] }}
            </button>
        </div>
    </div>
</div>
@endsection

