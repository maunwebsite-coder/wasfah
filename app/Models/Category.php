<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    // المفتاح الأساسي
    protected $primaryKey = 'category_id';

    // Laravel بافتراضه بيستخدم auto increment و integer، فممكن نثبتهم
    public $incrementing = true;
    protected $keyType = 'int';

    // الأعمدة اللي مسموح تتعبى
    protected $fillable = ['name', 'slug', 'image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = $category->generateSlug();
            }

            if ($category->is_active === null) {
                $category->is_active = true;
            }
        });

        static::updating(function (self $category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = $category->generateSlug();
            }
        });
    }

    public function generateSlug(): string
    {
        $baseSlug = Str::slug((string) $this->name, '-', 'ar');
        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($this->getKey(), fn ($query, $id) => $query->where($this->primaryKey, '!=', $id))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Allow accessing the primary key via `$category->id` for convenience.
     */
    public function getIdAttribute()
    {
        return $this->getAttribute($this->primaryKey);
    }

    // العلاقة مع الوصفات
    public function recipes()
    {
        // لازم تحدد المفتاحين (foreign key و local key) عشان ما يدور على id
        return $this->hasMany(Recipe::class, 'category_id', 'category_id');
    }
}
