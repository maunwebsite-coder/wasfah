<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_loads_correctly()
    {
        Recipe::factory()->count(5)->create(['status' => 'approved', 'visibility' => 'public']);

        $response = $this->get(route('recipes'));

        $response->assertStatus(200);
        $response->assertViewHas('recipes');
    }

    public function test_feed_filtering_by_category()
    {
        $category1 = Category::factory()->create(['name' => 'Breakfast']);
        $category2 = Category::factory()->create(['name' => 'Dinner']);

        Recipe::factory()->create([
            'category_id' => $category1->id,
            'status' => 'approved',
            'visibility' => 'public',
            'title' => 'Pancakes'
        ]);

        Recipe::factory()->create([
            'category_id' => $category2->id,
            'status' => 'approved',
            'visibility' => 'public',
            'title' => 'Steak'
        ]);

        $response = $this->get(route('recipes', ['category' => $category1->id]));

        $response->assertStatus(200);
        $response->assertSee('Pancakes');
        $response->assertDontSee('Steak');
    }

    public function test_feed_sorting_by_trending()
    {
        $recipe1 = Recipe::factory()->create(['status' => 'approved', 'visibility' => 'public', 'title' => 'Popular']);
        $recipe2 = Recipe::factory()->create(['status' => 'approved', 'visibility' => 'public', 'title' => 'Unpopular']);

        // Simulate interactions for recipe1
        $user = User::factory()->create();
        $recipe1->interactions()->create([
            'user_id' => $user->id,
            'is_saved' => true,
            'created_at' => now()
        ]);

        $response = $this->get(route('recipes', ['sort' => 'trending']));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Popular', 'Unpopular']);
    }
}
