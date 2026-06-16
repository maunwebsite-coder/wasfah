@php
    $year = now()->year;
    $currentLocale = app()->getLocale();
    $isArabic = $currentLocale === 'ar';
    $alternateLocale = $isArabic ? 'en' : 'ar';
    $languageCopy = \Illuminate\Support\Facades\Lang::get('navbar.language');
    $showAdminTools = auth()->user()?->isAdmin() ?? false;

    $links = [
        ['href' => route('thedolci.shop'), 'label' => $isArabic ? 'Ø§Ù„Ù…ØªØ¬Ø±' : 'Shop'],
        ['href' => route('thedolci.seasonal'), 'label' => $isArabic ? 'Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹Ø§Øª Ø§Ù„Ù…ÙˆØ³Ù…ÙŠØ©' : 'Seasonal'],
        ['href' => route('about'), 'label' => $isArabic ? 'Ø¹Ù† Thedolci' : 'About Thedolci'],
        ['href' => route('contact'), 'label' => $isArabic ? 'ØªÙˆØ§ØµÙ„ Ù…Ø¹Ù†Ø§' : 'Contact us'],
        ['href' => route('thedolci.track-order'), 'label' => $isArabic ? 'ØªØªØ¨Ø¹ Ø§Ù„Ø·Ù„Ø¨' : 'Track order'],
        ['href' => route('legal.terms'), 'label' => $isArabic ? 'Ø§Ù„Ø´Ø±ÙˆØ· ÙˆØ§Ù„Ø£Ø­ÙƒØ§Ù…' : 'Legal terms'],
        ['href' => route('legal.privacy'), 'label' => $isArabic ? 'Ø³ÙŠØ§Ø³Ø© Ø§Ù„Ø®ØµÙˆØµÙŠØ©' : 'Privacy Policy'],
    ];
    if (! $showAdminTools) {
        $links = array_values(array_filter(
            $links,
            fn ($link) => ($link['href'] ?? null) !== route('about')
        ));
    }

    $lineOne = $isArabic
        ? 'Thedolci ÙŠÙ‚Ø¯Ù… Ø­Ù„ÙˆÙŠØ§Øª ÙØ§Ø®Ø±Ø© Ø·Ø§Ø²Ø¬Ø© Ø¥Ù„Ù‰ Ø¨Ø§Ø¨ Ù…Ù†Ø²Ù„Ùƒ.'
        : 'Thedolci delivers premium desserts fresh to your door.';

    $lineTwo = $isArabic
        ? 'Ù†Ø­Ø¶Ù‘Ø± ÙƒÙ„ Ø·Ù„Ø¨ ÙŠÙˆÙ…ÙŠÙ‹Ø§ Ø¨Ø·Ø§Ø¨Ø¹ Ø¥ÙŠØ·Ø§Ù„ÙŠ Ø£ØµÙŠÙ„.'
        : 'Every order is crafted daily with authentic Italian character.';
@endphp

<footer class="border-t border-gray-200 bg-white py-8">
    <div class="container mx-auto px-4 space-y-6">
        <div class="footer-line flex flex-col items-center gap-3 text-sm text-gray-600 md:flex-row md:flex-wrap md:justify-center">
            <form method="POST" action="{{ route('locale.switch') }}" class="inline-flex">
                @csrf
                <input type="hidden" name="locale" value="{{ $alternateLocale }}">
                <button
                    type="submit"
                    class="flex items-center gap-2 rounded-full border border-rose-200 bg-white px-4 py-2 font-semibold text-rose-700 shadow-sm transition-all duration-200 hover:border-rose-300 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    aria-label="{{ data_get($languageCopy, 'switch_to.' . $alternateLocale, 'Switch language') }}"
                >
                    <i class="fas fa-globe text-base" aria-hidden="true"></i>
                    <span>{{ data_get($languageCopy, 'short.' . $alternateLocale, strtoupper($alternateLocale)) }}</span>
                </button>
            </form>

            <nav class="flex flex-wrap items-center justify-center gap-3 text-center">
                @foreach ($links as $link)
                    @if (! $loop->first)
                        <span class="text-gray-300">&bull;</span>
                    @endif
                    <a href="{{ $link['href'] }}" class="font-medium transition-colors hover:text-rose-700">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="footer-line flex flex-col items-center gap-2 text-center text-sm text-gray-500 md:flex-row md:flex-wrap md:justify-center md:gap-4">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-1 rounded-full border border-rose-100 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-rose-700"
                aria-label="{{ __('auth.logo_alt') }}"
            >
                <span>the</span><span>dolci</span>
            </a>

            <div class="flex flex-col items-center gap-1 text-center md:flex-row md:flex-wrap md:items-center md:gap-3">
                <span>{{ $lineOne }}</span>
                <span class="text-gray-300">&bull;</span>
                <span>{{ $lineTwo }}</span>
                <span class="text-gray-300">&bull;</span>
                <span>{{ $year }} thedolci. {{ $isArabic ? 'Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ù‚ÙˆÙ‚ Ù…Ø­ÙÙˆØ¸Ø©.' : 'All rights reserved.' }}</span>
            </div>
        </div>
    </div>
</footer>

