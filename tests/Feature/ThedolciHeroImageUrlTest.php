<?php

namespace Tests\Feature;

use App\Models\ThedolciStorefrontSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThedolciHeroImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_legacy_localhost_storage_urls_for_hero_image(): void
    {
        ThedolciStorefrontSetting::query()->create([
            'hero_image_url' => 'http://127.0.0.1:8000/storage/thedolci/hero/legacy-hero.png',
        ]);

        $hero = ThedolciStorefrontSetting::heroContent();

        $this->assertStringContainsString('/storage/thedolci/hero/legacy-hero.png', $hero['image_url']);
        $this->assertStringNotContainsString('127.0.0.1:8000', $hero['image_url']);
    }

    public function test_storage_route_serves_public_disk_files_without_storage_symlink(): void
    {
        Storage::disk('public')->put('thedolci/hero/fallback-check.txt', 'hero-fallback-content');

        $response = $this->get('/storage/thedolci/hero/fallback-check.txt');

        $response->assertOk();
    }

    public function test_storefront_update_keeps_existing_hero_image_when_no_new_file_is_uploaded(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        ThedolciStorefrontSetting::query()->create([
            'hero_image_url' => '/storage/thedolci/hero/existing-hero.png',
            'hero_title' => 'Original title',
        ]);

        $response = $this->actingAs($admin)->put(route('thedolci.admin.storefront.update'), [
            'hero_title' => 'Updated title',
        ]);

        $response->assertRedirect(route('thedolci.admin.storefront.edit'));

        $this->assertSame(
            '/storage/thedolci/hero/existing-hero.png',
            ThedolciStorefrontSetting::query()->firstOrFail()->hero_image_url
        );
    }

    public function test_it_reads_instagram_content_from_storefront_settings(): void
    {
        ThedolciStorefrontSetting::query()->create([
            'instagram_section_title' => 'Our Moments',
            'instagram_handle' => '@customdolci',
            'instagram_profile_url' => 'https://www.instagram.com/customdolci/',
            'instagram_posts' => [
                [
                    'image' => 'https://images.example.com/post-1.jpg',
                    'url' => 'https://www.instagram.com/p/custom-1/',
                    'caption' => 'Post one',
                ],
                [
                    'image' => '',
                    'url' => 'https://www.instagram.com/p/custom-2/',
                    'caption' => 'Invalid post should be ignored',
                ],
            ],
        ]);

        $instagram = ThedolciStorefrontSetting::instagramContent();

        $this->assertSame('Our Moments', $instagram['section_title']);
        $this->assertSame('@customdolci', $instagram['handle']);
        $this->assertSame('https://www.instagram.com/customdolci/', $instagram['profile_url']);
        $this->assertCount(1, $instagram['posts']);
        $this->assertSame('Post one', $instagram['posts'][0]['caption']);
    }

    public function test_storefront_update_persists_instagram_section_data(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('thedolci.admin.storefront.update'), [
            'instagram_section_title' => 'From Our Feed',
            'instagram_handle' => '@newhandle',
            'instagram_profile_url' => 'https://www.instagram.com/newhandle/',
            'instagram_posts' => [
                [
                    'image' => 'https://images.example.com/ig-1.jpg',
                    'url' => 'https://www.instagram.com/p/new-1/',
                    'caption' => 'First caption',
                ],
                [
                    'image' => '',
                    'url' => 'https://www.instagram.com/p/new-2/',
                    'caption' => 'Second caption',
                ],
            ],
        ]);

        $response->assertRedirect(route('thedolci.admin.storefront.edit'));

        $settings = ThedolciStorefrontSetting::query()->firstOrFail();

        $this->assertSame('From Our Feed', $settings->instagram_section_title);
        $this->assertSame('@newhandle', $settings->instagram_handle);
        $this->assertSame('https://www.instagram.com/newhandle/', $settings->instagram_profile_url);
        $this->assertIsArray($settings->instagram_posts);
        $this->assertCount(1, $settings->instagram_posts);
        $this->assertSame('First caption', $settings->instagram_posts[0]['caption']);
    }
}
