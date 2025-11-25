<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Recipe extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * Custom primary key configuration.
     */
    protected $primaryKey = 'recipe_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'author',
        'image_url',
        'image',
        'image_2',
        'image_3',
        'image_4',
        'image_5',
        'video_url',
        'category_id',
        'user_id',
        'status',
        'visibility',
        'approved_at',
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'steps',
        'tools',
        'registration_deadline',
    ];

    /**
     * Attribute casting definitions.
     */
    protected $casts = [
        'steps' => 'array',
        'tools' => 'array',
        'registration_deadline' => 'datetime',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
        'approved_at' => 'datetime',
        'visibility' => 'string',
    ];

    /**
     * Moderation status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Visibility constants.
     */
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    /**
     * Cached visibility column detection.
     */
    protected static bool $visibilityColumnChecked = false;
    protected static bool $visibilityColumnExists = false;

    protected static bool $statusColumnChecked = false;
    protected static bool $statusColumnExists = false;

    protected static bool $descriptionFullTextChecked = false;
    protected static bool $descriptionFullTextExists = false;

    /**
     * العلاقات
     */

    // العلاقة مع التصنيف
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // العلاقة مع الشيف (صاحب الوصفة)
    public function chef()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // العلاقة مع المكونات
    public function ingredients()
    {
        return $this->hasMany(Ingredient::class, 'recipe_id', 'recipe_id');
    }

    // العلاقة مع التفاعلات (تقييمات، إعجابات...)
    public function interactions()
    {
        return $this->hasMany(UserInteraction::class, 'recipe_id', 'recipe_id');
    }

    // العلاقة مع الورشات (many-to-many)
    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'workshop_recipes', 'recipe_id', 'workshop_id')
                    ->withPivot('order')
                    ->orderBy('workshop_recipes.order');
    }

    /**
     * الحصول على رابط الصورة
     */
    public function getImageUrlAttribute()
    {
        // إذا كان هناك رابط صورة خارجي (مثل Unsplash)
        if (isset($this->attributes['image_url']) && $this->attributes['image_url']) {
            // تحقق إذا كان الرابط خارجي (يبدأ بـ http)
            if (str_starts_with($this->attributes['image_url'], 'http')) {
                return $this->attributes['image_url'];
            }
            // إذا كان الرابط محلي، أضف storage path
            return static::publicUrl($this->attributes['image_url']);
        }

        // إذا كان هناك ملف صورة محفوظ محلياً
        if (isset($this->attributes['image']) && $this->attributes['image']) {
            return static::publicUrl($this->attributes['image']);
        }

        return null;
    }

    /**
     * الحصول على جميع الصور
     */
    public function getAllImages()
    {
        $images = [];

        // إضافة الصورة الرئيسية
        if ($this->image) {
            $images[] = static::publicUrl($this->image);
        }

        // إضافة الصور الإضافية
        for ($i = 2; $i <= 5; $i++) {
            $imageField = "image_{$i}";
            if ($this->$imageField) {
                $images[] = \Storage::disk('public')->url($this->$imageField);
            }
        }

        return $images;
    }

    /**
     * الحصول على رابط صورة محددة
     */
    public function getImageUrl($imageNumber = 1)
    {
        if ($imageNumber == 1) {
            return $this->getImageUrlAttribute();
        }

        $imageField = "image_{$imageNumber}";
        if (isset($this->attributes[$imageField]) && $this->attributes[$imageField]) {
            return static::publicUrl($this->attributes[$imageField]);
        }

        return null;
    }

    /**
     * التحقق من انتهاء مهلة الحجز
     */
    public function getIsRegistrationOpenAttribute()
    {
        if (!$this->registration_deadline) {
            return true; // إذا لم يتم تحديد موعد انتهاء، الوصفة متاحة دائماً
        }

        return now() < $this->registration_deadline;
    }

    /**
     * التحقق من انتهاء مهلة الحجز (للاستخدام في Blade)
     */
    public function getIsRegistrationClosedAttribute()
    {
        return !$this->is_registration_open;
    }

    /**
     * Generate slug from title
     */
    public function generateSlug()
    {
        $slug = \Str::slug($this->title, '-', 'ar');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('recipe_id', '!=', $this->recipe_id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot method to auto-generate slug and default status
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            if (empty($recipe->slug)) {
                $recipe->slug = $recipe->generateSlug();
            }

            if (static::statusColumnExists() && empty($recipe->status)) {
                $recipe->status = self::STATUS_DRAFT;
            }

            if (static::visibilityColumnExists() && empty($recipe->visibility)) {
                $recipe->visibility = self::VISIBILITY_PUBLIC;
            }

            if (empty($recipe->author)) {
                $recipe->author = $recipe->chef->name ?? 'Community Member';
            }
        });

        static::updating(function ($recipe) {
            if ($recipe->isDirty('title') && empty($recipe->slug)) {
                $recipe->slug = $recipe->generateSlug();
            }
        });
    }

    /**
     * Scope to only approved recipes.
     */
    public function scopeApproved($query)
    {
        if (!static::statusColumnExists()) {
            return $query;
        }

        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to recipes that are publicly visible.
     */
    public function scopePublic($query)
    {
        if (!static::visibilityColumnExists()) {
            return $query;
        }

        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /**
     * Scope for pending recipes awaiting approval.
     */
    public function scopePending($query)
    {
        if (!static::statusColumnExists()) {
            return $query;
        }

        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter recipes using RecipeFilter.
     */
    public function scopeFilter($query, \App\Filters\RecipeFilter $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Determine if the recipe is approved.
     */
    public function isApproved(): bool
    {
        if (!static::statusColumnExists()) {
            return true;
        }

        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Determine if the recipe is pending review.
     */
    public function isPending(): bool
    {
        if (!static::statusColumnExists()) {
            return false;
        }

        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Determine if the recipe is still a draft.
     */
    public function isDraft(): bool
    {
        if (!static::statusColumnExists()) {
            return false;
        }

        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Determine if the recipe is publicly visible.
     */
    public function isPublic(): bool
    {
        if (!static::visibilityColumnExists()) {
            return true;
        }

        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    /**
     * Determine if the recipe is private.
     */
    public function isPrivate(): bool
    {
        if (!static::visibilityColumnExists()) {
            return false;
        }

        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Determine if the recipes table has the visibility column.
     */
    protected static function visibilityColumnExists(): bool
    {
        if (!static::$visibilityColumnChecked) {
            $table = (new static())->getTable();
            static::$visibilityColumnExists = Schema::hasColumn($table, 'visibility');
            static::$visibilityColumnChecked = true;
        }

        return static::$visibilityColumnExists;
    }

    /**
     * Determine if the recipes table has the status column.
     */
    protected static function statusColumnExists(): bool
    {
        if (!static::$statusColumnChecked) {
            $table = (new static())->getTable();
            static::$statusColumnExists = Schema::hasColumn($table, 'status');
            static::$statusColumnChecked = true;
        }

        return static::$statusColumnExists;
    }

    /**
     * Expose status column availability for external callers.
     */
    public static function supportsModerationStatus(): bool
    {
        return static::statusColumnExists();
    }

    /**
     * Expose visibility column availability for external callers.
     */
    public static function supportsVisibilityFlag(): bool
    {
        return static::visibilityColumnExists();
    }

    /**
     * Determine if the recipes table has the description full-text index.
     */
    public static function hasDescriptionFullTextIndex(): bool
    {
        if (! static::$descriptionFullTextChecked) {
            static::$descriptionFullTextExists = static::fullTextIndexExists('recipes_description_fulltext');
            static::$descriptionFullTextChecked = true;
        }

        return static::$descriptionFullTextExists;
    }

    /**
     * Mark the description full-text index as unavailable for the current request.
     */
    public static function markDescriptionFullTextUnavailable(): void
    {
        static::$descriptionFullTextChecked = true;
        static::$descriptionFullTextExists = false;
    }

    /**
     * Build a relative URL for public disk assets to avoid APP_URL mismatches.
     */
    protected static function publicUrl(string $path): string
    {
        $trimmed = ltrim($path, '/');

        return '/storage/' . $trimmed;
    }

    /**
     * Inspect information_schema to check for a full-text index.
     */
    protected static function fullTextIndexExists(string $indexName): bool
    {
        try {
            $instance = new static();
            $connection = $instance->getConnection();
            $database = $connection->getDatabaseName();
            $table = $connection->getTablePrefix().$instance->getTable();

            $count = $connection->table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->where('index_type', 'FULLTEXT')
                ->count();

            return $count > 0;
        } catch (\Throwable $exception) {
            Log::warning('Failed to detect recipes full-text index.', [
                'index' => $indexName,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    #[SearchUsingFullText(['description'])]
    #[SearchUsingPrefix(['slug'])]
    public function toSearchableArray(): array
    {
        return [
            'title' => (string) $this->title,
            'slug' => (string) $this->slug,
            'description' => (string) $this->description,
            'author' => (string) $this->author,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        if (self::supportsModerationStatus() && $this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if (self::supportsVisibilityFlag() && $this->visibility !== self::VISIBILITY_PUBLIC) {
            return false;
        }

        return true;
    }
}
