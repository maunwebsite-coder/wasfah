@php
    $instagramPosts = old('instagram_posts', $instagram['posts'] ?? []);
    if (! is_array($instagramPosts)) {
        $instagramPosts = [];
    }

    $instagramPosts = array_values($instagramPosts);
    if ($instagramPosts === []) {
        $instagramPosts = [['image' => '', 'url' => '', 'caption' => '']];
    }

    $maxInstagramPosts = 12;
@endphp

<section class="dolci-admin-section dolci-admin-instagram-section" data-instagram-admin data-max-posts="{{ $maxInstagramPosts }}" data-disabled="{{ $dbReady ? '0' : '1' }}">
    <div class="dolci-admin-section-head">
        <div>
            <h3>Instagram Section</h3>
            <p class="dolci-limited">Keep this block independent from the rest of the page content and manage posts from one place.</p>
        </div>
        <a href="{{ old('instagram_profile_url', $instagram['profile_url'] ?? 'https://www.instagram.com/thedolci.jo/') }}" target="_blank" rel="noopener">Open profile</a>
    </div>

    <label>Section Title</label>
    <input type="text" name="instagram_section_title" value="{{ old('instagram_section_title', $instagram['section_title'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

    <div class="dolci-form-grid-2">
        <div>
            <label>Instagram Handle</label>
            <input type="text" name="instagram_handle" value="{{ old('instagram_handle', $instagram['handle'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
        </div>
        <div>
            <label>Instagram Profile URL</label>
            <input type="text" name="instagram_profile_url" value="{{ old('instagram_profile_url', $instagram['profile_url'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
        </div>
    </div>

    <div class="dolci-admin-section-head dolci-admin-subsection-head">
        <h4>Instagram Posts</h4>
        <div class="dolci-admin-instagram-actions">
            <span class="dolci-limited"><strong data-instagram-count>{{ count($instagramPosts) }}</strong> / {{ $maxInstagramPosts }}</span>
            <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-add {{ $dbReady ? '' : 'disabled' }}>
                Add Post
            </button>
        </div>
    </div>

    <div class="dolci-instagram-admin-list" data-instagram-list>
        @foreach($instagramPosts as $index => $post)
            @php($imageValue = old("instagram_posts.$index.image", $post['image'] ?? ''))
            @php($urlValue = old("instagram_posts.$index.url", $post['url'] ?? ''))
            @php($captionValue = old("instagram_posts.$index.caption", $post['caption'] ?? ''))
            <article class="dolci-instagram-admin-item" data-instagram-item data-index="{{ $index }}">
                <div class="dolci-instagram-admin-item-head">
                    <h4 data-instagram-item-title>Post {{ $index + 1 }}</h4>
                    <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-remove {{ $dbReady ? '' : 'disabled' }}>
                        Remove
                    </button>
                </div>

                <div class="dolci-form-grid-2">
                    <div>
                        <label>Image URL</label>
                        <input
                            type="text"
                            name="instagram_posts[{{ $index }}][image]"
                            value="{{ $imageValue }}"
                            data-instagram-image
                            data-field="image"
                            {{ $dbReady ? '' : 'disabled' }}
                        >
                    </div>
                    <div>
                        <label>Image Preview</label>
                        <div class="dolci-instagram-admin-preview">
                            <img src="{{ $imageValue }}" alt="Instagram post preview" loading="lazy" data-instagram-preview @if(trim((string) $imageValue) === '') hidden @endif>
                            <span data-instagram-placeholder @if(trim((string) $imageValue) !== '') hidden @endif>Paste an image URL to preview.</span>
                        </div>
                    </div>
                </div>

                <label>Post URL</label>
                <input
                    type="text"
                    name="instagram_posts[{{ $index }}][url]"
                    value="{{ $urlValue }}"
                    data-field="url"
                    {{ $dbReady ? '' : 'disabled' }}
                >

                <label>Caption</label>
                <input
                    type="text"
                    name="instagram_posts[{{ $index }}][caption]"
                    value="{{ $captionValue }}"
                    data-field="caption"
                    {{ $dbReady ? '' : 'disabled' }}
                >
            </article>
        @endforeach
    </div>

    <template data-instagram-template>
        <article class="dolci-instagram-admin-item" data-instagram-item data-index="__INDEX__">
            <div class="dolci-instagram-admin-item-head">
                <h4 data-instagram-item-title>Post __NUMBER__</h4>
                <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-remove {{ $dbReady ? '' : 'disabled' }}>
                    Remove
                </button>
            </div>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Image URL</label>
                    <input type="text" name="instagram_posts[__INDEX__][image]" value="" data-instagram-image data-field="image" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Image Preview</label>
                    <div class="dolci-instagram-admin-preview">
                        <img src="" alt="Instagram post preview" loading="lazy" data-instagram-preview hidden>
                        <span data-instagram-placeholder>Paste an image URL to preview.</span>
                    </div>
                </div>
            </div>

            <label>Post URL</label>
            <input type="text" name="instagram_posts[__INDEX__][url]" value="" data-field="url" {{ $dbReady ? '' : 'disabled' }}>

            <label>Caption</label>
            <input type="text" name="instagram_posts[__INDEX__][caption]" value="" data-field="caption" {{ $dbReady ? '' : 'disabled' }}>
        </article>
    </template>
</section>
