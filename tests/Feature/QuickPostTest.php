<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuickPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_quick_post_with_image()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $imagePath = base_path('tests/fixtures/quick-post.png');
        $file = new UploadedFile($imagePath, 'test.png', 'image/png', null, true);

        $response = $this->actingAs($user)->postJson(route('feed.quick-post'), [
            'type' => 'photo',
            'description' => 'My awesome photo',
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post created successfully']);

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'description' => 'My awesome photo',
            'title' => 'New Photo',
            'status' => 'approved',
        ]);

        Storage::disk('public')->assertExists('recipes/' . $file->hashName());
    }

    public function test_quick_post_validation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('feed.quick-post'), [
            'type' => 'photo',
            // Missing image and description
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }
}
