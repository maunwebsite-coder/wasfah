<?php

namespace App\Support;

use App\Models\ThedolciOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ThedolciCart
{
    public const CART_KEY = 'thedolci.cart';
    public const COUPON_KEY = 'thedolci.coupon';

    public static function items(): Collection
    {
        $items = collect(session(self::CART_KEY, []))
            ->map(function (array $item, string $key) {
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $item['key'] = $key;
                $item['quantity'] = $quantity;
                $item['unit_price'] = round($unitPrice, 2);
                $item['line_total'] = round($quantity * $unitPrice, 2);

                return $item;
            });

        return $items->values();
    }

    public static function add(array $item): string
    {
        $cart = session(self::CART_KEY, []);

        $signature = implode('|', [
            $item['slug'] ?? '',
            $item['size'] ?? '',
            strtolower(trim((string) data_get($item, 'customizations.packaging_type', ''))),
            data_get($item, 'customizations.add_pepper') ? 'pepper-1' : 'pepper-0',
            number_format((float) data_get($item, 'customizations.packaging_price', 0), 2, '.', ''),
            number_format((float) data_get($item, 'customizations.pepper_price', 0), 2, '.', ''),
        ]);

        $key = md5($signature);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = min(30, ((int) $cart[$key]['quantity']) + ((int) ($item['quantity'] ?? 1)));
        } else {
            $cart[$key] = [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'image' => $item['image'],
                'size' => $item['size'],
                'quantity' => max(1, min(30, (int) ($item['quantity'] ?? 1))),
                'unit_price' => round((float) $item['unit_price'], 2),
                'customizations' => [
                    'add_pepper' => (bool) data_get($item, 'customizations.add_pepper', false),
                    'pepper_price' => round((float) data_get($item, 'customizations.pepper_price', 0), 2),
                    'packaging_type' => trim((string) data_get($item, 'customizations.packaging_type', '')),
                    'packaging_price' => round((float) data_get($item, 'customizations.packaging_price', 0), 2),
                ],
            ];
        }

        session([self::CART_KEY => $cart]);

        return $key;
    }

    public static function quantityForSlug(string $slug): int
    {
        $cleanSlug = trim($slug);

        if ($cleanSlug === '') {
            return 0;
        }

        return (int) self::items()
            ->where('slug', $cleanSlug)
            ->sum(function (array $item) {
                return max(1, (int) ($item['quantity'] ?? 1));
            });
    }

    public static function availableQuantityForSlug(string $slug, ?Collection $products = null): ?int
    {
        $cleanSlug = trim($slug);

        if ($cleanSlug === '') {
            return null;
        }

        $catalog = $products ?? ThedolciCatalog::products();
        $product = $catalog->firstWhere('slug', $cleanSlug);

        if (! is_array($product)) {
            return null;
        }

        $availableBySlug = self::availableQuantitiesBySlug($catalog);

        return $availableBySlug[$cleanSlug] ?? null;
    }

    /**
     * @param Collection<int, array<string, mixed>>|null $products
     * @return array<string, int>
     */
    public static function availableQuantitiesBySlug(?Collection $products = null): array
    {
        $catalog = $products ?? ThedolciCatalog::products();
        $soldBySlug = self::soldQuantitiesBySlug();
        $available = [];

        foreach ($catalog as $product) {
            if (! is_array($product)) {
                continue;
            }

            $slug = trim((string) ($product['slug'] ?? ''));

            if ($slug === '') {
                continue;
            }

            $availableQuantity = self::availableQuantityForProduct($product, $soldBySlug);

            if ($availableQuantity !== null) {
                $available[$slug] = $availableQuantity;
            }
        }

        return $available;
    }

    public static function updateQuantity(string $key, int $quantity): void
    {
        $cart = session(self::CART_KEY, []);

        if (! isset($cart[$key])) {
            return;
        }

        $quantity = max(1, min(30, $quantity));
        $cart[$key]['quantity'] = $quantity;
        session([self::CART_KEY => $cart]);
    }

    public static function remove(string $key): void
    {
        $cart = session(self::CART_KEY, []);
        unset($cart[$key]);
        session([self::CART_KEY => $cart]);
    }

    public static function subtotal(): float
    {
        return round(self::items()->sum('line_total'), 2);
    }

    public static function count(): int
    {
        return (int) self::items()->sum('quantity');
    }

    public static function setCoupon(array $coupon): void
    {
        session([self::COUPON_KEY => $coupon]);
    }

    public static function coupon(): ?array
    {
        $coupon = session(self::COUPON_KEY);

        return is_array($coupon) ? $coupon : null;
    }

    public static function clearCoupon(): void
    {
        session()->forget(self::COUPON_KEY);
    }

    public static function clear(): void
    {
        session()->forget([self::CART_KEY, self::COUPON_KEY]);
    }

    /**
     * @param Collection<int, array<string, mixed>>|null $products
     * @return array{
     *     items: Collection<int, array<string, mixed>>,
     *     changed: bool,
     *     messages: array<int, string>,
     *     available_by_slug: array<string, int>
     * }
     */
    public static function reconcileWithCatalog(?Collection $products = null): array
    {
        $productsBySlug = ($products ?? ThedolciCatalog::products())->keyBy('slug');
        $soldBySlug = self::soldQuantitiesBySlug();
        $availableBySlug = $productsBySlug
            ->mapWithKeys(function ($product, $slug) use ($soldBySlug) {
                if (! is_array($product) || ! is_string($slug)) {
                    return [];
                }

                $available = self::availableQuantityForProduct($product, $soldBySlug);

                return $available === null ? [] : [$slug => $available];
            })
            ->all();
        $cart = session(self::CART_KEY, []);
        $normalizedCart = [];
        $messages = [];
        $cartUsageBySlug = [];
        $changed = false;

        foreach ($cart as $key => $rawItem) {
            if (! is_string($key) || ! is_array($rawItem)) {
                $changed = true;
                continue;
            }

            $slug = trim((string) ($rawItem['slug'] ?? ''));
            $product = $productsBySlug->get($slug);

            if (! is_array($product)) {
                $changed = true;
                $messages[] = 'Some items were removed because they are no longer available.';
                continue;
            }

            [$size, $sizePrice, $sizeAdjusted] = self::resolveSizeSelection($rawItem, $product);
            if ($sizeAdjusted) {
                $changed = true;
                $messages[] = 'Some item sizes were updated to available options.';
            }

            $quantity = max(1, min(30, (int) ($rawItem['quantity'] ?? 1)));
            if ($quantity !== (int) ($rawItem['quantity'] ?? 1)) {
                $changed = true;
            }

            $availableForProduct = self::availableQuantityForProduct($product, $soldBySlug);
            if ($availableForProduct !== null) {
                $alreadyAllocated = (int) ($cartUsageBySlug[$slug] ?? 0);
                $availableForLine = max(0, $availableForProduct - $alreadyAllocated);

                if ($availableForLine <= 0) {
                    $changed = true;
                    $messages[] = 'Some limited items were removed because stock is unavailable.';
                    continue;
                }

                if ($quantity > $availableForLine) {
                    $quantity = $availableForLine;
                    $changed = true;
                    $messages[] = 'Cart quantities were adjusted to match available stock.';
                }
            }

            [$packagingType, $packagingPrice, $packagingAdjusted] = self::resolvePackagingSelection($rawItem, $product);
            if ($packagingAdjusted) {
                $changed = true;
                $messages[] = 'Packaging options were updated to currently available choices.';
            }

            $addPepper = (bool) data_get($rawItem, 'customizations.add_pepper', false);
            $pepperPrice = $addPepper
                ? round(max(0, (float) ($product['pepper_price'] ?? 0)), 2)
                : 0.0;

            if ($addPepper) {
                $storedPepperPrice = round(max(0, (float) data_get($rawItem, 'customizations.pepper_price', 0)), 2);
                if (abs($storedPepperPrice - $pepperPrice) > 0.001) {
                    $changed = true;
                    $messages[] = 'Cart prices were refreshed to current catalog rates.';
                }
            }

            $unitPrice = round($sizePrice + $pepperPrice + $packagingPrice, 2);
            $storedUnitPrice = round((float) ($rawItem['unit_price'] ?? 0), 2);
            if (abs($storedUnitPrice - $unitPrice) > 0.001) {
                $changed = true;
                $messages[] = 'Cart prices were refreshed to current catalog rates.';
            }

            $normalizedCart[$key] = [
                'slug' => $slug,
                'name' => trim((string) ($product['name'] ?? $rawItem['name'] ?? '')),
                'image' => trim((string) ($product['cover_image'] ?? $rawItem['image'] ?? '')),
                'size' => $size,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'customizations' => [
                    'add_pepper' => $addPepper,
                    'pepper_price' => $pepperPrice,
                    'packaging_type' => $packagingType,
                    'packaging_price' => $packagingPrice,
                ],
            ];

            $cartUsageBySlug[$slug] = (int) ($cartUsageBySlug[$slug] ?? 0) + $quantity;
        }

        $cartChanged = $changed || $normalizedCart !== $cart;

        if ($cartChanged) {
            session([self::CART_KEY => $normalizedCart]);
        }

        return [
            'items' => self::items(),
            'changed' => $cartChanged,
            'messages' => collect($messages)->filter()->unique()->values()->all(),
            'available_by_slug' => $availableBySlug,
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $product
     * @return array{0: string, 1: float, 2: bool}
     */
    private static function resolveSizeSelection(array $item, array $product): array
    {
        $prices = self::normalizeSizePrices((array) ($product['size_prices'] ?? []));
        $requested = trim((string) ($item['size'] ?? ''));

        if (empty($prices)) {
            $defaultSize = $requested !== '' ? $requested : 'Standard';

            return [$defaultSize, 0.0, false];
        }

        if ($requested !== '' && array_key_exists($requested, $prices)) {
            return [$requested, (float) $prices[$requested], false];
        }

        if ($requested !== '') {
            foreach ($prices as $name => $price) {
                if (strtolower($name) === strtolower($requested)) {
                    return [$name, (float) $price, $name !== $requested];
                }
            }
        }

        $fallbackSize = (string) array_key_first($prices);

        return [$fallbackSize, (float) ($prices[$fallbackSize] ?? 0), true];
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $product
     * @return array{0: string, 1: float, 2: bool}
     */
    private static function resolvePackagingSelection(array $item, array $product): array
    {
        $requested = trim((string) data_get($item, 'customizations.packaging_type', ''));
        $storedPrice = round(max(0, (float) data_get($item, 'customizations.packaging_price', 0)), 2);
        $options = self::normalizePackagingOptions((array) ($product['packaging_options'] ?? []));

        if ($requested === '') {
            return ['', 0.0, $storedPrice > 0];
        }

        $selected = $options->first(function (array $option) use ($requested) {
            return strtolower($option['name']) === strtolower($requested);
        });

        if (! is_array($selected)) {
            return ['', 0.0, true];
        }

        $resolvedName = $selected['name'];
        $resolvedPrice = round(max(0, (float) ($selected['price'] ?? 0)), 2);
        $wasAdjusted = $resolvedName !== $requested || abs($resolvedPrice - $storedPrice) > 0.001;

        return [$resolvedName, $resolvedPrice, $wasAdjusted];
    }

    /**
     * @param array<int|string, mixed> $sizePrices
     * @return array<string, float>
     */
    private static function normalizeSizePrices(array $sizePrices): array
    {
        $normalized = [];

        foreach ($sizePrices as $name => $price) {
            $label = trim((string) $name);

            if ($label === '') {
                continue;
            }

            $normalized[$label] = round(max(0, (float) $price), 2);
        }

        return $normalized;
    }

    /**
     * @param array<int|string, mixed> $options
     * @return Collection<int, array{name: string, price: float}>
     */
    private static function normalizePackagingOptions(array $options): Collection
    {
        return collect($options)
            ->map(function ($option, $key) {
                if (is_array($option)) {
                    $name = trim((string) ($option['name'] ?? ''));
                    $price = round(max(0, (float) ($option['price'] ?? 0)), 2);

                    return $name === '' ? null : ['name' => $name, 'price' => $price];
                }

                if (is_string($key) && is_numeric($option)) {
                    $name = trim($key);

                    return $name === ''
                        ? null
                        : ['name' => $name, 'price' => round(max(0, (float) $option), 2)];
                }

                return null;
            })
            ->filter()
            ->unique('name')
            ->values();
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, int> $soldBySlug
     */
    private static function availableQuantityForProduct(array $product, array $soldBySlug): ?int
    {
        $limit = data_get($product, 'limited_quantity');

        if ($limit === null || $limit === '') {
            return null;
        }

        $maxUnits = max(0, (int) $limit);
        $slug = trim((string) ($product['slug'] ?? ''));
        $soldUnits = $slug === '' ? 0 : (int) ($soldBySlug[$slug] ?? 0);

        return max(0, $maxUnits - $soldUnits);
    }

    /**
     * @return array<string, int>
     */
    private static function soldQuantitiesBySlug(): array
    {
        if (! Schema::hasTable('thedolci_orders')) {
            return [];
        }

        $sold = [];

        try {
            foreach (
                ThedolciOrder::query()
                    ->where('status', '!=', 'cancelled')
                    ->select('items')
                    ->cursor() as $order
            ) {
                $items = $order->items;

                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $lineItem) {
                    if (! is_array($lineItem)) {
                        continue;
                    }

                    $slug = trim((string) ($lineItem['slug'] ?? ''));
                    $quantity = max(0, (int) ($lineItem['quantity'] ?? 0));

                    if ($slug === '' || $quantity === 0) {
                        continue;
                    }

                    $sold[$slug] = (int) ($sold[$slug] ?? 0) + $quantity;
                }
            }
        } catch (QueryException) {
            return [];
        }

        return $sold;
    }
}
