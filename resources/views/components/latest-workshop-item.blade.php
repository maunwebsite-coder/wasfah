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
    $locationStatusClass = $workshop->is_online ? 'is-online' : 'is-onsite';
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
                <span class="latest-recipe-chip latest-workshop-chip {{ $locationStatusClass }}">
                    {{ $locationLabel }}
                </span>
            @endif
        </div>
    </a>
</li>

@once
    @push('styles')
        <style>
            .latest-recipe-chip.latest-workshop-chip {
                border: 1px solid transparent;
            }
            .latest-recipe-chip.latest-workshop-chip.is-online {
                background: linear-gradient(135deg, #0819ff, #2c4dff);
                color: #ffffff;
                border-color: #0819ff;
                box-shadow: 0 8px 18px rgba(8, 25, 255, 0.25);
            }
            .latest-recipe-chip.latest-workshop-chip.is-onsite {
                background: linear-gradient(135deg, #0a1ece, #6b7fff);
                color: #f8fafc;
                border-color: #1e2dd8;
                box-shadow: 0 8px 18px rgba(8, 25, 255, 0.18);
            }
        </style>
    @endpush
@endonce
