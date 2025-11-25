<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            // IDs will be auto-incremented starting from 1
            $this->buildCategory('حلويات'),        // Corresponds to category_id: 1
            $this->buildCategory('سلطات'),         // Corresponds to category_id: 2
            $this->buildCategory('سناكات صحية'),   // Corresponds to category_id: 3
            $this->buildCategory('أطباق رئيسية'),  // Corresponds to category_id: 4
            $this->buildCategory('شوربات'),        // Corresponds to category_id: 5
        ]);
    }

    protected function buildCategory(string $name): array
    {
        $now = now();

        return [
            'name' => $name,
            'slug' => Str::slug($name, '-', 'ar'),
            'image' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
