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
    $instagramSectionTitle = old('instagram_section_title', $instagram['section_title'] ?? '');
    $instagramHandle = old('instagram_handle', $instagram['handle'] ?? '');
    $instagramProfileUrl = old('instagram_profile_url', $instagram['profile_url'] ?? 'https://www.instagram.com/thedolci.jo/');
@endphp

<section class="dolci-admin-section dolci-admin-instagram-section" data-instagram-admin data-max-posts="{{ $maxInstagramPosts }}" data-disabled="{{ $dbReady ? '0' : '1' }}">
    <div class="dolci-admin-section-head">
        <div>
            <h3>Instagram Section</h3>
            <p class="dolci-limited">Manage your Instagram content in one place. Every saved post needs an image URL, post URL, and caption.</p>
        </div>
        <a href="{{ $instagramProfileUrl }}" target="_blank" rel="noopener" data-instagram-profile-link>Open profile</a>
    </div>

    <div class="dolci-instagram-admin-intro">
        <p><strong>Quick flow:</strong> paste image URL, paste post URL, write caption, then save.</p>
        <p class="dolci-limited">Tip: You can reorder posts, duplicate a post, or open a post link to verify before saving.</p>
    </div>

    <label>Section Title</label>
    <input
        type="text"
        name="instagram_section_title"
        value="{{ $instagramSectionTitle }}"
        maxlength="120"
        placeholder="From Instagram"
        {{ $dbReady ? '' : 'disabled' }}
    >

    <div class="dolci-form-grid-2">
        <div>
            <label>Instagram Handle</label>
            <input
                type="text"
                name="instagram_handle"
                value="{{ $instagramHandle }}"
                maxlength="80"
                placeholder="@thedolci.jo"
                data-instagram-handle
                {{ $dbReady ? '' : 'disabled' }}
            >
            <p class="dolci-limited dolci-instagram-field-hint">Use the handle format: @username</p>
        </div>
        <div>
            <div class="dolci-admin-label-row">
                <label>Instagram Profile URL</label>
                <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-sync-profile {{ $dbReady ? '' : 'disabled' }}>
                    Use handle
                </button>
            </div>
            <input
                type="text"
                inputmode="url"
                name="instagram_profile_url"
                value="{{ $instagramProfileUrl }}"
                maxlength="2048"
                placeholder="https://www.instagram.com/thedolci.jo/"
                data-instagram-profile-url
                {{ $dbReady ? '' : 'disabled' }}
            >
        </div>
    </div>

    <div class="dolci-admin-section-head dolci-admin-subsection-head">
        <div>
            <h4>Instagram Posts</h4>
            <p class="dolci-limited dolci-instagram-admin-summary" data-instagram-summary aria-live="polite">Ready posts will appear on the storefront in this order.</p>
        </div>
        <div class="dolci-admin-instagram-actions">
            <span class="dolci-limited"><strong data-instagram-count>{{ count($instagramPosts) }}</strong> / {{ $maxInstagramPosts }}</span>
            <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-add {{ $dbReady ? '' : 'disabled' }}>
                Add Post
            </button>
        </div>
    </div>
    <p class="dolci-instagram-admin-warning" data-instagram-incomplete-warning aria-live="polite" hidden>Some posts are incomplete and will be ignored on save.</p>

    <div class="dolci-instagram-admin-list" data-instagram-list>
        @foreach($instagramPosts as $index => $post)
            @php($imageValue = old("instagram_posts.$index.image", $post['image'] ?? ''))
            @php($urlValue = old("instagram_posts.$index.url", $post['url'] ?? ''))
            @php($captionValue = old("instagram_posts.$index.caption", $post['caption'] ?? ''))
            @php($captionLength = mb_strlen(trim((string) $captionValue)))
            <article class="dolci-instagram-admin-item" data-instagram-item data-index="{{ $index }}">
                <div class="dolci-instagram-admin-item-head">
                    <div class="dolci-instagram-admin-item-meta">
                        <h4 data-instagram-item-title>Post {{ $index + 1 }}</h4>
                        <span class="dolci-instagram-admin-status" data-instagram-item-status>Ready</span>
                    </div>
                    <div class="dolci-instagram-admin-item-actions">
                        <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-move-up {{ $dbReady ? '' : 'disabled' }}>
                            Up
                        </button>
                        <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-move-down {{ $dbReady ? '' : 'disabled' }}>
                            Down
                        </button>
                        <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-duplicate {{ $dbReady ? '' : 'disabled' }}>
                            Duplicate
                        </button>
                        <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-remove {{ $dbReady ? '' : 'disabled' }}>
                            Remove
                        </button>
                    </div>
                </div>
                <p class="dolci-instagram-admin-missing" data-instagram-missing hidden>Missing fields: image URL, post URL, caption.</p>

                <div class="dolci-form-grid-2">
                    <div>
                        <label>Image URL</label>
                        <input
                            type="text"
                            inputmode="url"
                            name="instagram_posts[{{ $index }}][image]"
                            value="{{ $imageValue }}"
                            maxlength="2048"
                            placeholder="https://..."
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
                            <span class="dolci-instagram-admin-preview-error" data-instagram-image-error hidden>Could not load this image. Check the URL.</span>
                        </div>
                    </div>
                </div>

                <div class="dolci-admin-label-row">
                    <label>Post URL</label>
                    <a href="{{ $urlValue }}" target="_blank" rel="noopener" class="dolci-link-muted" data-instagram-open-post @if(trim((string) $urlValue) === '') hidden @endif>Open</a>
                </div>
                <input
                    type="text"
                    inputmode="url"
                    name="instagram_posts[{{ $index }}][url]"
                    value="{{ $urlValue }}"
                    maxlength="2048"
                    placeholder="https://www.instagram.com/p/..."
                    data-instagram-post-url
                    data-field="url"
                    {{ $dbReady ? '' : 'disabled' }}
                >

                <div class="dolci-admin-label-row">
                    <label>Caption</label>
                    <span class="dolci-limited"><span data-instagram-caption-count>{{ $captionLength }}</span> / 180</span>
                </div>
                <textarea
                    name="instagram_posts[{{ $index }}][caption]"
                    rows="2"
                    maxlength="180"
                    placeholder="Short caption for this post"
                    data-instagram-caption
                    data-field="caption"
                    {{ $dbReady ? '' : 'disabled' }}
                >{{ $captionValue }}</textarea>
            </article>
        @endforeach
    </div>

    <template data-instagram-template>
        <article class="dolci-instagram-admin-item" data-instagram-item data-index="__INDEX__">
            <div class="dolci-instagram-admin-item-head">
                <div class="dolci-instagram-admin-item-meta">
                    <h4 data-instagram-item-title>Post __NUMBER__</h4>
                    <span class="dolci-instagram-admin-status" data-instagram-item-status>Ready</span>
                </div>
                <div class="dolci-instagram-admin-item-actions">
                    <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-move-up {{ $dbReady ? '' : 'disabled' }}>
                        Up
                    </button>
                    <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-move-down {{ $dbReady ? '' : 'disabled' }}>
                        Down
                    </button>
                    <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-duplicate {{ $dbReady ? '' : 'disabled' }}>
                        Duplicate
                    </button>
                    <button type="button" class="dolci-btn dolci-btn-secondary dolci-btn-compact" data-instagram-remove {{ $dbReady ? '' : 'disabled' }}>
                        Remove
                    </button>
                </div>
            </div>
            <p class="dolci-instagram-admin-missing" data-instagram-missing hidden>Missing fields: image URL, post URL, caption.</p>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Image URL</label>
                    <input type="text" inputmode="url" name="instagram_posts[__INDEX__][image]" value="" maxlength="2048" placeholder="https://..." data-instagram-image data-field="image" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Image Preview</label>
                    <div class="dolci-instagram-admin-preview">
                        <img src="" alt="Instagram post preview" loading="lazy" data-instagram-preview hidden>
                        <span data-instagram-placeholder>Paste an image URL to preview.</span>
                        <span class="dolci-instagram-admin-preview-error" data-instagram-image-error hidden>Could not load this image. Check the URL.</span>
                    </div>
                </div>
            </div>

            <div class="dolci-admin-label-row">
                <label>Post URL</label>
                <a href="" target="_blank" rel="noopener" class="dolci-link-muted" data-instagram-open-post hidden>Open</a>
            </div>
            <input type="text" inputmode="url" name="instagram_posts[__INDEX__][url]" value="" maxlength="2048" placeholder="https://www.instagram.com/p/..." data-instagram-post-url data-field="url" {{ $dbReady ? '' : 'disabled' }}>

            <div class="dolci-admin-label-row">
                <label>Caption</label>
                <span class="dolci-limited"><span data-instagram-caption-count>0</span> / 180</span>
            </div>
            <textarea name="instagram_posts[__INDEX__][caption]" rows="2" maxlength="180" placeholder="Short caption for this post" data-instagram-caption data-field="caption" {{ $dbReady ? '' : 'disabled' }}></textarea>
        </article>
    </template>
</section>
