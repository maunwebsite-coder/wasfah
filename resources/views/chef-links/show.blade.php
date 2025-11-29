@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $chefName = $page->user->name ?? __('chef_links.footer.brand');
    $defaultHeadline = $page->headline ?? __('chef_links.meta.default_title', ['name' => $chefName]);
    $defaultDescription = $page->subheadline ?? __('chef_links.meta.default_description');
    $ogTitle = $page->headline ?? __('chef_links.meta.og_title');
    $ogDescription = $page->subheadline ?? __('chef_links.meta.og_description');
    $imageAlt = __('chef_links.profile.image_alt', ['name' => $chefName]);
    $dateLocale = $isRtl ? 'ar' : 'en';
    $dateFormat = $isRtl ? 'd F Y • h:i a' : 'd M Y • h:i a';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $defaultHeadline }}</title>
    <meta name="description" content="{{ $defaultDescription }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $page->avatar_url ?? \App\Support\BrandAssets::logoAsset('webp') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="{{ $accentColor }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />
    <style>
        :root {
            --accent: {{ $accentColor }};
            --accent-soft: {{ $accentColor }}1a;
            --solid-bg: #f4f6ff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --card-bg: rgba(255, 255, 255, 0.96);
            --border: rgba(0, 0, 0, 0.05);
            font-family: 'Tajawal', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 32px 16px 48px;
            background: linear-gradient(180deg, rgba(244, 246, 255, 0.9) 0%, #ffffff 55%, #ffffff 100%);
            color: var(--text-main);
        }

        .page-card {
            width: min(420px, 100%);
            background: var(--card-bg);
            border-radius: 36px;
            border: 1px solid var(--border);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.12);
            padding: 32px 28px 36px;
            position: relative;
            overflow: hidden;
        }

        .page-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(110% 130% at 50% -10%, rgba(8, 25, 255, 0.16) 0%, transparent 65%);
            pointer-events: none;
        }

        header {
            text-align: center;
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
        }

        .hero-image {
            width: 140px;
            height: 140px;
            border-radius: 32px;
            object-fit: cover;
            display: block;
            margin: 0 auto 16px;
            box-shadow: 0 20px 45px rgba(8, 25, 255, 0.18);
            border: 4px solid rgba(8, 25, 255, 0.15);
            background: #fff;
        }

        h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        p.subtitle {
            margin: 8px 0 0;
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .bio {
            margin: 18px auto 0;
            padding: 14px 18px;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .cta-button {
            margin: 20px 0 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 28px;
            border-radius: 999px;
            border: none;
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            font-size: 0.98rem;
            text-decoration: none;
            box-shadow: 0 18px 40px rgba(8, 25, 255, 0.25);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 50px rgba(8, 25, 255, 0.3);
        }

        .links-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            z-index: 2;
        }

        .link-card {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 18px 22px;
            border-radius: 26px;
            border: 1px solid rgba(8, 25, 255, 0.1);
            background: rgba(255, 255, 255, 0.97);
            color: inherit;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .link-card::after {
            content: '';
            position: absolute;
            inset-block-start: -40%;
            inset-inline-end: -10%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(8, 25, 255, 0.08), transparent 65%);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .link-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 32px rgba(8, 25, 255, 0.12);
        }

        .link-card:hover::after {
            opacity: 1;
        }

        .link-card--upcoming {
            position: relative;
            border: 1px solid rgba(8, 25, 255, 0.25);
            background: linear-gradient(135deg, rgba(8, 25, 255, 0.08), rgba(255, 255, 255, 0.96));
            overflow: hidden;
        }

        .link-card--upcoming::after {
            display: none;
        }

        .link-card--photo {
            border: none;
            background: transparent;
            color: #fff;
            padding: 24px;
            box-shadow: 0 30px 50px rgba(15, 23, 42, 0.25);
        }

        .link-card--photo::before,
        .link-card--photo::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .link-card--photo::before {
            background-image: var(--card-image);
            background-size: cover;
            background-position: center;
            filter: saturate(1) brightness(0.95);
        }

        .link-card--photo::after {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.3));
        }

        .link-card--photo > * {
            position: relative;
            z-index: 1;
        }

        .link-card--photo .link-title {
            color: #fff;
        }

        .link-card--photo .link-subtitle {
            color: rgba(255, 255, 255, 0.82);
        }

        .link-card--photo .link-arrow {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .link-card--photo:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 40px 60px rgba(15, 23, 42, 0.35);
        }

        .link-card--upcoming {
            position: relative;
            border: 1px solid rgba(8, 25, 255, 0.25);
            background: linear-gradient(135deg, rgba(8, 25, 255, 0.08), rgba(255, 255, 255, 0.96));
            overflow: hidden;
        }

        .link-card--upcoming::before {
            content: '';
            position: absolute;
            inset-inline-end: -80px;
            inset-block-start: -60px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(8, 25, 255, 0.08);
            z-index: 0;
        }

        .link-card--upcoming .link-icon {
            background: rgba(8, 25, 255, 0.2);
            color: var(--accent);
        }

        .link-card--upcoming .upcoming-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(8, 25, 255, 0.12);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .link-card--upcoming .workshop-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.75);
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .link-card--upcoming .workshop-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .link-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(8, 25, 255, 0.12), rgba(8, 25, 255, 0.25));
            color: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 14px 24px rgba(8, 25, 255, 0.12);
        }

        .link-text {
            flex: 1;
        }

        .link-title {
            font-weight: 600;
            font-size: 1rem;
            margin: 0;
        }

        .link-subtitle {
            margin: 4px 0 0;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .link-arrow {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(8, 25, 255, 0.14);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .link-arrow--ghost {
            background: rgba(255, 255, 255, 0.25);
            color: var(--accent);
        }

        footer {
            margin-top: 28px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            position: relative;
            z-index: 2;
        }

        footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .manage-bar {
            position: fixed;
            inset-inline-end: 16px;
            inset-block-end: 16px;
            background: var(--accent);
            color: #fff;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 18px 40px rgba(8, 25, 255, 0.25);
        }

        .manage-bar:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <main class="page-card">
        <header>
            <img src="{{ $page->avatar_url ?? \App\Support\BrandAssets::logoAsset('webp') }}" alt="{{ $imageAlt }}" class="hero-image" loading="lazy">
            <h1>{{ $defaultHeadline }}</h1>
            <p class="subtitle">{{ $page->subheadline ?? $defaultDescription }}</p>
            <div class="bio">{{ $page->bio ?? __('chef_links.profile.bio_fallback') }}</div>
            @if ($page->cta_label && $page->cta_url)
                <a class="cta-button" href="{{ $page->cta_url }}" target="_blank" rel="noopener">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    {{ $page->cta_label }}
                </a>
            @endif
        </header>

        <section class="links-stack">
            @if ($page->show_upcoming_workshop && $upcomingWorkshop)
                @php
                    $upcomingDate = optional($upcomingWorkshop->start_date)->locale($dateLocale)->translatedFormat($dateFormat);
                    $upcomingLocation = $upcomingWorkshop->is_online ? __('chef_links.upcoming.location.online') : ($upcomingWorkshop->location ?: __('chef_links.upcoming.location.pending'));
                    $upcomingPrice = $upcomingWorkshop->formatted_price ?? (number_format((float) ($upcomingWorkshop->price ?? 0), 2) . ' ' . ($upcomingWorkshop->currency ?? 'USD'));
                @endphp
                <a href="{{ route('workshop.show', ['workshop' => $upcomingWorkshop->slug]) }}" target="_blank" rel="noopener" class="link-card link-card--upcoming">
                    <span class="link-icon">
                        <i class="fas fa-calendar-day"></i>
                    </span>
                    <span class="link-text">
                        <span class="upcoming-badge">
                            <i class="fas fa-bolt"></i>
                            {{ __('chef_links.upcoming.badge') }}
                        </span>
                        <span class="link-title">{{ $upcomingWorkshop->title }}</span>
                        <span class="link-subtitle">{{ \Illuminate\Support\Str::limit($upcomingWorkshop->description ?? __('chef_links.upcoming.description_fallback'), 110) }}</span>
                        <span class="workshop-meta">
                            @if ($upcomingDate)
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $upcomingDate }}
                                </span>
                            @endif
                            <span>
                                <i class="fas {{ $upcomingWorkshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                {{ $upcomingLocation }}
                            </span>
                            <span>
                                <i class="fas fa-money-bill-wave"></i>
                                {{ $upcomingPrice }}
                            </span>
                        </span>
                    </span>
                    <span class="link-arrow link-arrow--ghost" style="z-index: 1;">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                </a>
            @endif
            @forelse ($page->items as $item)
                @php
                    $itemImage = $item->image_url;
                    $itemIcon = $item->icon ?: 'fas fa-link';
                    $cardClasses = 'link-card';
                    if ($itemImage) {
                        $cardClasses .= ' link-card--photo';
                    }
                @endphp
                <a href="{{ $item->url }}" target="_blank" rel="noopener" class="{{ $cardClasses }}" @if ($itemImage) style="--card-image: url('{{ $itemImage }}');" @endif>
                    @unless ($itemImage)
                        <span class="link-icon">
                            <i class="{{ $itemIcon }}"></i>
                        </span>
                    @endunless
                    <span class="link-text">
                        <span class="link-title">{{ $item->title }}</span>
                        @if ($item->subtitle)
                            <span class="link-subtitle">{{ $item->subtitle }}</span>
                        @endif
                    </span>
                    <span class="link-arrow">
                        <i class="fas fa-angle-left"></i>
                    </span>
                </a>
            @empty
                <div class="link-card" style="justify-content: center; background: rgba(8, 25, 255, 0.06); border-style: dashed;">
                    <span class="link-text" style="text-align: center;">
                        <span class="link-title">{{ __('chef_links.empty.title') }}</span>
                        <span class="link-subtitle">{{ __('chef_links.empty.subtitle') }}</span>
                    </span>
                </div>
            @endforelse
        </section>

        <footer>
            <p>{{ __('chef_links.footer.powered', ['brand' => __('chef_links.footer.brand')]) }} <a href="{{ url('/') }}">{{ __('chef_links.footer.brand') }}</a></p>
        </footer>
    </main>

    @auth
        @if (auth()->id() === $page->user_id || auth()->user()->isAdmin())
            <a href="{{ route('chef.links.edit') }}" class="manage-bar">
                <i class="fas fa-pen"></i>
                {{ __('chef_links.manage.label') }}
            </a>
        @endif
    @endauth
</body>
</html>










