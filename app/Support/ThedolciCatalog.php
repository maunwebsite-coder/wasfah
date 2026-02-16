<?php

namespace App\Support;

use App\Models\ThedolciCoupon;
use App\Models\ThedolciProduct;
use App\Models\ThedolciReview;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ThedolciCatalog
{
    public const DEFAULT_SEASONAL_END = '2026-03-31 23:59:59';

    public static function products(bool $includeInactive = false): Collection
    {
        if (self::productsTableReady()) {
            try {
                $query = ThedolciProduct::query()->orderBy('sort_order')->orderBy('id');

                if (! $includeInactive) {
                    $query->where('is_active', true);
                }

                $products = $query->get()->map(fn (ThedolciProduct $product) => self::mapProductModel($product));

                if ($products->isNotEmpty()) {
                    return $products->values();
                }
            } catch (QueryException) {
                // Fallback to defaults if DB is not yet migrated.
            }
        }

        return collect(self::defaultProducts())
            ->when(! $includeInactive, fn (Collection $items) => $items->where('is_active', true))
            ->values();
    }

    public static function findProduct(string $slug): ?array
    {
        return self::products()->firstWhere('slug', $slug);
    }

    public static function seasonalProducts(): Collection
    {
        return self::products()->where('is_seasonal', true)->values();
    }

    public static function bestSellers(): Collection
    {
        return self::products()->where('is_best_seller', true)->values();
    }

    public static function reviews(): Collection
    {
        if (self::reviewsTableReady()) {
            try {
                $reviews = ThedolciReview::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function (ThedolciReview $review) {
                        return [
                            'id' => $review->id,
                            'name' => $review->customer_name,
                            'rating' => max(1, min(5, (int) $review->rating)),
                            'text' => $review->review_text,
                            'is_featured' => (bool) $review->is_featured,
                            'sort_order' => (int) $review->sort_order,
                        ];
                    });

                if ($reviews->isNotEmpty()) {
                    return $reviews->values();
                }
            } catch (QueryException) {
                // Fallback to defaults if DB is not yet migrated.
            }
        }

        return collect(self::defaultReviews());
    }

    public static function featuredReviews(): Collection
    {
        $reviews = self::reviews();
        $featured = $reviews->where('is_featured', true)->values();

        if ($featured->isNotEmpty()) {
            return $featured->take(3)->values();
        }

        return $reviews->take(3)->values();
    }

    public static function instagramFeed(): Collection
    {
        return collect([
            [
                'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Classic layers, fresh mascarpone.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Seasonal strawberry collection.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Gift-ready tiramisu boxes.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Daily fresh production.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Chocolate tiramisu moment.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80',
                'url' => 'https://instagram.com',
                'caption' => 'Cloud kitchen, artisan quality.',
            ],
        ]);
    }

    public static function deliverySlots(): Collection
    {
        return collect([
            '10:00 - 12:00',
            '12:00 - 14:00',
            '14:00 - 16:00',
            '16:00 - 18:00',
            '18:00 - 20:00',
            '20:00 - 22:00',
        ]);
    }

    public static function validateCoupon(?string $code, float $subtotal): array
    {
        $normalized = strtoupper(trim((string) $code));

        if ($normalized === '') {
            return [
                'valid' => false,
                'code' => null,
                'discount' => 0,
                'message' => 'Coupon code is empty.',
            ];
        }

        $coupon = self::couponByCode($normalized);

        if (! $coupon) {
            return [
                'valid' => false,
                'code' => $normalized,
                'discount' => 0,
                'message' => 'Coupon is invalid or expired.',
            ];
        }

        $minOrder = (float) ($coupon['min_order'] ?? 0);

        if ($subtotal < $minOrder) {
            return [
                'valid' => false,
                'code' => $normalized,
                'discount' => 0,
                'message' => 'Minimum order not reached for this coupon.',
            ];
        }

        $discount = self::discountFromCoupon($coupon, $subtotal);

        return [
            'valid' => true,
            'code' => $normalized,
            'discount' => round($discount, 2),
            'message' => 'Coupon applied successfully.',
            'coupon' => $coupon,
        ];
    }

    public static function defaultProducts(): array
    {
        return [
            [
                'slug' => 'classic-tiramisu',
                'name' => 'Classic Tiramisu',
                'headline' => 'Authentic espresso-soaked Italian layers.',
                'description' => 'Our signature recipe with premium mascarpone, delicate cocoa dusting, and bold espresso notes.',
                'story' => 'Balanced sweetness, velvety cream, and handmade ladyfingers prepared fresh daily.',
                'cover_image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=1300&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=1300&q=80',
                ],
                'size_prices' => ['Small' => 8.90, 'Medium' => 14.90, 'Large' => 21.90, 'Family Box' => 36.00],
                'pepper_price' => 0.75,
                'packaging_options' => [
                    ['name' => 'Classic Box', 'price' => 0],
                    ['name' => 'Gift Bag', 'price' => 2.00],
                ],
                'is_best_seller' => true,
                'is_seasonal' => false,
                'show_limited_edition' => false,
                'seasonal_ends_at' => null,
                'limited_quantity' => null,
                'preorder_enabled' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'strawberry-tiramisu',
                'name' => 'Strawberry Tiramisu',
                'headline' => 'A fresh berry twist with silky cream.',
                'description' => 'Soft strawberry layers, mascarpone cream, and bright fruit notes for a lighter premium profile.',
                'story' => 'Season-inspired and handcrafted in small batches for maximum freshness.',
                'cover_image' => 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?auto=format&fit=crop&w=1300&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1300&q=80',
                ],
                'size_prices' => ['Small' => 9.50, 'Medium' => 15.90, 'Large' => 23.00, 'Family Box' => 38.00],
                'pepper_price' => 1.00,
                'packaging_options' => [
                    ['name' => 'Classic Box', 'price' => 0],
                    ['name' => 'Gift Bag', 'price' => 2.00],
                    ['name' => 'Premium Ribbon Box', 'price' => 3.50],
                ],
                'is_best_seller' => true,
                'is_seasonal' => true,
                'show_limited_edition' => true,
                'seasonal_ends_at' => self::DEFAULT_SEASONAL_END,
                'limited_quantity' => 120,
                'preorder_enabled' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'chocolate-tiramisu',
                'name' => 'Chocolate Tiramisu',
                'headline' => 'Deep cocoa flavor with rich finish.',
                'description' => 'For chocolate lovers: layered cocoa mascarpone, espresso syrup, and dark chocolate curls.',
                'story' => 'Crafted with premium cocoa for a bold, indulgent, and elegant dessert experience.',
                'cover_image' => 'https://images.unsplash.com/photo-1559622214-f8a9850965bb?auto=format&fit=crop&w=1300&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1517433367423-c7e5b0f35086?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1541782814456-5f6ee0cfd7b3?auto=format&fit=crop&w=1300&q=80',
                ],
                'size_prices' => ['Small' => 9.20, 'Medium' => 15.40, 'Large' => 22.50, 'Family Box' => 37.20],
                'pepper_price' => 0.50,
                'packaging_options' => [
                    ['name' => 'Classic Box', 'price' => 0],
                    ['name' => 'Gift Bag', 'price' => 2.00],
                ],
                'is_best_seller' => true,
                'is_seasonal' => false,
                'show_limited_edition' => false,
                'seasonal_ends_at' => null,
                'limited_quantity' => null,
                'preorder_enabled' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'pistachio-espresso-tiramisu',
                'name' => 'Pistachio Espresso Tiramisu',
                'headline' => 'Limited edition with roasted pistachio.',
                'description' => 'A luxurious seasonal blend of pistachio cream, espresso, and premium mascarpone.',
                'story' => 'Designed as a monthly limited edition with controlled production quantity.',
                'cover_image' => 'https://images.unsplash.com/photo-1627308594196-a057cd4bf310?auto=format&fit=crop&w=1300&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1488477304112-4944851de03d?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1559628233-100c798642d4?auto=format&fit=crop&w=1300&q=80',
                    'https://images.unsplash.com/photo-1562440499-64c9a111f713?auto=format&fit=crop&w=1300&q=80',
                ],
                'size_prices' => ['Small' => 10.20, 'Medium' => 16.90, 'Large' => 24.90, 'Family Box' => 41.00],
                'pepper_price' => 1.20,
                'packaging_options' => [
                    ['name' => 'Classic Box', 'price' => 0],
                    ['name' => 'Premium Ribbon Box', 'price' => 3.50],
                ],
                'is_best_seller' => false,
                'is_seasonal' => true,
                'show_limited_edition' => true,
                'seasonal_ends_at' => self::DEFAULT_SEASONAL_END,
                'limited_quantity' => 80,
                'preorder_enabled' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }

    public static function defaultCoupons(): array
    {
        return [
            [
                'code' => 'FIRST10',
                'type' => 'percent',
                'value' => 10,
                'min_order' => 20,
                'max_discount' => 8,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'usage_limit' => null,
                'usage_count' => 0,
            ],
            [
                'code' => 'DOLCI15',
                'type' => 'fixed',
                'value' => 15,
                'min_order' => 60,
                'max_discount' => null,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'usage_limit' => null,
                'usage_count' => 0,
            ],
        ];
    }

    public static function bootstrapDefaultsInDatabase(): void
    {
        if (! self::productsTableReady()) {
            return;
        }

        try {
            if (ThedolciProduct::query()->count() === 0) {
                $hasLimitedEditionColumn = Schema::hasColumn('thedolci_products', 'show_limited_edition');

                foreach (self::defaultProducts() as $product) {
                    if (! $hasLimitedEditionColumn) {
                        unset($product['show_limited_edition']);
                    }

                    ThedolciProduct::query()->create($product);
                }
            }
        } catch (QueryException) {
            return;
        }

        if (! self::couponsTableReady()) {
            return;
        }

        try {
            foreach (self::defaultCoupons() as $coupon) {
                ThedolciCoupon::query()->updateOrCreate(
                    ['code' => $coupon['code']],
                    $coupon
                );
            }
        } catch (QueryException) {
            // No-op fallback for fresh installs without completed migrations.
        }

        if (! self::reviewsTableReady()) {
            return;
        }

        try {
            if (ThedolciReview::query()->count() === 0) {
                foreach (self::defaultReviews() as $index => $review) {
                    ThedolciReview::query()->create([
                        'customer_name' => $review['name'],
                        'rating' => max(1, min(5, (int) ($review['rating'] ?? 5))),
                        'review_text' => $review['text'],
                        'is_featured' => (bool) ($review['is_featured'] ?? false),
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        } catch (QueryException) {
            // No-op fallback for fresh installs without completed migrations.
        }
    }

    private static function mapProductModel(ThedolciProduct $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'headline' => $product->headline,
            'description' => $product->description,
            'story' => $product->story,
            'cover_image' => self::normalizeProductImageUrl($product->cover_image),
            'gallery_images' => collect($product->gallery_images ?? [])
                ->map(fn ($image) => self::normalizeProductImageUrl((string) $image))
                ->filter()
                ->values()
                ->all(),
            'size_prices' => self::normalizeSizePrices($product->size_prices ?? []),
            'pepper_price' => round((float) ($product->pepper_price ?? 0), 2),
            'packaging_options' => self::normalizePackagingOptions($product->packaging_options ?? []),
            'is_best_seller' => (bool) $product->is_best_seller,
            'is_seasonal' => (bool) $product->is_seasonal,
            'show_limited_edition' => (bool) ($product->show_limited_edition ?? false),
            'seasonal_ends_at' => optional($product->seasonal_ends_at)?->toIso8601String(),
            'limited_quantity' => $product->limited_quantity,
            'preorder_enabled' => (bool) $product->preorder_enabled,
            'is_active' => (bool) $product->is_active,
            'sort_order' => $product->sort_order,
        ];
    }

    private static function couponByCode(string $code): ?array
    {
        if (self::couponsTableReady()) {
            try {
                $coupon = ThedolciCoupon::query()
                    ->whereRaw('UPPER(code) = ?', [$code])
                    ->first();

                if ($coupon && $coupon->isUsable()) {
                    return [
                        'code' => strtoupper($coupon->code),
                        'type' => $coupon->type,
                        'value' => (float) $coupon->value,
                        'min_order' => (float) ($coupon->min_order ?? 0),
                        'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
                    ];
                }
            } catch (QueryException) {
                // Fallback to static coupons.
            }
        }

        return collect(self::defaultCoupons())
            ->first(function (array $coupon) use ($code) {
                return strtoupper((string) $coupon['code']) === $code && ($coupon['is_active'] ?? false);
            });
    }

    private static function discountFromCoupon(array $coupon, float $subtotal): float
    {
        if (($coupon['type'] ?? 'percent') === 'fixed') {
            return min($subtotal, (float) ($coupon['value'] ?? 0));
        }

        $discount = $subtotal * ((float) ($coupon['value'] ?? 0) / 100);

        if (array_key_exists('max_discount', $coupon) && $coupon['max_discount'] !== null) {
            $discount = min($discount, (float) $coupon['max_discount']);
        }

        return min($subtotal, $discount);
    }

    private static function productsTableReady(): bool
    {
        return Schema::hasTable('thedolci_products');
    }

    /**
     * @param array<string, mixed> $sizePrices
     * @return array<string, float>
     */
    private static function normalizeSizePrices(array $sizePrices): array
    {
        $normalized = [];

        foreach ($sizePrices as $name => $price) {
            $key = trim((string) $name);

            if ($key === '') {
                continue;
            }

            $normalized[$key] = round(max(0, (float) $price), 2);
        }

        return $normalized;
    }

    /**
     * @param array<int|string, mixed> $options
     * @return array<int, array{name: string, price: float}>
     */
    private static function normalizePackagingOptions(array $options): array
    {
        $normalized = collect($options)
            ->map(function ($option, $key) {
                if (is_array($option)) {
                    $name = trim((string) ($option['name'] ?? $option['label'] ?? ''));
                    $price = round(max(0, (float) ($option['price'] ?? 0)), 2);

                    if ($name === '') {
                        return null;
                    }

                    return ['name' => $name, 'price' => $price];
                }

                if (is_string($key) && is_numeric($option)) {
                    $name = trim($key);

                    if ($name === '') {
                        return null;
                    }

                    return [
                        'name' => $name,
                        'price' => round(max(0, (float) $option), 2),
                    ];
                }

                return null;
            })
            ->filter()
            ->unique('name')
            ->values()
            ->all();

        return $normalized;
    }

    private static function couponsTableReady(): bool
    {
        return Schema::hasTable('thedolci_coupons');
    }

    public static function defaultReviews(): array
    {
        return [
            [
                'name' => 'Nora A.',
                'rating' => 5,
                'text' => 'The texture is unbelievably creamy and balanced. Best tiramisu I had in the city.',
                'is_featured' => true,
            ],
            [
                'name' => 'Mazen K.',
                'rating' => 5,
                'text' => 'Arrived perfectly chilled and fresh. Strawberry flavor is elegant, not too sweet.',
                'is_featured' => true,
            ],
            [
                'name' => 'Sara M.',
                'rating' => 5,
                'text' => 'The Family Box is perfect for gatherings. Premium packaging and amazing quality.',
                'is_featured' => true,
            ],
            [
                'name' => 'Huda R.',
                'rating' => 4,
                'text' => 'Chocolate tiramisu is rich and smooth. Will reorder for sure.',
                'is_featured' => false,
            ],
            [
                'name' => 'Ahmad S.',
                'rating' => 5,
                'text' => 'Exactly what a premium dessert brand should taste like.',
                'is_featured' => false,
            ],
        ];
    }

    private static function reviewsTableReady(): bool
    {
        return Schema::hasTable('thedolci_reviews');
    }

    private static function normalizeProductImageUrl(?string $url): string
    {
        $cleanedValue = trim((string) $url);

        if ($cleanedValue === '') {
            return '';
        }

        $cleanedValue = str_replace('\\', '/', $cleanedValue);

        if (Str::startsWith($cleanedValue, 'storage/')) {
            return '/' . ltrim($cleanedValue, '/');
        }

        if (Str::startsWith($cleanedValue, '/storage/')) {
            return $cleanedValue;
        }

        if (Str::startsWith($cleanedValue, 'image/')) {
            return '/' . ltrim($cleanedValue, '/');
        }

        if (Str::startsWith($cleanedValue, '/image/')) {
            return $cleanedValue;
        }

        $parsedUrl = parse_url($cleanedValue);

        if (! is_array($parsedUrl)) {
            return $cleanedValue;
        }

        $host = strtolower((string) ($parsedUrl['host'] ?? ''));
        $path = (string) ($parsedUrl['path'] ?? '');
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
            && Str::startsWith($path, ['/storage/', '/image/'])) {
            return $path . $query . $fragment;
        }

        return $cleanedValue;
    }
}
