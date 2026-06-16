@extends('layouts.app')

@section('title', __('chef.workshops_create.page_title'))

@section('content')
<div class="min-h-screen bg-gradient-to-b from-orange-50/70 to-white py-10">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-orange-500">{{ __('chef.workshops_create.hero_badge') }}</p>
                <h1 class="mt-3 text-3xl font-bold text-slate-900">{{ __('chef.workshops_create.hero_heading') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('chef.workshops_create.hero_description') }}</p>
            </div>
            <a href="{{ route('chef.workshops.index') }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm hover:border-slate-300 hover:text-slate-800">
                <i class="fas fa-arrow-right"></i>
                {{ __('chef.workshops_create.back_to_list') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5 text-red-700">
                <p class="font-semibold">{{ __('chef.workshops_create.validation_heading') }}</p>
                <ul class="list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $formWorkshop = new \App\Models\Workshop();
            $formWorkshop->is_online = true;
            $formWorkshop->currency = 'JOD';
            $formWorkshop->level = 'beginner';
            $formDraftStorageKey = 'chef_workshop_create_' . (auth()->id() ?? 'guest');
        @endphp

        <form method="POST" action="{{ route('chef.workshops.store') }}" enctype="multipart/form-data" class="space-y-10" data-preserve-key="{{ $formDraftStorageKey }}">
            @csrf

            @include('chef.workshops.form', [
                'workshop' => $formWorkshop,
                'forceAutoMeetingLinks' => $forceAutoMeetingLinks ?? false,
                'hostCalendarConnected' => $hostCalendarConnected ?? false,
                'hostCalendarEmail' => $hostCalendarEmail ?? null,
            ])

            <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-slate-100 bg-white/90 px-5 py-4 shadow-lg backdrop-blur">
                <div class="text-sm text-slate-500">
                    {{ __('chef.workshops_create.draft_notice') }}
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3 text-white shadow hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-200">
                    <i class="fas fa-save"></i>
                    {{ __('chef.workshops_create.submit_label') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


