@php
    $year = now()->year;
    $currentLocale = app()->getLocale();
    $alternateLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $languageCopy = \Illuminate\Support\Facades\Lang::get('navbar.language');
    $showAdminTools = auth()->user()?->isAdmin() ?? false;
    $footerLinks = [
        ['route' => 'recipes', 'label' => __('footer.bottom.links.recipes')],
        ['route' => 'workshops', 'label' => __('footer.bottom.links.workshops')],
        ['route' => 'tools', 'label' => __('footer.bottom.links.tools')],
        ['route' => 'about', 'label' => __('footer.bottom.links.about')],
        ['route' => 'contact', 'label' => __('footer.bottom.links.contact')],
        ['route' => 'legal.terms', 'label' => __('footer.bottom.links.legal')],
        ['url' => 'https://peahskill.com/privacy', 'label' => __('footer.bottom.links.privacy')],
    ];
    if (! $showAdminTools) {
        $footerLinks = array_values(array_filter(
            $footerLinks,
            fn ($link) => ($link['route'] ?? null) !== 'tools'
        ));
    }
@endphp
<footer class="border-t border-gray-200 bg-white py-8">
    <div class="container mx-auto px-4 space-y-6">
        <div class="footer-line flex flex-col items-center gap-3 text-sm text-gray-600 md:flex-row md:flex-wrap md:justify-center">
            <form method="POST" action="{{ route('locale.switch') }}" class="inline-flex">
                @csrf
                <input type="hidden" name="locale" value="{{ $alternateLocale }}">
                <button
                    type="submit"
                    class="flex items-center gap-2 rounded-full border border-orange-200 bg-white px-4 py-2 font-semibold text-orange-600 shadow-sm transition-all duration-200 hover:border-orange-300 hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-200"
                    aria-label="{{ data_get($languageCopy, 'switch_to.' . $alternateLocale, 'Switch language') }}"
                >
                    <i class="fas fa-globe text-base"></i>
                    <span>{{ data_get($languageCopy, 'short.' . $alternateLocale, strtoupper($alternateLocale)) }}</span>
                </button>
            </form>

            <nav class="flex flex-wrap items-center justify-center gap-3 text-center">
                @foreach ($footerLinks as $link)
                    @if (! $loop->first)
                        <span class="text-gray-300">•</span>
                    @endif
                    @php
                        $href = isset($link['route']) ? route($link['route']) : $link['url'];
                    @endphp
                    <a href="{{ $href }}" class="font-medium transition-colors hover:text-orange-500">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="footer-line flex flex-col items-center gap-2 text-center text-sm text-gray-500 md:flex-row md:flex-wrap md:justify-center md:gap-4">
            <div class="flex items-center gap-2 text-orange-600">
                    <x-optimized-picture
                        :base="\App\Support\BrandAssets::logoBase()"
                    :widths="[96, 192, 384]"
                    alt="{{ __('footer.brand.logo_alt') }}"
                    class="h-8 w-auto"
                    :lazy="false"
                    sizes="96px"
                />
            </div>

            <div class="flex flex-col items-center gap-1 text-center md:flex-row md:flex-wrap md:items-center md:gap-3">
                <span>{{ __('footer.bottom.line_one') }}</span>
                <span class="text-gray-300">•</span>
                <span>{{ __('footer.bottom.line_two') }}</span>
                <span class="text-gray-300">•</span>
                <span>{{ __('footer.bottom.copyright', ['year' => $year]) }}</span>
            </div>
        </div>
    </div>
</footer>

