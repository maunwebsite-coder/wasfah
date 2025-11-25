<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'author' => $this->faker->name(),
            'image_url' => $this->faker->imageUrl(),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'status' => 'approved',
            'visibility' => 'public',
            'prep_time' => $this->faker->numberBetween(10, 120),
            'cook_time' => $this->faker->numberBetween(10, 120),
            'servings' => $this->faker->numberBetween(1, 10),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'steps' => json_encode(['Step 1', 'Step 2']),
            'tools' => json_encode([]),
            'approved_at' => now(),
        ];
    }
}
