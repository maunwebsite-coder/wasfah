<?php

namespace Tests\Feature;

use App\Models\ThedolciOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThedolciTrackOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_orders_with_normalized_email_and_order_number(): void
    {
        ThedolciOrder::query()->create([
            'order_number' => 'TDL-20260215-ABCDE',
            'customer_name' => 'Sara',
            'customer_email' => 'Customer@Example.com',
            'customer_phone' => '0790000000',
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'Amman',
            'delivery_date' => '2026-02-16',
            'delivery_time' => '10:00 AM',
            'coupon_code' => null,
            'discount_amount' => 0,
            'subtotal' => 25,
            'total' => 25,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'new',
            'items' => [
                [
                    'slug' => 'cookies',
                    'name' => 'Cookies',
                    'size' => 'Box',
                    'quantity' => 1,
                    'unit_price' => 25,
                    'line_total' => 25,
                    'customizations' => [],
                ],
            ],
            'notes' => null,
            'loyalty_points_earned' => 2,
        ]);

        $response = $this->post(route('thedolci.track-order'), [
            'order_number' => ' tdl - ٢٠٢٦٠٢١٥ - abcde ',
            'customer_email' => ' customer@example.com ',
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Order TDL-20260215-ABCDE');
        $response->assertDontSeeText('No order found with the provided details.');
    }

    public function test_it_uses_session_fallback_when_order_is_not_in_database(): void
    {
        $this->withSession([
            'thedolci.fallback_orders' => [
                'TDL-20260216-ZYXWV' => [
                    'order_number' => 'TDL-20260216-ZYXWV',
                    'customer_name' => 'Lama',
                    'customer_email' => 'lama@example.com',
                    'customer_phone' => '0791111111',
                    'fulfillment_type' => 'pickup',
                    'delivery_date' => '2026-02-17',
                    'delivery_time' => '6:00 PM',
                    'payment_status' => 'pending',
                    'status' => 'confirmed',
                    'total' => 18.5,
                ],
            ],
        ]);

        $response = $this->post(route('thedolci.track-order'), [
            'order_number' => 'tdl-20260216-zyxwv',
            'customer_email' => 'LAMA@EXAMPLE.COM',
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Order TDL-20260216-ZYXWV');
        $response->assertSeeText('Status:');
    }
}
