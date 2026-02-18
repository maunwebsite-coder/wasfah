@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $isRtl = $isRtl ?? ($currentLocale === 'ar');
    $alternateLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $languageCopy = \Illuminate\Support\Facades\Lang::get('navbar.language');
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wasfah Platform')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap">
    </noscript>
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>
    @stack('preloads')
    @php
        $vite = app(\Illuminate\Foundation\Vite::class);
        $isViteHot = \App\Support\ViteHot::shouldUseHotReload();
        $hasViteManifest = \App\Support\ViteHot::hasBuildManifest();
    @endphp

    @if ($isViteHot)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif ($hasViteManifest)
        @php
            $appCss = $vite->asset('resources/css/app.css');
        @endphp
        <link rel="stylesheet" href="{{ $appCss }}">
        @vite(['resources/js/app.js'])
    @endif

    <style>
    body {
        font-family: 'Tajawal', 'Cairo', sans-serif;
        background: radial-gradient(circle at top right, rgba(111, 28, 56, 0.2), transparent 52%),
            radial-gradient(circle at bottom left, rgba(176, 74, 109, 0.16), transparent 42%),
            #fbf2f6;
    }
    </style>
    <script>
        window.__APP_LOCALE = "{{ $currentLocale }}";
        window.__CONTENT_TRANSLATIONS = @json($globalContentTranslations ?? []);
    </script>
    @stack('styles')
</head>
<body class="font-sans relative">

    @unless($hideLocaleSwitcher ?? false)
        <div class="fixed bottom-6 {{ $isRtl ? 'left-6' : 'right-6' }} z-50">
            <form
                method="POST"
                action="{{ route('locale.switch') }}"
                class="flex flex-col gap-2 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 shadow-2xl shadow-rose-100/70 backdrop-blur {{ $isRtl ? 'text-right items-end' : 'text-left items-start' }}"
            >
                @csrf
                <input type="hidden" name="locale" value="{{ $alternateLocale }}">
                <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">{{ data_get($languageCopy, 'label', 'Language') }}</p>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-gradient-to-r from-rose-50 to-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 shadow-sm transition hover:from-white hover:to-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    aria-label="{{ data_get($languageCopy, 'switch_to.' . $alternateLocale, 'Switch language') }}"
                >
                    <span>{{ data_get($languageCopy, 'short.' . $alternateLocale, strtoupper($alternateLocale)) }}</span>
                    <span class="text-[11px] font-medium text-slate-500">{{ data_get($languageCopy, 'switch_to.' . $alternateLocale, 'Switch language') }}</span>
                </button>
            </form>
        </div>
    @endunless

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>


    @stack('scripts')
</body>
</html>



