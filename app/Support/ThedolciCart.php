<?php

namespace App\Support;

use Illuminate\Support\Collection;

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
}
