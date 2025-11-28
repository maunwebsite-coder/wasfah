@extends('layouts.auth')

@php
    $copy = \Illuminate\Support\Facades\Lang::get('auth.policy_consent');
    $termsLinkLabel = __('auth.policy_consent.terms_link');
    $privacyLinkLabel = __('auth.policy_consent.privacy_link');
    $termsStatementHtml = __(
        'auth.policy_consent.terms_statement_html',
        [
            'terms' => '<a href="' . e($termsUrl) . '" target="_blank" rel="noopener noreferrer" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">' . e($termsLinkLabel) . '</a>',
            'privacy' => '<a href="' . e($privacyUrl) . '" target="_blank" rel="noopener noreferrer" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">' . e($privacyLinkLabel) . '</a>',
        ]
    );
    $termsHint = __('auth.policy_consent.terms_hint');
@endphp

@section('title', data_get($copy, 'title', 'Wasfah'))

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="bg-white/90 backdrop-blur rounded-3xl shadow-2xl border border-orange-100 p-10 space-y-8">
            <div class="space-y-3 text-center">
                <p class="text-sm uppercase font-semibold tracking-[0.3em] text-orange-500">{{ data_get($copy, 'eyebrow') }}</p>
                <h1 class="text-3xl font-extrabold text-gray-900">{{ data_get($copy, 'headline') }}</h1>
                <p class="text-gray-600 leading-relaxed">
                    {{ data_get($copy, 'description') }}
                </p>
            </div>

            <form method="POST" action="{{ route('policy-consent.store') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-gray-900">{{ data_get($copy, 'name_label') }}</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        dir="auto"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        class="w-full rounded-2xl border border-gray-200 bg-white/70 focus:bg-white px-4 py-3 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        placeholder="{{ data_get($copy, 'name_placeholder') }}"
                    >
                    @error('name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-orange-50/40 px-4 py-4">
                    <input
                        type="checkbox"
                        name="accept_terms"
                        id="accept_terms"
                        value="1"
                        class="mt-1 h-5 w-5 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                        {{ old('accept_terms') ? 'checked' : '' }}
                        required
                    >
                    <div class="flex-1">
                        <label for="accept_terms" class="text-sm text-gray-800 leading-6">
                            {!! $termsStatementHtml !!}
                            <span class="block text-xs text-gray-500 mt-1">
                                {{ $termsHint }}
                            </span>
                        </label>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-semibold text-orange-700">
                            <a
                                href="{{ $termsUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full border border-orange-100 bg-white px-3 py-1 hover:bg-orange-50 hover:border-orange-200"
                            >
                                <i class="fas fa-file-contract text-[11px]"></i>
                                {{ $termsLinkLabel }}
                            </a>
                            <span class="text-gray-300" aria-hidden="true">•</span>
                            <a
                                href="{{ $privacyUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full border border-orange-100 bg-white px-3 py-1 hover:bg-orange-50 hover:border-orange-200"
                            >
                                <i class="fas fa-shield-alt text-[11px]"></i>
                                {{ $privacyLinkLabel }}
                            </a>
                        </div>
                    </div>
                </div>
                @error('accept_terms')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="space-y-4">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 text-white font-semibold py-3 shadow-lg shadow-orange-200 hover:bg-orange-600 transition"
                    >
                        {{ data_get($copy, 'submit') }}
                        <span class="text-xs uppercase tracking-widest">{{ data_get($copy, 'submit_hint') }}</span>
                    </button>

                    <div class="text-center">
                        <a href="{{ url()->previous() ?: route('home') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900">
                            {{ data_get($copy, 'back') }}
                        </a>
                    </div>
                </div>
            </form>

            <div class="text-center text-xs text-gray-400">
                {{ __('auth.policy_consent.policy_version', ['version' => $policyVersion]) }}
            </div>
        </div>
    </div>
</div>
@endsection

