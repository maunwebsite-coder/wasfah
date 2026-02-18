<?php

namespace Tests\Feature;

use App\Models\ThedolciOrder;
use App\Models\ThedolciProduct;
use App\Support\ThedolciCart;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThedolciCartInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_cart_add_clamps_quantity_to_remaining_limited_stock(): void
    {
        $this->createLimitedProduct('limited-stock-clamp', 5);
        $this->createOrderForProduct('limited-stock-clamp', 4);

        $response = $this->post(route('thedolci.cart.add'), [
            'slug' => 'limited-stock-clamp',
            'size' => 'Small',
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('thedolci.cart'));
        $response->assertSessionHas('warning');

        $cart = session(ThedolciCart::CART_KEY, []);
        $key = array_key_first($cart);

        $this->assertIsString($key);
        $this->assertSame(1, (int) data_get($cart, $key . '.quantity'));
    }

    public function test_cart_add_fails_when_limited_item_is_fully_sold_out(): void
    {
        $this->createLimitedProduct('limited-stock-soldout', 2);
        $this->createOrderForProduct('limited-stock-soldout', 2);

        $response = $this->from(route('thedolci.shop'))->post(route('thedolci.cart.add'), [
            'slug' => 'limited-stock-soldout',
            'size' => 'Small',
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('thedolci.shop'));
        $response->assertSessionHasErrors('product');
        $this->assertSame([], session(ThedolciCart::CART_KEY, []));
    }

    public function test_checkout_place_redirects_to_cart_when_inventory_changes_after_cart_add(): void
    {
        $this->createLimitedProduct('limited-stock-checkout', 3);

        $addResponse = $this->post(route('thedolci.cart.add'), [
            'slug' => 'limited-stock-checkout',
            'size' => 'Small',
            'quantity' => 2,
        ]);

        $addResponse->assertRedirect(route('thedolci.cart'));
        $this->createOrderForProduct('limited-stock-checkout', 3);

        $checkoutResponse = $this->post(route('thedolci.checkout.place'), [
            'customer_name' => 'Cart Tester',
            'customer_email' => 'cart-tester@example.com',
            'customer_phone' => '0790000000',
            'fulfillment_type' => 'pickup',
            'delivery_date' => now()->addDay()->format('Y-m-d'),
            'delivery_time' => '10:00 - 12:00',
        ]);

        $checkoutResponse->assertRedirect(route('thedolci.cart'));
        $checkoutResponse->assertSessionHasErrors('cart');
    }

    private function createLimitedProduct(string $slug, int $limitedQuantity): void
    {
        ThedolciProduct::query()->create([
            'slug' => $slug,
            'name' => 'Limited Product ' . $slug,
            'cover_image' => 'https://example.com/' . $slug . '.jpg',
            'size_prices' => ['Small' => 10.00],
            'packaging_options' => [
                ['name' => 'Classic Box', 'price' => 0],
            ],
            'limited_quantity' => $limitedQuantity,
            'is_active' => true,
        ]);
    }

    private function createOrderForProduct(string $slug, int $quantity): void
    {
        ThedolciOrder::query()->create([
            'order_number' => 'TDL-TST-' . Str::upper(Str::random(6)),
            'customer_name' => 'Inventory Customer',
            'customer_email' => 'inventory@example.com',
            'customer_phone' => '0799999999',
            'fulfillment_type' => 'pickup',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time' => '10:00 - 12:00',
            'coupon_code' => null,
            'discount_amount' => 0,
            'subtotal' => 10 * $quantity,
            'total' => 10 * $quantity,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'notes' => null,
            'items' => [
                [
                    'slug' => $slug,
                    'name' => 'Limited Product ' . $slug,
                    'size' => 'Small',
                    'quantity' => $quantity,
                    'unit_price' => 10.00,
                    'line_total' => round(10 * $quantity, 2),
                    'customizations' => [],
                ],
            ],
            'loyalty_points_earned' => 0,
        ]);
    }
}

