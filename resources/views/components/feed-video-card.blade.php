@props(['video'])

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Support\VideoEmbed;

    $coverImage = $video->image_url ?: \App\Support\BrandAssets::logoAsset('webp');
    $inlineVideo = VideoEmbed::inlinePlayable($video->video_url);
    $embedUrl = VideoEmbed::embedUrl($video->video_url);
    $hideTitle = (bool) ($video->hide_title ?? false);
@endphp

<article class="feed-video-card relative rounded-2xl border border-white/10 shadow-xl overflow-hidden transition-all duration-500" style="aspect-ratio: 9 / 16; width: 100%; height: 100%;">
    @if($inlineVideo)
        <video
            src="{{ $video->video_url }}"
            class="absolute inset-0 w-full h-full object-cover"
            preload="metadata"
            playsinline
            muted
            loop
            poster="{{ $coverImage }}"
        ></video>
    @elseif($embedUrl)
        <iframe
            src="{{ $embedUrl }}"
            class="absolute inset-0 w-full h-full"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="{{ $video->title }}"
        ></iframe>
    @else
        <img
            src="{{ $coverImage }}"
            alt="{{ $video->title }}"
            class="absolute inset-0 w-full h-full object-cover"
            loading="lazy"
            onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';"
        >
    @endif

    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-95 pointer-events-none"></div>
    <div class="absolute inset-x-0 bottom-0 p-4 flex items-start justify-between gap-3 z-10 pointer-events-none">
        <div class="space-y-1 text-white">
            @unless($hideTitle)
                <h3 class="text-lg font-semibold leading-snug line-clamp-2">{{ $video->title }}</h3>
            @endunless
        </div>
    </div>

    <a href="{{ route('recipes') . '#feed-card-' . $video->slug }}"
       class="absolute inset-0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
       aria-label="{{ $video->title }}">
        <span class="sr-only">{{ __('home.featured_recipes.watch_cta') }}</span>
    </a>
</article>
