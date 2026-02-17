<?php

namespace Tests\Feature;

use App\Models\ThedolciProduct;
use App\Models\User;
use App\Support\ThedolciCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThedolciProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_normalizes_legacy_localhost_cover_image_urls(): void
    {
        ThedolciProduct::query()->create([
            'slug' => 'legacy-cover-product',
            'name' => 'Legacy Cover Product',
            'cover_image' => 'http://127.0.0.1:8000/storage/thedolci/products/legacy-cover.png',
            'size_prices' => ['Small' => 10.00, 'Medium' => 15.00],
            'is_active' => true,
        ]);

        $catalogProduct = ThedolciCatalog::products(true)->firstWhere('slug', 'legacy-cover-product');

        $this->assertIsArray($catalogProduct);
        $this->assertSame('/storage/thedolci/products/legacy-cover.png', $catalogProduct['cover_image']);
    }

    public function test_catalog_excludes_cover_image_from_gallery_images(): void
    {
        ThedolciProduct::query()->create([
            'slug' => 'gallery-dedup-product',
            'name' => 'Gallery Dedup Product',
            'cover_image' => '/storage/thedolci/products/cover.png',
            'gallery_images' => [
                '/storage/thedolci/products/cover.png',
                '/storage/thedolci/products/detail-1.png',
                '/storage/thedolci/products/detail-1.png',
            ],
            'size_prices' => ['Small' => 10.00, 'Medium' => 15.00],
            'is_active' => true,
        ]);

        $catalogProduct = ThedolciCatalog::products(true)->firstWhere('slug', 'gallery-dedup-product');

        $this->assertIsArray($catalogProduct);
        $this->assertSame('/storage/thedolci/products/cover.png', $catalogProduct['cover_image']);
        $this->assertSame(['/storage/thedolci/products/detail-1.png'], $catalogProduct['gallery_images']);
    }

    public function test_admin_can_save_local_cover_image_path_for_single_product(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $product = ThedolciProduct::query()->create([
            'slug' => 'single-product-local-image',
            'name' => 'Single Product Local Image',
            'cover_image' => 'https://example.com/original-cover.jpg',
            'size_prices' => ['Small' => 10.00, 'Medium' => 15.00],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('thedolci.admin.products.update', $product), [
            'slug' => 'single-product-local-image',
            'name' => 'Single Product Local Image',
            'headline' => 'Updated headline',
            'description' => 'Updated description',
            'story' => 'Updated story',
            'cover_image' => '/storage/thedolci/products/new-cover.jpg',
            'gallery_images' => '',
            'size_prices_input' => "Small|10\nMedium|15",
            'pepper_price' => 0,
            'packaging_options_input' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('thedolci.admin.products.index'));

        $this->assertSame(
            '/storage/thedolci/products/new-cover.jpg',
            ThedolciProduct::query()->firstOrFail()->cover_image
        );
    }

    public function test_admin_can_upload_local_cover_image_file_for_single_product(): void
    {
        Storage::fake('public');

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('thedolci.admin.products.store'), [
            'slug' => 'single-product-uploaded-image',
            'name' => 'Single Product Uploaded Image',
            'headline' => 'Upload headline',
            'description' => 'Upload description',
            'story' => 'Upload story',
            'cover_image_file' => UploadedFile::fake()->image('cover.jpg', 1200, 1200),
            'gallery_images' => '',
            'size_prices_input' => "Small|8.5\nMedium|12.0",
            'pepper_price' => 0,
            'packaging_options_input' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('thedolci.admin.products.index'));

        $product = ThedolciProduct::query()->firstOrFail();

        $this->assertTrue(Str::startsWith($product->cover_image, '/storage/thedolci/products/'));

        $storedPath = Str::replaceFirst('/storage/', '', $product->cover_image);
        Storage::disk('public')->assertExists($storedPath);
    }
}
