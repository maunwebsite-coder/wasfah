<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class ThedolciCartCouponRouteTest extends TestCase
{
    public function test_get_cart_coupon_redirects_to_cart_page(): void
    {
        $response = $this->get('/cart/coupon');

        $response->assertRedirect(route('thedolci.cart'));
    }

    public function test_guest_post_cart_coupon_redirects_to_login(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $response = $this->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'NOT-VALID',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_admin_post_cart_coupon_still_hits_coupon_handler(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = new User();
        $admin->forceFill([
            'id' => 999002,
            'name' => 'Admin User',
            'email' => 'admin-route@example.com',
            'is_admin' => true,
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->from(route('thedolci.cart'))->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'NOT-VALID',
        ]);

        $response->assertRedirect(route('thedolci.cart'));
        $response->assertSessionHasErrors('coupon_code');
    }
}
