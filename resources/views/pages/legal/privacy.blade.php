@extends('layouts.app')

@php
    $copy = \Illuminate\Support\Facades\Lang::get('legal.privacy');
    $sections = $copy['sections'] ?? [];
@endphp

@section('title', data_get($copy, 'meta.title', 'Wasfah'))

@section('content')
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 max-w-4xl space-y-10">
        <div class="text-center space-y-3">
            <p class="text-orange-500 font-semibold uppercase tracking-widest">{{ data_get($copy, 'hero.eyebrow') }}</p>
            <h1 class="text-3xl font-extrabold text-gray-900">{{ data_get($copy, 'hero.title') }}</h1>
            <p class="text-gray-600 leading-relaxed">
                {{ data_get($copy, 'hero.description') }}
            </p>
        </div>

        <div class="space-y-8 text-gray-800 leading-8">
            @foreach ($sections as $section)
                <article class="space-y-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ data_get($section, 'title') }}</h2>
                    <p>{{ data_get($section, 'body') }}</p>
                </article>
            @endforeach
        </div>

        <div class="rounded-2xl bg-white shadow-sm border border-gray-200 p-6 text-sm text-gray-700 space-y-2">
            <p>{{ data_get($copy, 'updated_label') }}: {{ now()->format('Y-m-d') }}</p>
            <p>
                {{ data_get($copy, 'contact_label') }}:
                <a href="mailto:{{ data_get($copy, 'contact_email') }}" class="text-orange-600 font-semibold">
                    {{ data_get($copy, 'contact_email') }}
                </a>
            </p>
        </div>
    </div>
</section>
@endsection

