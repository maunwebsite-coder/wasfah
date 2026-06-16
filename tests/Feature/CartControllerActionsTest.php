<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_cart_item_quantity(): void
    {
        $admin = $this->makeAdmin();
        $tool = $this->makeTool(['price' => 12.50]);

        $cart = Cart::create([
            'user_id' => $admin->id,
            'session_id' => null,
            'tool_id' => $tool->id,
            'quantity' => 1,
            'price' => $tool->price,
            'amazon_url' => $tool->amazon_url,
            'affiliate_url' => $tool->affiliate_url,
        ]);

        $response = $this->actingAs($admin)->patchJson(route('cart.update', $cart), [
            'quantity' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cart_count', 4)
            ->assertJsonPath('item_total', 50.0)
            ->assertJsonPath('cart_total', 50.0);

        $this->assertSame(4, $cart->fresh()->quantity);
    }

    public function test_admin_can_remove_cart_item(): void
    {
        $admin = $this->makeAdmin();
        $toolOne = $this->makeTool(['name' => 'Tool One', 'price' => 20.00]);
        $toolTwo = $this->makeTool(['name' => 'Tool Two', 'price' => 10.00]);

        $toRemove = Cart::create([
            'user_id' => $admin->id,
            'session_id' => null,
            'tool_id' => $toolOne->id,
            'quantity' => 2,
            'price' => $toolOne->price,
            'amazon_url' => $toolOne->amazon_url,
            'affiliate_url' => $toolOne->affiliate_url,
        ]);

        Cart::create([
            'user_id' => $admin->id,
            'session_id' => null,
            'tool_id' => $toolTwo->id,
            'quantity' => 1,
            'price' => $toolTwo->price,
            'amazon_url' => $toolTwo->amazon_url,
            'affiliate_url' => $toolTwo->affiliate_url,
        ]);

        $response = $this->actingAs($admin)->deleteJson(route('cart.remove', $toRemove));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cart_count', 1)
            ->assertJsonPath('cart_total', 10.0);

        $this->assertDatabaseMissing('carts', ['id' => $toRemove->id]);
    }

    public function test_admin_cannot_update_or_remove_another_users_cart_item(): void
    {
        $owner = $this->makeAdmin();
        $intruder = $this->makeAdmin(['email' => 'intruder@example.com']);
        $tool = $this->makeTool(['price' => 15.00]);

        $cart = Cart::create([
            'user_id' => $owner->id,
            'session_id' => null,
            'tool_id' => $tool->id,
            'quantity' => 2,
            'price' => $tool->price,
            'amazon_url' => $tool->amazon_url,
            'affiliate_url' => $tool->affiliate_url,
        ]);

        $updateResponse = $this->actingAs($intruder)->patchJson(route('cart.update', $cart), [
            'quantity' => 5,
        ]);

        $updateResponse->assertNotFound()
            ->assertJsonPath('success', false);

        $deleteResponse = $this->actingAs($intruder)->deleteJson(route('cart.remove', $cart));

        $deleteResponse->assertNotFound()
            ->assertJsonPath('success', false);

        $this->assertSame(2, $cart->fresh()->quantity);
        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
    }

    private function makeAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => true,
            'role' => User::ROLE_ADMIN,
        ], $overrides));
    }

    private function makeTool(array $overrides = []): Tool
    {
        return Tool::create(array_merge([
            'name' => 'Test Tool ' . uniqid('', true),
            'description' => 'Tool description',
            'image' => null,
            'gallery_images' => [],
            'amazon_url' => 'https://www.amazon.ae/dp/B08N5WRWNW',
            'affiliate_url' => 'https://example.com/affiliate/tool',
            'price' => 9.99,
            'category' => 'Kitchen',
            'rating' => 4.5,
            'features' => ['Durable', 'Easy to clean'],
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }
}
