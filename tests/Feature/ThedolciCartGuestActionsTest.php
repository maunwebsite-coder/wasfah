<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_guest_can_update_packaging_with_post_route(): void
    {
        $key = $this->addDefaultItemToCart('Classic Box');

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.post', $key), [
            'action' => 'update-packaging',
            'packaging_type' => 'Gift Bag',
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $updatedKey = array_key_first($cart);

        $this->assertIsString($updatedKey);
        $this->assertSame('Gift Bag', (string) data_get($cart, $updatedKey . '.customizations.packaging_type'));
        $this->assertSame(2.0, (float) data_get($cart, $updatedKey . '.customizations.packaging_price'));
        $this->assertSame(23.9, (float) data_get($cart, $updatedKey . '.unit_price'));
    }

    public function test_guest_can_remove_packaging_with_post_route(): void
    {
        $key = $this->addDefaultItemToCart('Gift Bag');

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.post', $key), [
            'action' => 'update-packaging',
            'packaging_type' => '',
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $updatedKey = array_key_first($cart);

        $this->assertIsString($updatedKey);
        $this->assertSame('', (string) data_get($cart, $updatedKey . '.customizations.packaging_type'));
        $this->assertSame(0.0, (float) data_get($cart, $updatedKey . '.customizations.packaging_price'));
        $this->assertSame(21.9, (float) data_get($cart, $updatedKey . '.unit_price'));
    }

    public function test_guest_cannot_apply_coupon_from_cart_page(): void
    {
        $this->addDefaultItemToCart();

        $response = $this->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'first10',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertNull(session(ThedolciCart::COUPON_KEY));
    }

    public function test_admin_can_apply_coupon_from_cart_page(): void
    {
        $this->addDefaultItemToCart();

        $admin = new User();
        $admin->forceFill([
            'id' => 999001,
            'name' => 'Admin User',
            'email' => 'admin-cart@example.com',
            'is_admin' => true,
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->from(route('thedolci.cart'))->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'first10',
        ]);

        $response->assertRedirect(route('thedolci.cart'));
        $this->assertSame('FIRST10', session(ThedolciCart::COUPON_KEY . '.code'));
        $this->assertSame(2.19, (float) session(ThedolciCart::COUPON_KEY . '.discount'));
    }

    private function addDefaultItemToCart(string $packagingType = 'Classic Box'): string
    {
        $response = $this->post(route('thedolci.cart.add'), [
            'slug' => 'classic-tiramisu',
            'size' => 'Large',
            'quantity' => 1,
            'packaging_type' => $packagingType,
        ]);

        $response->assertRedirect(route('thedolci.cart'));

        $cart = session(ThedolciCart::CART_KEY, []);
        $key = array_key_first($cart);

        $this->assertIsString($key);

        return $key;
    }
}
