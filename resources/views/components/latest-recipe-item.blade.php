@props(['recipe'])

<li>
    <a href="{{ route('recipe.show', $recipe->slug) }}" class="latest-recipe-mini">
        <div class="latest-recipe-thumb">
            <img src="{{ $recipe->image_url ?: \App\Support\BrandAssets::logoAsset('webp') }}"
                 alt="{{ $recipe->title }}"
                 width="56"
                 height="56"
                 onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='صورة افتراضية';" loading="lazy">
        </div>
        <div class="latest-recipe-info">
            <h3 class="latest-recipe-title line-clamp-2">
                {{ $recipe->title }}
            </h3>
            <div class="latest-recipe-meta">
                <span class="latest-recipe-meta-item">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    {{ $recipe->prep_time }} دقيقة
                </span>
                @if($recipe->interactions_avg_rating)
                    <span class="latest-recipe-meta-item is-rating">
                        <i class="fas fa-star" aria-hidden="true"></i>
                        {{ number_format($recipe->interactions_avg_rating, 1) }}
                    </span>
                @endif
            </div>
            @if($recipe->category)
                <span class="latest-recipe-chip">
                    {{ $recipe->category->name }}
                </span>
            @endif
        </div>
    </a>
</li>



