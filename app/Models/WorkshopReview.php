<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkshopReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'user_id',
        'rating',
        'comment',
        'is_approved',
        'helpful_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (WorkshopReview $review): void {
            static::syncWorkshopAggregates($review->workshop_id);
        });

        static::deleted(function (WorkshopReview $review): void {
            static::syncWorkshopAggregates($review->workshop_id);
        });
    }

    // العلاقات
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    // Accessors
    public function getRatingStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    /**
     * Recalculate rating aggregates for the parent workshop.
     */
    public static function syncWorkshopAggregates(int $workshopId): void
    {
        $stats = static::query()
            ->where('workshop_id', $workshopId)
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
            ->first();

        $reviewsCount = (int) ($stats->total_reviews ?? 0);
        $averageRating = $stats && $stats->average_rating !== null
            ? round((float) $stats->average_rating, 2)
            : 0;

        Workshop::query()
            ->whereKey($workshopId)
            ->update([
                'reviews_count' => $reviewsCount,
                'rating' => $averageRating,
            ]);
    }
}
