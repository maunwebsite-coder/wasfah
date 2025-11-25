@extends('layouts.app')

@section('title', __('chef.recipes_create.page_title'))

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wider text-orange-500 font-semibold mb-2">{{ __('chef.recipes_create.hero_badge') }}</p>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('chef.recipes_create.hero_heading') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('chef.recipes_create.hero_description') }}</p>
            </div>
            <a href="{{ route('chef.recipes.index') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-white transition">
                <i class="fas fa-arrow-right"></i>
                {{ __('chef.recipes_create.back_to_index') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm leading-6">
                <p class="font-semibold mb-1">{{ __('chef.recipes_create.validation_heading') }}</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('chef.recipes.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @include('chef.recipes.form')

            <div class="flex flex-wrap items-center justify-end gap-3 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <button type="submit" data-submit-action="draft" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-gray-700 hover:bg-gray-50 transition">
                    <i class="fas fa-save"></i>
                    {{ __('chef.recipes_create.actions.save_draft') }}
                </button>
                <button type="submit" data-submit-action="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition">
                    <i class="fas fa-paper-plane"></i>
                    {{ __('chef.recipes_create.actions.submit_review') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

