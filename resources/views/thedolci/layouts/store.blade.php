<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="thedolci - Premium Italian tiramisu delivered fresh daily.">
    <meta name="theme-color" content="#6b2e30">
    <title>@yield('title', 'thedolci | Premium Tiramisu')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $thedolciAssets = ['resources/css/thedolci.css', 'resources/js/thedolci.js'];
        $fallbackAssets = ['resources/css/app.css', 'resources/js/app.js'];
        $isViteHot = \App\Support\ViteHot::shouldUseHotReload();
        $hasThedolciManifestEntries = \App\Support\ViteHot::hasBuildManifestEntries($thedolciAssets);
        $hasFallbackManifestEntries = \App\Support\ViteHot::hasBuildManifestEntries($fallbackAssets);
        $inlineDolciCss = is_file(resource_path('css/thedolci.css'))
            ? @file_get_contents(resource_path('css/thedolci.css'))
            : null;
        $inlineDolciJs = is_file(resource_path('js/thedolci.js'))
            ? @file_get_contents(resource_path('js/thedolci.js'))
            : null;
    @endphp

    @if($isViteHot || $hasThedolciManifestEntries)
        @vite($thedolciAssets)
    @elseif($inlineDolciCss && $inlineDolciJs)
        <style>{!! $inlineDolciCss !!}</style>
        <script type="module">{!! $inlineDolciJs !!}</script>
    @elseif($hasFallbackManifestEntries)
        @vite($fallbackAssets)
    @endif
</head>
<body class="dolci-body @yield('body_class')">
    @include('thedolci.partials.header', ['cartCount' => $cartCount ?? 0])

    <main class="dolci-main">
        @include('thedolci.partials.flash')
        @yield('content')
    </main>

    @include('thedolci.partials.footer')

    @stack('scripts')
</body>
</html>

