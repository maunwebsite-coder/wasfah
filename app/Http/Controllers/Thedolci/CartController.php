<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Support\ThedolciCart;
use App\Support\ThedolciCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = ThedolciCart::items();
        $subtotal = ThedolciCart::subtotal();
        $coupon = ThedolciCart::coupon();
        $discount = (float) ($coupon['discount'] ?? 0);
        $total = max(0, round($subtotal - $discount, 2));

        return view('thedolci.cart', [
            'cartCount' => ThedolciCart::count(),
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $total,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'max:80'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:30'],
            'packaging_type' => ['nullable', 'string', 'max:120'],
            'add_pepper' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'in:cart,checkout,back'],
        ]);

        $product = ThedolciCatalog::findProduct($data['slug']);

        if (! $product) {
            return back()->withErrors(['product' => 'Selected product is not available.']);
        }

        $prices = $product['size_prices'] ?? [];
        $requestedSize = trim((string) ($data['size'] ?? ''));
        $size = array_key_exists($requestedSize, $prices)
            ? $requestedSize
            : (array_key_first($prices) ?? 'Standard');

        $unitPrice = (float) ($prices[$size] ?? 0);

        $pepperPrice = round(max(0, (float) ($product['pepper_price'] ?? 0)), 2);
        $addPepper = (bool) ($data['add_pepper'] ?? false);
        if ($addPepper && $pepperPrice > 0) {
            $unitPrice += $pepperPrice;
        }

        [$packagingType, $packagingPrice] = $this->resolvePackagingSelection(
            $product['packaging_options'] ?? [],
            $data['packaging_type'] ?? null
        );

        if ($packagingPrice > 0) {
            $unitPrice += $packagingPrice;
        }

        ThedolciCart::add([
            'slug' => $product['slug'],
            'name' => $product['name'],
            'image' => $product['cover_image'],
            'size' => $size,
            'quantity' => (int) ($data['quantity'] ?? 1),
            'unit_price' => $unitPrice,
            'customizations' => [
                'add_pepper' => $addPepper,
                'pepper_price' => $addPepper ? $pepperPrice : 0,
                'packaging_type' => $packagingType,
                'packaging_price' => $packagingPrice,
            ],
        ]);

        $redirect = $data['redirect_to'] ?? 'cart';

        if ($redirect === 'checkout') {
            return redirect()->route('thedolci.checkout')->with('success', 'Item added to cart.');
        }

        if ($redirect === 'back') {
            return back()->with('success', 'Item added to cart.');
        }

        return redirect()->route('thedolci.cart')->with('success', 'Item added to cart.');
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        ThedolciCart::updateQuantity($key, (int) $data['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(string $key): RedirectResponse
    {
        ThedolciCart::remove($key);

        return back()->with('success', 'Item removed from cart.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:30'],
        ]);

        $subtotal = ThedolciCart::subtotal();
        $result = ThedolciCatalog::validateCoupon($data['coupon_code'], $subtotal);

        if (! $result['valid']) {
            ThedolciCart::clearCoupon();

            return back()->withErrors(['coupon_code' => $result['message']]);
        }

        ThedolciCart::setCoupon([
            'code' => $result['code'],
            'discount' => $result['discount'],
        ]);

        return back()->with('success', 'Coupon applied.');
    }

    /**
     * @param array<int|string, mixed> $options
     * @return array{0: string, 1: float}
     */
    private function resolvePackagingSelection(array $options, ?string $requestedType): array
    {
        $normalized = collect($options)
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
            ->values();

        if ($normalized->isEmpty()) {
            return ['', 0.0];
        }

        $requested = trim((string) $requestedType);

        if ($requested !== '') {
            $selected = $normalized->first(function (array $option) use ($requested) {
                return strtolower($option['name']) === strtolower($requested);
            });

            if ($selected) {
                return [$selected['name'], (float) $selected['price']];
            }
        }

        $fallback = $normalized->first();

        return [$fallback['name'], (float) $fallback['price']];
    }
}
