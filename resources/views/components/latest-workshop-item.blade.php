@props(['workshop'])

@php
    $imageUrl = $workshop->image
        ? asset('storage/' . ltrim($workshop->image, '/'))
        : \App\Support\BrandAssets::logoAsset('webp');

    $startDateLabel = $workshop->start_date
        ? $workshop->start_date->translatedFormat('d M')
        : __('home.labels.unspecified');

    $durationMinutes = $workshop->duration;
    $durationLabel = is_numeric($durationMinutes) ? $workshop->formatted_duration : null;

    $locationLabel = $workshop->is_online
        ? __('home.labels.online_short')
        : ($workshop->location ?: __('home.labels.onsite_short'));
@endphp

<li>
    <a href="{{ route('workshop.show', $workshop->slug) }}" class="latest-recipe-mini">
        <div class="latest-recipe-thumb">
            <img src="{{ $imageUrl }}"
                 alt="{{ $workshop->title }}"
                 width="56"
                 height="56"
                 onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ __('home.premium_workshops.placeholder_image_alt') }}';"
                 loading="lazy">
        </div>
        <div class="latest-recipe-info">
            <h3 class="latest-recipe-title line-clamp-2">
                {{ $workshop->title }}
            </h3>
            <div class="latest-recipe-meta">
                <span class="latest-recipe-meta-item">
                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    {{ $startDateLabel }}
                </span>
                @if($durationLabel)
                    <span class="latest-recipe-meta-item">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        {{ $durationLabel }}
                    </span>
                @endif
            </div>
            @if($locationLabel)
                <span class="latest-recipe-chip">
                    {{ $locationLabel }}
                </span>
            @endif
        </div>
    </a>
</li>
