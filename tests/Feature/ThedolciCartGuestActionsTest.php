<?php

namespace Tests\Feature;

use App\Support\ThedolciCart;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class ThedolciCartGuestActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_can_update_cart_quantity_with_post_route(): void
    {
        $key = $this->addDefaultItemToCart();

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.post', $key), [
            'action' => 'update',
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $this->assertSame(3, (int) data_get($cart, $key . '.quantity'));
    }

    public function test_guest_can_remove_cart_item_with_post_route(): void
    {
        $key = $this->addDefaultItemToCart();

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.post', $key), [
            'action' => 'remove',
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $this->assertArrayNotHasKey($key, $cart);
    }

    public function test_guest_can_apply_coupon_from_cart_page(): void
    {
        $this->addDefaultItemToCart();

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'first10',
        ]);

        $response->assertRedirect(route('thedolci.cart'));
        $this->assertSame('FIRST10', session(ThedolciCart::COUPON_KEY . '.code'));
        $this->assertSame(2.19, (float) session(ThedolciCart::COUPON_KEY . '.discount'));
    }

    private function addDefaultItemToCart(): string
    {
        $response = $this->post(route('thedolci.cart.add'), [
            'slug' => 'classic-tiramisu',
            'size' => 'Large',
            'quantity' => 1,
            'packaging_type' => 'Classic Box',
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $key = array_key_first($cart);

        $this->assertIsString($key);

        return $key;
    }
}
