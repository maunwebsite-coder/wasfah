<?php

namespace App\Livewire\Thedolci;

use App\Support\ThedolciCart;
use App\Support\ThedolciCatalog;
use Illuminate\Support\Collection;
use Livewire\Component;

class CartManager extends Component
{
    public array $quantities = [];
    public array $packagingSelections = [];

    public function mount(): void
    {
        $this->syncInputState(ThedolciCart::items());
    }

    public function render()
    {
        return view('livewire.thedolci.cart-manager', $this->buildViewData());
    }

    public function increment(string $key): void
    {
        $current = (int) ($this->quantities[$key] ?? 1);

        ThedolciCart::updateQuantity($key, min(30, $current + 1));
        $this->reconcileAndSync();
    }

    public function decrement(string $key): void
    {
        $current = (int) ($this->quantities[$key] ?? 1);

        ThedolciCart::updateQuantity($key, max(1, $current - 1));
        $this->reconcileAndSync();
    }

    public function updateQuantity(string $key): void
    {
        $quantity = (int) ($this->quantities[$key] ?? 1);

        ThedolciCart::updateQuantity($key, $quantity);
        $this->reconcileAndSync();
    }

    public function updatePackaging(string $key): void
    {
        $item = ThedolciCart::items()->firstWhere('key', $key);

        if (! is_array($item)) {
            return;
        }

        $requestedType = trim((string) ($this->packagingSelections[$key] ?? ''));
        $currentPackagingPrice = round(max(0, (float) data_get($item, 'customizations.packaging_price', 0)), 2);

        if ($requestedType === '') {
            $packagingType = '';
            $packagingPrice = 0.0;
        } else {
            $product = ThedolciCatalog::findProduct((string) ($item['slug'] ?? ''));

            if (! is_array($product)) {
                return;
            }

            [$packagingType, $packagingPrice] = $this->resolvePackagingSelection(
                $product['packaging_options'] ?? [],
                $requestedType,
                false
            );

            if ($packagingType === '') {
                return;
            }
        }

        $unitPrice = round(max(0, (float) ($item['unit_price'] ?? 0) - $currentPackagingPrice + $packagingPrice), 2);

        ThedolciCart::remove($key);
        ThedolciCart::add([
            'slug' => $item['slug'],
            'name' => $item['name'],
            'image' => $item['image'],
            'size' => $item['size'],
            'quantity' => (int) ($item['quantity'] ?? 1),
            'unit_price' => $unitPrice,
            'customizations' => [
                'add_pepper' => (bool) data_get($item, 'customizations.add_pepper', false),
                'pepper_price' => round((float) data_get($item, 'customizations.pepper_price', 0), 2),
                'packaging_type' => $packagingType,
                'packaging_price' => $packagingPrice,
            ],
        ]);

        $this->reconcileAndSync();
    }

    public function removeItem(string $key): void
    {
        ThedolciCart::remove($key);
        $this->reconcileAndSync();
    }

    public function clearCart(): void
    {
        ThedolciCart::clear();
        $this->syncInputState(collect());
        $this->dispatch('thedolci-cart-count-updated', count: 0);
    }

    private function buildViewData(): array
    {
        $products = ThedolciCatalog::products();
        $productsBySlug = $products->keyBy('slug');
        $syncResult = ThedolciCart::reconcileWithCatalog($products);
        $availabilityBySlug = (array) ($syncResult['available_by_slug'] ?? []);
        $allocatedBySlug = [];

        $items = $syncResult['items']->map(function (array $item) use ($productsBySlug, $availabilityBySlug, &$allocatedBySlug) {
            $slug = trim((string) ($item['slug'] ?? ''));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $availableForProduct = $availabilityBySlug[$slug] ?? null;
            $allocatedBefore = (int) ($allocatedBySlug[$slug] ?? 0);
            $availableForLine = $availableForProduct !== null
                ? max(0, $availableForProduct - $allocatedBefore)
                : null;

            $allocatedBySlug[$slug] = $allocatedBefore + $quantity;

            $remainingAfterLine = $availableForProduct !== null
                ? max(0, $availableForProduct - (int) $allocatedBySlug[$slug])
                : null;

            $item['available_packaging_options'] = $this->normalizePackagingOptions(
                (array) data_get($productsBySlug, $slug . '.packaging_options', [])
            )->all();
            $item['inventory'] = [
                'is_limited' => $availableForProduct !== null,
                'available_for_product' => $availableForProduct,
                'available_for_line' => $availableForLine,
                'remaining_after_line' => $remainingAfterLine,
            ];

            return $item;
        });

        $this->syncInputState($items);

        $summary = $this->buildSummaryTotals($items);
        $itemCount = (int) $items->sum('quantity');
        $uniqueItems = (int) $items->count();
        $limitedSelectionsCount = (int) $items->filter(function (array $item) {
            return (bool) data_get($item, 'inventory.is_limited', false);
        })->count();

        $subtotal = ThedolciCart::subtotal();
        $couponsEnabled = $this->couponsEnabledForCurrentUser();
        $couponMessages = [];
        $coupon = $this->resolveActiveCoupon($subtotal, $couponsEnabled, $couponMessages);
        $discount = $couponsEnabled ? (float) ($coupon['discount'] ?? 0) : 0;
        $total = max(0, round($subtotal - $discount, 2));
        $cartSyncMessages = collect(array_merge(
            (array) ($syncResult['messages'] ?? []),
            $couponMessages
        ))->filter()->unique()->values();
        $healthScore = max(0, min(100, 100 - ($cartSyncMessages->count() * 15)));

        return [
            'items' => $items,
            'itemCount' => $itemCount,
            'uniqueItems' => $uniqueItems,
            'limitedSelectionsCount' => $limitedSelectionsCount,
            'cartSyncMessages' => $cartSyncMessages,
            'healthScore' => $healthScore,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $total,
            'couponsEnabled' => $couponsEnabled,
            ...$summary,
        ];
    }

    private function reconcileAndSync(?Collection $products = null): void
    {
        $syncResult = ThedolciCart::reconcileWithCatalog($products);

        $this->syncInputState($syncResult['items']);
        $this->dispatch('thedolci-cart-count-updated', count: ThedolciCart::count());
    }

    private function syncInputState(Collection $items): void
    {
        $quantities = [];
        $packagingSelections = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $key = (string) ($item['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $quantities[$key] = max(1, min(30, (int) ($item['quantity'] ?? 1)));
            $packagingSelections[$key] = trim((string) data_get($item, 'customizations.packaging_type', ''));
        }

        if ($this->quantities !== $quantities) {
            $this->quantities = $quantities;
        }

        if ($this->packagingSelections !== $packagingSelections) {
            $this->packagingSelections = $packagingSelections;
        }
    }

    private function couponsEnabledForCurrentUser(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    /**
     * @param array<int, string> $messages
     * @return array{code: string, discount: float}|null
     */
    private function resolveActiveCoupon(float $subtotal, bool $couponsEnabled, array &$messages): ?array
    {
        if (! $couponsEnabled) {
            ThedolciCart::clearCoupon();

            return null;
        }

        $coupon = ThedolciCart::coupon();

        if (! is_array($coupon) || empty($coupon['code'])) {
            return null;
        }

        $result = ThedolciCatalog::validateCoupon((string) $coupon['code'], $subtotal);

        if (! $result['valid']) {
            ThedolciCart::clearCoupon();
            $messages[] = 'Coupon was removed because current cart total does not meet coupon rules.';

            return null;
        }

        $currentDiscount = round((float) ($coupon['discount'] ?? 0), 2);
        $resolvedDiscount = round((float) ($result['discount'] ?? 0), 2);
        $resolvedCode = (string) ($result['code'] ?? '');

        if ($resolvedCode !== '' && ($resolvedCode !== (string) $coupon['code'] || abs($currentDiscount - $resolvedDiscount) > 0.001)) {
            ThedolciCart::setCoupon([
                'code' => $resolvedCode,
                'discount' => $resolvedDiscount,
            ]);

            $messages[] = 'Coupon discount was recalculated to match your latest cart total.';
        }

        return [
            'code' => $resolvedCode,
            'discount' => $resolvedDiscount,
        ];
    }

    /**
     * @param array<int|string, mixed> $options
     * @return array{0: string, 1: float}
     */
    private function resolvePackagingSelection(array $options, ?string $requestedType, bool $fallbackToFirst = true): array
    {
        $normalized = $this->normalizePackagingOptions($options);

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

        if (! $fallbackToFirst) {
            return ['', 0.0];
        }

        $fallback = $normalized->first();

        return [$fallback['name'], (float) $fallback['price']];
    }

    /**
     * @param array<int|string, mixed> $options
     * @return Collection<int, array{name: string, price: float}>
     */
    private function normalizePackagingOptions(array $options): Collection
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
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $items
     * @return array{
     *     baseItemsSubtotal: float,
     *     pepperAddonsTotal: float,
     *     packagingAddonsTotal: float,
     *     addonsTotal: float,
     *     showPepperAddonsRow: bool,
     *     showPackagingAddonsRow: bool,
     *     showAddonsTotalRow: bool
     * }
     */
    private function buildSummaryTotals(Collection $items): array
    {
        $baseItemsSubtotal = 0.0;
        $pepperAddonsTotal = 0.0;
        $packagingAddonsTotal = 0.0;
        $pepperAddonsSelectedCount = 0;
        $packagingAddonsSelectedCount = 0;

        foreach ($items as $summaryItem) {
            $quantity = max(1, (int) ($summaryItem['quantity'] ?? 1));
            $lineTotal = (float) ($summaryItem['line_total'] ?? 0);
            $hasPepperAddon = ! empty(data_get($summaryItem, 'customizations.add_pepper'));
            $hasPackagingAddon = trim((string) data_get($summaryItem, 'customizations.packaging_type', '')) !== '';
            $pepperUnitPrice = $hasPepperAddon
                ? max(0, (float) data_get($summaryItem, 'customizations.pepper_price', 0))
                : 0.0;
            $packagingUnitPrice = $hasPackagingAddon
                ? max(0, (float) data_get($summaryItem, 'customizations.packaging_price', 0))
                : 0.0;

            $pepperLineTotal = round($pepperUnitPrice * $quantity, 2);
            $packagingLineTotal = round($packagingUnitPrice * $quantity, 2);
            $addonsLineTotal = round($pepperLineTotal + $packagingLineTotal, 2);
            $baseLineTotal = round(max(0, $lineTotal - $addonsLineTotal), 2);

            $baseItemsSubtotal += $baseLineTotal;
            $pepperAddonsTotal += $pepperLineTotal;
            $packagingAddonsTotal += $packagingLineTotal;

            if ($hasPepperAddon) {
                $pepperAddonsSelectedCount += $quantity;
            }

            if ($hasPackagingAddon) {
                $packagingAddonsSelectedCount += $quantity;
            }
        }

        $baseItemsSubtotal = round($baseItemsSubtotal, 2);
        $pepperAddonsTotal = round($pepperAddonsTotal, 2);
        $packagingAddonsTotal = round($packagingAddonsTotal, 2);
        $addonsTotal = round($pepperAddonsTotal + $packagingAddonsTotal, 2);
        $showPepperAddonsRow = $pepperAddonsSelectedCount > 0;
        $showPackagingAddonsRow = $packagingAddonsSelectedCount > 0;
        $showAddonsTotalRow = $showPepperAddonsRow && $showPackagingAddonsRow;

        return [
            'baseItemsSubtotal' => $baseItemsSubtotal,
            'pepperAddonsTotal' => $pepperAddonsTotal,
            'packagingAddonsTotal' => $packagingAddonsTotal,
            'addonsTotal' => $addonsTotal,
            'showPepperAddonsRow' => $showPepperAddonsRow,
            'showPackagingAddonsRow' => $showPackagingAddonsRow,
            'showAddonsTotalRow' => $showAddonsTotalRow,
        ];
    }
}
