@extends('thedolci.layouts.admin')

@section('title', 'Storefront Content | thedolci Admin')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>Storefront Content</h1>
            <a href="{{ route('home') }}" class="dolci-btn dolci-btn-secondary" target="_blank" rel="noopener">Preview Store</a>
        </div>

        @if(!$dbReady)
            <div class="dolci-alert dolci-alert-error">Database table is not ready. Run migrations first.</div>
        @endif

        <form method="POST" action="{{ route('thedolci.admin.storefront.update') }}" class="dolci-form-card" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <p class="dolci-limited">You can leave any field empty to use the default storefront content.</p>

            <h3>Main Copy</h3>
            <label>Hero Kicker</label>
            <input type="text" name="hero_kicker" value="{{ old('hero_kicker', $hero['kicker'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

            <label>Hero Title</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $hero['title'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

            <label>Hero Description</label>
            <textarea name="hero_description" rows="4" {{ $dbReady ? '' : 'disabled' }}>{{ old('hero_description', $hero['description'] ?? '') }}</textarea>

            <div class="dolci-form-grid-2">
                <div>
                    <label>Primary Button Text</label>
                    <input type="text" name="hero_primary_button_text" value="{{ old('hero_primary_button_text', $hero['primary_button_text'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Secondary Button Text</label>
                    <input type="text" name="hero_secondary_button_text" value="{{ old('hero_secondary_button_text', $hero['secondary_button_text'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
            </div>

            <h3>Metrics</h3>
            <div class="dolci-form-grid-2">
                <div>
                    <label>Metric 1 Title</label>
                    <input type="text" name="hero_metric_1_title" value="{{ old('hero_metric_1_title', $hero['metric_1_title'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Metric 1 Subtitle</label>
                    <input type="text" name="hero_metric_1_subtitle" value="{{ old('hero_metric_1_subtitle', $hero['metric_1_subtitle'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
            </div>
            <div class="dolci-form-grid-2">
                <div>
                    <label>Metric 2 Title</label>
                    <input type="text" name="hero_metric_2_title" value="{{ old('hero_metric_2_title', $hero['metric_2_title'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Metric 2 Subtitle</label>
                    <input type="text" name="hero_metric_2_subtitle" value="{{ old('hero_metric_2_subtitle', $hero['metric_2_subtitle'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
            </div>
            <div class="dolci-form-grid-2">
                <div>
                    <label>Metric 3 Title</label>
                    <input type="text" name="hero_metric_3_title" value="{{ old('hero_metric_3_title', $hero['metric_3_title'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
                <div>
                    <label>Metric 3 Subtitle</label>
                    <input type="text" name="hero_metric_3_subtitle" value="{{ old('hero_metric_3_subtitle', $hero['metric_3_subtitle'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
                </div>
            </div>

            <h3>Hero Image</h3>
            <label>Upload Image</label>
            <input type="file" name="hero_image_file" accept=".jpg,.jpeg,.png,.webp,.avif,image/*" {{ $dbReady ? '' : 'disabled' }}>
            <p class="dolci-limited">Leave this empty to keep the current hero image.</p>

            <label>Hero Image Alt Text</label>
            <input type="text" name="hero_image_alt" value="{{ old('hero_image_alt', $hero['image_alt'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

            @if(!empty($hero['image_url']))
                <p class="dolci-limited">Current hero image preview:</p>
                <img src="{{ $hero['image_url'] }}" alt="{{ $hero['image_alt'] ?? 'Hero image' }}" class="dolci-rounded-img" loading="lazy">
            @endif

            @php
                $instagramPosts = old('instagram_posts', $instagram['posts'] ?? []);
                if (! is_array($instagramPosts)) {
                    $instagramPosts = [];
                }
                $instagramPosts = array_values($instagramPosts);
                $minimumInstagramPosts = 6;

                while (count($instagramPosts) < $minimumInstagramPosts) {
                    $instagramPosts[] = ['image' => '', 'url' => '', 'caption' => ''];
                }
            @endphp

            <h3>Instagram Section</h3>
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

            @foreach($instagramPosts as $index => $post)
                <h3>Instagram Post {{ $index + 1 }}</h3>
                <label>Image URL</label>
                <input type="text" name="instagram_posts[{{ $index }}][image]" value="{{ old("instagram_posts.$index.image", $post['image'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

                <label>Post URL</label>
                <input type="text" name="instagram_posts[{{ $index }}][url]" value="{{ old("instagram_posts.$index.url", $post['url'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>

                <label>Caption</label>
                <input type="text" name="instagram_posts[{{ $index }}][caption]" value="{{ old("instagram_posts.$index.caption", $post['caption'] ?? '') }}" {{ $dbReady ? '' : 'disabled' }}>
            @endforeach

            <button type="submit" class="dolci-btn dolci-btn-primary" {{ $dbReady ? '' : 'disabled' }}>Save Storefront Content</button>
        </form>
    </div>
</section>
@endsection

