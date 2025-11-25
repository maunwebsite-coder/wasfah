@extends('layouts.app')

@section('title', __('about.meta.title'))

@section('content')
@php
    $copy = \Illuminate\Support\Facades\Lang::get('about');
    $featureIcons = ['fas fa-book', 'fas fa-graduation-cap', 'fas fa-users'];
    $teamIcons = ['fas fa-user', 'fas fa-chef-hat', 'fas fa-headset'];
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 space-y-12">
        <!-- Hero -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl text-white p-12 text-center shadow-2xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ $copy['hero']['title'] }}</h1>
            <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">{{ $copy['hero']['subtitle'] }}</p>
        </div>

        <!-- Mission -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">{{ $copy['mission']['title'] }}</h2>
                    <p class="text-gray-600 text-lg leading-relaxed mb-6">{{ $copy['mission']['description'] }}</p>
                    <div class="flex items-center space-x-4 rtl:space-x-reverse">
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-heart text-orange-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">{{ $copy['mission']['highlight']['title'] }}</h3>
                            <p class="text-gray-600">{{ $copy['mission']['highlight']['description'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl p-8 text-center">
                    <i class="fas fa-utensils text-6xl text-orange-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $copy['mission']['stats_card']['title'] }}</h3>
                    <p class="text-gray-600">{{ $copy['mission']['stats_card']['description'] }}</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($copy['features'] as $index => $feature)
                <div class="bg-white rounded-xl shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="{{ $featureIcons[$index] ?? 'fas fa-star' }} text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Story -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ $copy['story']['title'] }}</h2>
            <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($copy['story']['sections'] as $section)
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">{{ $section['title'] }}</h3>
                        <p class="text-gray-600 leading-relaxed">{{ $section['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl text-white p-12">
            <h2 class="text-3xl font-bold text-center mb-12">{{ $copy['stats']['title'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                @foreach ($copy['stats']['items'] as $item)
                    <div>
                        <div class="text-4xl font-bold mb-2">{{ $item['value'] }}</div>
                        <div class="text-orange-200">{{ $item['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Team -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ $copy['team']['title'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($copy['team']['members'] as $index => $member)
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-orange-400 to-orange-600 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="{{ $teamIcons[$index] ?? 'fas fa-user' }} text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $member['title'] }}</h3>
                        <p class="text-gray-600">{{ $member['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

