<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class ThedolciCartCouponRouteTest extends TestCase
{
    public function test_get_cart_coupon_redirects_to_cart_page(): void
    {
        $response = $this->get('/cart/coupon');

        $response->assertRedirect(route('thedolci.cart'));
    }

    public function test_post_cart_coupon_still_hits_coupon_handler(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $response = $this->from(route('thedolci.cart'))->post(route('thedolci.cart.coupon'), [
            'coupon_code' => 'NOT-VALID',
        ]);

        $response->assertRedirect(route('thedolci.cart'));
        $response->assertSessionHasErrors('coupon_code');
    }
}
