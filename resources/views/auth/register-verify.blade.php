@extends('layouts.auth')

@section('title', 'Verify your email – Wasfah')

@push('styles')
<style>
    .verify-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1.5rem;
        background: linear-gradient(135deg, #f8edee 0%, #f8edee 30%, #9f5e63 100%);
    }
    .verify-card {
        width: 100%;
        max-width: 540px;
        background: white;
        border-radius: 1.75rem;
        box-shadow: 0 30px 60px rgba(107, 46, 48, 0.2);
        border: 1px solid rgba(107, 46, 48, 0.12);
        overflow: hidden;
    }
    .verify-header {
        background: linear-gradient(135deg, #6b2e30, #9f5e63);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
    }
    .verify-content {
        padding: 2.5rem 2.5rem 2.75rem;
    }
    .code-input {
        letter-spacing: 0.65rem;
        text-align: center;
        font-size: 1.75rem;
        font-weight: 700;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        border: 2px solid #6b2e30;
        background: #ffffff;
        color: #7f3a3d;
        caret-color: #7f3a3d;
    }
    .code-input:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(107, 46, 48, 0.25);
    }
    .submit-btn {
        width: 100%;
        border: none;
        border-radius: 1rem;
        padding: 0.95rem 1.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #10b981, #059669);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(16, 185, 129, 0.25);
    }
    .resend-btn {
        background: none;
        border: none;
        color: #7f3a3d;
        font-weight: 700;
        text-decoration: underline;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="verify-container">
    <div class="verify-card">
        <div class="verify-header">
            <h1 class="text-3xl font-extrabold mb-2">
                Verify your email
            </h1>
            <p class="text-orange-100 text-sm">
                Enter the verification code we sent to your email to complete registration.
            </p>
        </div>
        <div class="verify-content space-y-6">
            @if(session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <i class="fas fa-check-circle ml-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <i class="fas fa-exclamation-circle ml-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-2xl border border-orange-100 bg-orange-50/60 px-4 py-4 text-sm leading-6 text-orange-700">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-orange-500 shadow">
                        <i class="fas fa-envelope text-base"></i>
                    </span>
                    <div>
                        <div class="font-semibold">We sent the code to:</div>
                        <div class="text-base text-orange-900">{{ $email }}</div>
                    </div>
                </div>
                <p class="mt-3">
                    The code expires {{ $expiresAt?->locale(app()->getLocale())->diffForHumans() ?? 'in 15 minutes' }}.
                    If you do not see the email within a minute, check your spam folder or request a new code.
                </p>
            </div>

            <form method="POST" action="{{ route('register.verify') }}" class="space-y-5">
                @csrf
                <div class="space-y-2">
                    <label for="code" class="text-sm font-semibold text-gray-700">
                        6-digit verification code
                    </label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        required
                        class="code-input w-full"
                        value="{{ old('code') }}"
                        autofocus>
                    @error('code')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="submit-btn">
                    Verify email and create account
                </button>
            </form>

            <div class="text-sm text-gray-600 leading-6">
                <p class="mb-3">
                    Didn’t receive the code? We can resend it after one minute.
                </p>
                <form method="POST" action="{{ route('register.verify.resend') }}">
                    @csrf
                    <button type="submit" class="resend-btn">
                        Resend the code
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection









