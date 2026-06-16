@php($instagramPostCount = $instagramPosts->count())

<section class="dolci-section dolci-home-social">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h2>{{ $instagram['section_title'] ?? 'From Instagram' }}</h2>
            <a href="{{ $instagram['profile_url'] ?? 'https://www.instagram.com/thedolci.jo/' }}" target="_blank" rel="noopener">
                {{ $instagram['handle'] ?? '@thedolci.jo' }}
            </a>
        </div>

        <div class="dolci-instagram-grid" data-instagram-slider>
            @forelse($instagramPosts as $index => $post)
                @php($caption = trim((string) ($post['caption'] ?? 'Instagram post')))
                <a
                    href="{{ $post['url'] }}"
                    target="_blank"
                    rel="noopener"
                    class="dolci-instagram-item"
                    data-instagram-slide
                    aria-label="Open Instagram post {{ $index + 1 }}: {{ $caption }}"
                >
                    <img src="{{ $post['image'] }}" alt="{{ $caption }}" loading="lazy">
                    <span class="dolci-instagram-caption">{{ $caption }}</span>
                </a>
            @empty
                <p class="dolci-limited">Instagram posts will appear here after you add them from admin.</p>
            @endforelse
        </div>

        @if($instagramPostCount > 1)
            <div class="dolci-trust-dots dolci-instagram-dots" aria-label="Instagram slider navigation">
                @foreach($instagramPosts as $index => $post)
                    <button
                        type="button"
                        data-instagram-dot="{{ $index }}"
                        aria-label="Instagram post {{ $index + 1 }}"
                        @class(['is-active' => $index === 0])
                        @if($index === 0) aria-current="true" @endif
                    ></button>
                @endforeach
            </div>
        @endif
    </div>
</section>
