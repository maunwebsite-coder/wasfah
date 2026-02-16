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
}
