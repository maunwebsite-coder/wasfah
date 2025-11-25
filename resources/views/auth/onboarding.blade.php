@extends('layouts.app')

@section('title', __('onboarding.page_title'))

@push('styles')
<style>
    .onboarding-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(15, 76, 115, 0.08);
    }
    .onboarding-card[dir="rtl"] {
        direction: rtl;
        text-align: right;
    }
    .onboarding-header {
        background: linear-gradient(135deg, #0f4c73 0%, #0fb39a 40%, #a1dfd1 100%);
        color: white;
        border-radius: 1.5rem 1.5rem 0 0;
        padding: 2.5rem 2rem;
        position: relative;
        overflow: hidden;
    }
    .onboarding-header::after {
        content: "";
        position: absolute;
        bottom: -40px;
        right: -40px;
        width: 160px;
        height: 160px;
        background: rgba(255, 255, 255, 0.18);
        border-radius: 9999px;
    }
    .onboarding-content {
        padding: 2.5rem;
    }
    .onboarding-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .onboarding-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.18);
        color: #eaf3f6;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge-tip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(34, 197, 94, 0.1);
        color: #047857;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .helper-checklist {
        background: #eaf3f6;
        border: 1px solid #c8ede3;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .checklist-item {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        color: #92400e;
        font-size: 0.95rem;
    }
    .checklist-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
        background: #0f4c73;
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-top: 0.15rem;
    }
    .checklist-item + .checklist-item {
        margin-top: 0.5rem;
    }
    .form-section {
        padding-top: 1.5rem;
        border-top: 1px dashed #c8ede3;
        margin-top: 1.5rem;
    }
    .form-section:first-of-type {
        border-top: none;
        padding-top: 0;
        margin-top: 0;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #7c2d12;
        margin-bottom: 0.35rem;
    }
    .form-section-hint {
        font-size: 0.95rem;
        color: #a16207;
        margin-bottom: 1rem;
    }
    .input-label {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
        display: block;
    }
    .input-control {
        border-radius: 0.85rem;
        border: 1px solid #e2e8f0;
        width: 100%;
        padding: 0.85rem 1rem;
        transition: all 0.2s ease;
    }
    .phone-prefix {
        min-width: 4.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-weight: 600;
        color: #1f2937;
    }
    .input-control:focus {
        outline: none;
        border-color: #0fb39a;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.25);
    }
    .submit-btn {
        background: linear-gradient(135deg, #0f4c73 0%, #0a8070 100%);
        color: white;
        padding: 0.95rem 1.5rem;
        border-radius: 0.85rem;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 35px rgba(15, 76, 115, 0.35);
    }
    .submit-btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(15, 76, 115, 0.28);
    }
    .stepper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .step-pill {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 1rem 1.1rem;
        border-radius: 1rem;
        border: 1px solid #c8ede3;
        background: #f8fafc;
        box-shadow: 0 10px 24px rgba(15, 76, 115, 0.08);
        transition: all 0.2s ease;
    }
    .step-pill.is-active {
        background: #0f4c73;
        border-color: #0f4c73;
        color: #eaf3f6;
        box-shadow: 0 18px 36px rgba(15, 76, 115, 0.3);
    }
    .step-pill__index {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 9999px;
        background: #a1dfd1;
        color: #0a344f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.4);
    }
    .step-pill.is-active .step-pill__index {
        background: white;
        color: #0f4c73;
        box-shadow: inset 0 0 0 0;
    }
    .step-pill__body p {
        margin: 0;
    }
    .step-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #ecfeff;
        border: 1px solid #bae6fd;
        color: #0ea5e9;
        padding: 0.45rem 0.8rem;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .step-panel {
        display: none;
        animation: fadeIn 0.25s ease;
    }
    .step-panel.is-active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .step-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: space-between;
        margin-top: 1rem;
    }
    .ghost-btn {
        border: 1px dashed #0f4c73;
        color: #0f4c73;
        background: #f8fafc;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        font-weight: 700;
    }
    .secondary-btn {
        border: 1px solid #e5e7eb;
        background: white;
        color: #0f172a;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .secondary-btn:hover {
        border-color: #0f4c73;
        color: #0f4c73;
    }
    .step-actions .submit-btn {
        min-width: 200px;
    }
</style>
@endpush

@section('content')
@php
    $locale = app()->getLocale();
    $isRtl = str_starts_with($locale, 'ar');
    $availableCountries = $countries ?? [];
    $defaultCountryCode = 'SA';
    $selectedCountryCode = old('country_code', $user->country_code ?? $defaultCountryCode);
    if (!array_key_exists($selectedCountryCode, $availableCountries)) {
        $selectedCountryCode = $defaultCountryCode;
    }
    $dialCodeFromCountry = $availableCountries[$selectedCountryCode]['dial_code'] ?? '+966';
    $storedDialCode = old('phone_country_code', $user->phone_country_code ?? $dialCodeFromCountry);
    $currentDialCode = str_starts_with($storedDialCode, '+') ? $storedDialCode : '+' . ltrim($storedDialCode ?? '', '+');
    $phoneInputValue = old('phone');
    if ($phoneInputValue === null) {
        $fullPhone = $user->phone ?? '';
        if ($fullPhone && str_starts_with($fullPhone, $currentDialCode)) {
            $phoneInputValue = ltrim(substr($fullPhone, strlen($currentDialCode)), ' ');
        } else {
            $phoneInputValue = $fullPhone;
        }
    }
    $steps = (array) trans('onboarding.steps');
    $checklistItems = (array) trans('onboarding.checklist.items');
    $specialtyOptions = (array) trans('onboarding.sections.bio.specialty_options');
    $stepKeys = ['contact', 'social', 'bio'];
    $stepFieldMap = [
        'contact' => ['country_code', 'phone', 'google_email'],
        'social' => ['instagram_url', 'youtube_url'],
        'bio' => ['chef_specialty_area', 'chef_specialty_description'],
    ];
    $initialStepKey = 'contact';
    foreach ($stepKeys as $key) {
        foreach ($stepFieldMap[$key] ?? [] as $field) {
            if ($errors->has($field)) {
                $initialStepKey = $key;
                break 2;
            }
        }
    }
    $totalSteps = count($stepKeys);
@endphp
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-100 py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="onboarding-card" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-initial-step="{{ $initialStepKey }}">
            <div class="onboarding-header">
                <h1 class="text-3xl font-bold mb-3">{{ __('onboarding.header.title') }}</h1>
                <p class="text-base md:text-lg text-orange-50/90 max-w-2xl leading-relaxed">
                    {{ __('onboarding.header.subtitle') }}
                </p>
                <div class="onboarding-chips">
                    <span class="onboarding-chip">
                        <i class="fas fa-bolt"></i>
                        {{ __('onboarding.header.chips.instant') }}
                    </span>
                    <span class="onboarding-chip">
                        <i class="fas fa-shield-alt"></i>
                        {{ __('onboarding.header.chips.secure') }}
                    </span>
                    <span class="onboarding-chip">
                        <i class="fas fa-users"></i>
                        {{ __('onboarding.header.chips.spotlight') }}
                    </span>
                </div>
            </div>
            <div class="onboarding-content">
                @if (session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div class="badge-tip">
                        <i class="fas fa-check-circle"></i>
                        {{ __('onboarding.header.badge_tip') }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('onboarding.header.signed_in_as', ['email' => $user->email]) }}
                    </div>
                </div>

                <div class="stepper" data-stepper>
                    @foreach($stepKeys as $index => $key)
                        @php $step = $steps[$index] ?? []; @endphp
                        <div class="step-pill {{ $key === $initialStepKey ? 'is-active' : '' }}" data-step-pill="{{ $key }}">
                            <div class="step-pill__index">{{ $index + 1 }}</div>
                            <div class="step-pill__body">
                                <p class="font-semibold text-sm">{{ $step['title'] ?? '' }}</p>
                                <p class="text-xs opacity-80">{{ $step['body'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="helper-checklist mb-10">
                    <h3 class="text-base font-semibold text-orange-900 mb-2">{{ __('onboarding.checklist.title') }}</h3>
                    <ul>
                        @foreach($checklistItems as $item)
                            <li class="checklist-item" @if($isRtl) dir="rtl" @endif>
                                <span class="checklist-icon"><i class="fas fa-check"></i></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-8" id="onboarding-form">
                    @csrf
                    <section class="step-panel {{ $initialStepKey === 'contact' ? 'is-active' : '' }}" data-step-panel="contact">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="step-status">
                                <i class="fas fa-arrow-right"></i>
                                {{ __('onboarding.navigation.step_status', ['current' => 1, 'total' => $totalSteps]) }}
                            </div>
                            <p class="text-sm text-orange-800 font-semibold">{{ $steps[0]['title'] ?? '' }}</p>
                        </div>
                        <div class="form-section">
                            <h3 class="form-section-title">{{ __('onboarding.sections.contact.title') }}</h3>
                            <p class="form-section-hint">{{ __('onboarding.sections.contact.hint') }}</p>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="input-label">{{ __('onboarding.sections.contact.country_label') }}</label>
                                    <select name="country_code" id="country-code" class="input-control">
                                        <option value="">{{ __('onboarding.sections.contact.country_placeholder') }}</option>
                                        @foreach($availableCountries as $code => $country)
                                            <option value="{{ $code }}"
                                                    data-dial-code="{{ $country['dial_code'] }}"
                                                    {{ $code === $selectedCountryCode ? 'selected' : '' }}>
                                                {{ $country['name'] }} ({{ $country['dial_code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_code')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="input-label">{{ __('onboarding.sections.contact.phone_label') }}</label>
                                    <div class="flex items-center gap-3">
                                        <span class="phone-prefix" id="dial-code-display">{{ $currentDialCode }}</span>
                                        <div class="flex-1">
                                            <input type="text"
                                                   name="phone"
                                                   value="{{ $phoneInputValue }}"
                                                   class="input-control"
                                                   placeholder="{{ __('onboarding.sections.contact.phone_placeholder') }}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="phone_country_code" id="phone_country_code" value="{{ $currentDialCode }}">
                                    <p class="mt-2 text-xs text-gray-500">{{ __('onboarding.sections.contact.phone_note') }}</p>
                                    @error('phone')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="input-label">{{ __('onboarding.sections.contact.google_email_label') }}</label>
                            <input type="email"
                                   name="google_email"
                                   value="{{ old('google_email', $user->google_email) }}"
                                   class="input-control"
                                   placeholder="name@gmail.com">
                            <p class="mt-2 text-xs text-gray-500">
                                {{ __('onboarding.sections.contact.google_email_hint') }}
                            </p>
                            @error('google_email')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="step-actions">
                            <span></span>
                            <button type="button" class="submit-btn" data-next-step="social">
                                {{ __('onboarding.navigation.next') }}
                            </button>
                        </div>
                    </section>

                    <section class="step-panel {{ $initialStepKey === 'social' ? 'is-active' : '' }}" data-step-panel="social">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="step-status">
                                <i class="fas fa-arrow-right"></i>
                                {{ __('onboarding.navigation.step_status', ['current' => 2, 'total' => $totalSteps]) }}
                            </div>
                            <p class="text-sm text-orange-800 font-semibold">{{ $steps[1]['title'] ?? '' }}</p>
                        </div>
                        <div class="form-section">
                            <h3 class="form-section-title">{{ __('onboarding.sections.social.title') }}</h3>
                            <p class="form-section-hint">{{ __('onboarding.sections.social.hint') }}</p>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="input-label">{{ __('onboarding.sections.social.instagram_label') }}</label>
                                    <input type="url"
                                           name="instagram_url"
                                           value="{{ old('instagram_url', $user->instagram_url) }}"
                                           class="input-control"
                                           placeholder="https://www.instagram.com/username">
                                    @error('instagram_url')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="input-label">{{ __('onboarding.sections.social.youtube_label') }}</label>
                                    <input type="url"
                                           name="youtube_url"
                                           value="{{ old('youtube_url', $user->youtube_url) }}"
                                           class="input-control"
                                           placeholder="https://www.youtube.com/channel/...">
                                    @error('youtube_url')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                @php
                                    $instagramOldValue = old('instagram_url', $user->instagram_url);
                                    $youtubeOldValue = old('youtube_url', $user->youtube_url);
                                    $showSocialRequiredError = ($errors->has('instagram_url') || $errors->has('youtube_url')) && empty($instagramOldValue) && empty($youtubeOldValue);
                                @endphp
                                @if($showSocialRequiredError)
                                    <div class="md:col-span-2">
                                        <p class="mt-1 text-sm text-red-500">
                                            {{ __('onboarding.sections.social.required_error') }}
                                        </p>
                                    </div>
                                @endif
                                <div class="md:col-span-2">
                                    <div class="bg-orange-50 border border-orange-100 rounded-lg px-4 py-3 text-sm text-orange-700">
                                        {{ __('onboarding.sections.social.public_notice') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="step-actions">
                            <button type="button" class="secondary-btn" data-prev-step="contact">
                                {{ __('onboarding.navigation.back') }}
                            </button>
                            <button type="button" class="submit-btn" data-next-step="bio">
                                {{ __('onboarding.navigation.next') }}
                            </button>
                        </div>
                    </section>

                    <section class="step-panel {{ $initialStepKey === 'bio' ? 'is-active' : '' }}" data-step-panel="bio">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="step-status">
                                <i class="fas fa-arrow-right"></i>
                                {{ __('onboarding.navigation.step_status', ['current' => 3, 'total' => $totalSteps]) }}
                            </div>
                            <p class="text-sm text-orange-800 font-semibold">{{ $steps[2]['title'] ?? '' }}</p>
                        </div>
                        <div class="form-section">
                            <h3 class="form-section-title">{{ __('onboarding.sections.bio.title') }}</h3>
                            <p class="form-section-hint">{{ __('onboarding.sections.bio.hint') }}</p>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="input-label">{{ __('onboarding.sections.bio.specialty_label') }}</label>
                                    <select name="chef_specialty_area" class="input-control">
                                        <option value="">{{ __('onboarding.sections.bio.specialty_placeholder') }}</option>
                                        @foreach($specialtyOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('chef_specialty_area', $user->chef_specialty_area) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('chef_specialty_area')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2 md:col-start-1">
                                    <label class="input-label">{{ __('onboarding.sections.bio.description_label') }}</label>
                                    <textarea name="chef_specialty_description"
                                              rows="5"
                                              class="input-control"
                                              placeholder="{{ __('onboarding.sections.bio.description_placeholder') }}">{{ old('chef_specialty_description', $user->chef_specialty_description) }}</textarea>
                                    @error('chef_specialty_description')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            {{ __('onboarding.alerts.post_submit') }}
                        </div>

                        <div class="step-actions">
                            <button type="button" class="secondary-btn" data-prev-step="social">
                                {{ __('onboarding.navigation.back') }}
                            </button>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="ghost-btn" name="skip_bio" value="1" formnovalidate>
                                    {{ __('onboarding.navigation.skip_bio') }}
                                </button>
                                <button type="submit" class="submit-btn">
                                    {{ __('onboarding.submit.cta') }}
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">{{ __('onboarding.submit.time_notice') }}</p>
                    </section>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countrySelect = document.getElementById('country-code');
        const dialCodeDisplay = document.getElementById('dial-code-display');
        const phoneCountryCodeInput = document.getElementById('phone_country_code');

        const updateDialCode = () => {
            if (!countrySelect) {
                return;
            }

            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const dialCode = selectedOption ? selectedOption.getAttribute('data-dial-code') : '';

            if (dialCodeDisplay) {
                dialCodeDisplay.textContent = dialCode || '+';
            }

            if (phoneCountryCodeInput) {
                phoneCountryCodeInput.value = dialCode || '';
            }
        };

        if (countrySelect) {
            countrySelect.addEventListener('change', updateDialCode);
            updateDialCode();
        }

        const wizardContainer = document.querySelector('.onboarding-card');
        const panels = Array.from(document.querySelectorAll('[data-step-panel]'));
        const pills = Array.from(document.querySelectorAll('[data-step-pill]'));

        const activateStep = (targetStep) => {
            panels.forEach(panel => {
                panel.classList.toggle('is-active', panel.dataset.stepPanel === targetStep);
            });
            pills.forEach(pill => {
                pill.classList.toggle('is-active', pill.dataset.stepPill === targetStep);
            });
        };

        if (wizardContainer && panels.length) {
            const initialStep = wizardContainer.getAttribute('data-initial-step') || panels[0].dataset.stepPanel;
            activateStep(initialStep);

            document.querySelectorAll('[data-next-step]').forEach(button => {
                button.addEventListener('click', () => {
                    const nextStep = button.getAttribute('data-next-step');
                    if (nextStep) {
                        activateStep(nextStep);
                    }
                });
            });

            document.querySelectorAll('[data-prev-step]').forEach(button => {
                button.addEventListener('click', () => {
                    const prevStep = button.getAttribute('data-prev-step');
                    if (prevStep) {
                        activateStep(prevStep);
                    }
                });
            });
        }
    });
</script>
@endpush
