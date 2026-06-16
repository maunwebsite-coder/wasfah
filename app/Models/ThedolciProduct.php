<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThedolciProduct extends Model
{
    use HasFactory;

    protected $table = 'thedolci_products';

    protected $fillable = [
        'slug',
        'name',
        'headline',
        'description',
        'story',
        'cover_image',
        'gallery_images',
        'size_prices',
        'pepper_price',
        'packaging_options',
        'is_best_seller',
        'is_seasonal',
        'show_limited_edition',
        'seasonal_ends_at',
        'limited_quantity',
        'preorder_enabled',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'size_prices' => 'array',
            'packaging_options' => 'array',
            'pepper_price' => 'decimal:2',
            'is_best_seller' => 'boolean',
            'is_seasonal' => 'boolean',
            'show_limited_edition' => 'boolean',
            'preorder_enabled' => 'boolean',
            'is_active' => 'boolean',
            'seasonal_ends_at' => 'datetime',
        ];
    }

    public function priceForSize(string $size): float
    {
        $prices = $this->size_prices ?? [];
        $value = $prices[$size] ?? null;

        if ($value === null) {
            $value = $prices['Medium'] ?? reset($prices);
        }

        return (float) ($value ?? 0);
    }
}
