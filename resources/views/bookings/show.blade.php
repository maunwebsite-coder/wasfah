@extends('layouts.app')

@section('title', 'Workshop booking details #' . $booking->id)

@section('content')
@php
    $workshop = $booking->workshop ?? $booking->loadMissing('workshop')->workshop;
    $statusLabels = [
        'pending' => 'Pending review',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ];
    $backUrl = route('bookings.index');
    $backLabel = 'Back to bookings';
@endphp

<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm uppercase tracking-widest text-orange-500 font-semibold">{{ __('bookings.hero.title') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">Booking details #{{ $booking->id }}</h1>
                <p class="text-slate-500 mt-1">Review the workshop details, booking status, and the available join links.</p>
            </div>
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-slate-600 font-semibold shadow-sm hover:border-slate-300 hover:text-slate-900">
                <i class="fas fa-arrow-right"></i>
                {{ $backLabel }}
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-wider text-orange-500 font-semibold">Workshop</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ $workshop->title ?? 'Unknown workshop' }}</h2>
                    <p class="text-sm text-slate-500">
                        {{ optional($workshop->start_date)->locale('ar')->translatedFormat('d F Y • h:i a') }}
                        @if($workshop?->duration)
                            • Duration {{ $workshop->duration }} min
                        @endif
                    </p>
                </div>

                <dl class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <dt class="font-semibold text-slate-800">Booking status</dt>
                        <dd class="rounded-full px-3 py-1 text-xs font-semibold {{ $booking->status === 'confirmed' ? 'bg-emerald-50 text-emerald-600' : ($booking->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600') }}">
                            {{ $statusLabels[$booking->status] ?? $booking->status }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="font-semibold text-slate-800">Booking date</dt>
                        <dd>{{ $booking->created_at->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="font-semibold text-slate-800">Payment method</dt>
                        <dd>{{ $booking->payment_method ? ucfirst($booking->payment_method) : 'To be determined' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="font-semibold text-slate-800">Amount</dt>
                        <dd>{{ number_format($booking->payment_amount ?? $workshop->price ?? 0, 2) }} {{ $workshop->currency ?? 'USD' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-800 mb-1">Notes</dt>
                        <dd class="rounded-2xl bg-slate-50 px-4 py-3 text-slate-600">
                            {{ $booking->notes ?: 'No notes added' }}
                        </dd>
                    </div>
                </dl>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Location / join method</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">
                        {{ $workshop->is_online ? 'Online workshop' : 'In-person workshop' }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $workshop->is_online ? 'This workshop runs via Google Meet inside Wasfah.' : ($workshop->location ?? 'Details will be shared soon.') }}
                    </p>
                    <p class="mt-3 text-xs text-slate-500">
                        {{ __('bookings.notices.registration_closure', ['minutes' => 2]) }}
                    </p>
                    @if ($workshop->is_online)
                        <p class="mt-2">
                            <span class="text-sm text-slate-500 __web-inspector-hide-shortcut__">This workshop is online-only and cannot be switched to in-person.</span>
                        </p>
                        <div class="mt-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-600">
                            ننشئ غرفة Google Meet مباشرة من حسابك في Google لتبقى أنت المضيف. <span class="font-semibold text-slate-800">حساب المضيف:</span> maunwebsite@gmail.com
                        </div>
                    @endif

                    @if ($workshop->is_online && $workshop->meeting_link)
                        @if ($booking->status === 'confirmed')
                            @if(!empty($booking->public_code))
                                <a href="{{ $booking->secure_join_url }}"
                                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow hover:from-indigo-600 hover:to-indigo-700">
                                     <i class="fas fa-video"></i>
                                     Enter workshop room
                                 </a>
                                 <p class="mt-2 text-xs text-slate-500 text-center">
                                     The room opens shortly before the workshop after the chef approves it.
                                 </p>
                            @else
                                <div class="mt-4 rounded-2xl border border-dashed border-purple-300 bg-purple-50 px-4 py-3 text-sm text-purple-700">
                                    The join code is not available yet. Please contact support to complete the booking.
                                </div>
                            @endif
                        @else
                            <div class="mt-4 rounded-2xl border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                The join button becomes active once the Wasfah team confirms your booking.
                            </div>
                        @endif
                    @endif
                </div>

                @if (!empty($workshop->recording_url) && $booking->status === 'confirmed')
                    <div class="rounded-3xl border border-indigo-100 bg-indigo-50 p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-wider text-indigo-600 font-semibold">Recorded workshop</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">
                            {{ $workshop->title ?? 'Workshop recording' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">Watch the recording of this session any time.</p>
                        <a href="{{ $workshop->recording_url }}" target="_blank" rel="noopener" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-slate-800">
                            <i class="fas fa-play"></i>
                            {{ __('bookings.history.actions.watch_recording') }}
                        </a>
                    </div>
                @endif

                @if ($booking->status === 'pending')
                    <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-700">
                        <p class="font-semibold mb-1">Booking under review</p>
                        <p>We will reach out once the booking is confirmed. Track the status from your profile at any time.</p>
                    </div>
                @elseif ($booking->status === 'cancelled')
                    <div class="rounded-3xl border border-red-100 bg-red-50 p-5 text-sm text-red-700">
                        <p class="font-semibold mb-1">This booking was cancelled</p>
                        <p>{{ $booking->cancellation_reason ?: 'Cancelled by request or because the process was not completed.' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

