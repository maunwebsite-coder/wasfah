<div>
    @if($hasWhatsappBooking)
        <div class="space-y-2 mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm font-semibold text-gray-900 text-right flex items-center justify-end gap-2">
                <span>{{ __('workshops.whatsapp_verification.title') }}</span>
                <i class="fab fa-whatsapp text-green-500 text-lg"></i>
            </p>
            @if($whatsappVerificationUrl)
                <a
                    href="{{ $whatsappVerificationUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full bg-white border border-emerald-200 text-emerald-700 font-bold py-3.5 px-4 rounded-xl booking-button flex items-center justify-center gap-2 transition hover:bg-emerald-50"
                >
                    <i class="fab fa-whatsapp text-xl booking-button-icon"></i>
                    <span class="booking-button-label">{{ __('workshops.whatsapp_verification.button') }}</span>
                </a>
            @else
                <button
                    type="button"
                    wire:click="verifyWhatsappBooking"
                    wire:loading.attr="disabled"
                    wire:target="verifyWhatsappBooking"
                    class="w-full bg-white border border-emerald-200 text-emerald-700 font-bold py-3.5 px-4 rounded-xl booking-button flex items-center justify-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span class="booking-button-label" wire:loading.remove wire:target="verifyWhatsappBooking">
                        {{ __('workshops.whatsapp_verification.button') }}
                    </span>
                    <span class="booking-button-label flex items-center gap-2 text-sm" wire:loading.flex wire:target="verifyWhatsappBooking">
                        <i class="fas fa-spinner fa-spin ml-1"></i>
                        {{ __('workshops.whatsapp_verification.loading') }}
                    </span>
                </button>
            @endif
            <p class="text-xs text-gray-500 text-center">
                {{ __('workshops.whatsapp_verification.helper') }}
            </p>
            @if($statusMessage)
                @php
                    $toneClasses = [
                        'success' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                        'error' => 'text-rose-600 bg-rose-50 border-rose-100',
                        'info' => 'text-amber-700 bg-amber-50 border-amber-100',
                    ];
                    $appliedTone = $toneClasses[$statusTone] ?? $toneClasses['info'];
                @endphp
                <div class="text-sm text-center rounded-xl border px-3 py-2 {{ $appliedTone }}">
                    {{ $statusMessage }}
                </div>
            @endif
        </div>
    @endif
</div>

