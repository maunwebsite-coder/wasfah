<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            if (! Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('image');
            }
        });

        $this->backfillSlugs();
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('categories', 'image')) {
                $table->dropColumn('image');
            }

            if (Schema::hasColumn('categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    private function backfillSlugs(): void
    {
        if (! Schema::hasColumn('categories', 'slug')) {
            return;
        }

        $categories = DB::table('categories')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->get(['category_id', 'name']);

        foreach ($categories as $category) {
            $baseSlug = Str::slug((string) $category->name, '-', 'ar');
            if ($baseSlug === '') {
                $baseSlug = 'category-' . $category->category_id;
            }

            $slug = $baseSlug;
            $suffix = 1;

            while (
                DB::table('categories')
                    ->where('slug', $slug)
                    ->where('category_id', '!=', $category->category_id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            DB::table('categories')
                ->where('category_id', $category->category_id)
                ->update(['slug' => $slug]);
        }
    }
};
