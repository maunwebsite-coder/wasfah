<?php

namespace Tests\Feature;

use App\Models\ThedolciProduct;
use App\Models\User;
use App\Support\ThedolciCart;
use App\Support\ThedolciCatalog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThedolciPackagingOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_catalog_normalizes_legacy_packaging_name_that_contains_trailing_decimal_price(): void
    {
        ThedolciProduct::query()->create([
            'slug' => 'legacy-packaging-format',
            'name' => 'Legacy Packaging Format',
            'cover_image' => 'https://example.com/cover.jpg',
            'size_prices' => ['Small' => 10.00],
            'packaging_options' => [
                ['name' => 'Classic Box', 'price' => 0],
                ['name' => 'Premium Box3.00', 'price' => 0],
            ],
            'is_active' => true,
        ]);

        $catalogProduct = ThedolciCatalog::findProduct('legacy-packaging-format');

        $this->assertIsArray($catalogProduct);

        $premiumOption = collect($catalogProduct['packaging_options'] ?? [])->firstWhere('name', 'Premium Box');
        $this->assertIsArray($premiumOption);
        $this->assertSame(3.0, (float) ($premiumOption['price'] ?? 0));
    }

    public function test_admin_update_parses_packaging_line_without_pipe_when_price_is_appended(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $product = ThedolciProduct::query()->create([
            'slug' => 'packaging-admin-parse',
            'name' => 'Packaging Admin Parse',
            'cover_image' => 'https://example.com/cover.jpg',
            'size_prices' => ['Small' => 10.00, 'Medium' => 15.00],
            'packaging_options' => [],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('thedolci.admin.products.update', $product), [
            'slug' => 'packaging-admin-parse',
            'name' => 'Packaging Admin Parse',
            'headline' => 'Headline',
            'description' => 'Description',
            'story' => 'Story',
            'cover_image' => 'https://example.com/cover.jpg',
            'gallery_images' => '',
            'size_prices_input' => "Small|10\nMedium|15",
            'pepper_price' => 0,
            'packaging_options_input' => "Classic Box|0\nPremium Box3.00",
            'sort_order' => 0,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('thedolci.admin.products.index'));

        $product->refresh();
        $premiumOption = collect($product->packaging_options ?? [])->firstWhere('name', 'Premium Box');

        $this->assertIsArray($premiumOption);
        $this->assertSame(3.0, (float) ($premiumOption['price'] ?? 0));
    }

    public function test_cart_add_applies_extracted_price_from_legacy_packaging_name(): void
    {
        ThedolciProduct::query()->create([
            'slug' => 'legacy-packaging-cart',
            'name' => 'Legacy Packaging Cart',
            'cover_image' => 'https://example.com/cover.jpg',
            'size_prices' => ['Small' => 10.00],
            'packaging_options' => [
                ['name' => 'Classic Box', 'price' => 0],
                ['name' => 'Premium Box3.00', 'price' => 0],
            ],
            'is_active' => true,
        ]);

        $response = $this->post(route('thedolci.cart.add'), [
            'slug' => 'legacy-packaging-cart',
            'size' => 'Small',
            'quantity' => 1,
            'packaging_type' => 'Premium Box',
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $key = array_key_first($cart);

        $this->assertIsString($key);
        $this->assertSame(13.0, (float) data_get($cart, $key . '.unit_price'));
        $this->assertSame(3.0, (float) data_get($cart, $key . '.customizations.packaging_price'));
        $this->assertSame('Premium Box', (string) data_get($cart, $key . '.customizations.packaging_type'));
    }
}
