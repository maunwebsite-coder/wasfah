<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciCoupon;
use App\Models\ThedolciOrder;
use App\Support\ThedolciCart;
use App\Support\ThedolciCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const FALLBACK_ORDERS_SESSION_KEY = 'thedolci.fallback_orders';

    public function show(): View|RedirectResponse
    {
        $syncResult = ThedolciCart::reconcileWithCatalog();
        $items = $syncResult['items'];

        if ($items->isEmpty()) {
            return redirect()->route('thedolci.shop')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $subtotal = ThedolciCart::subtotal();
        $couponsEnabled = $this->couponsEnabledForCurrentUser();

        if (! $couponsEnabled) {
            ThedolciCart::clearCoupon();
            session()->forget('thedolci.preferred_coupon');
        }

        $coupon = $couponsEnabled ? ThedolciCart::coupon() : null;
        $discount = $couponsEnabled ? (float) ($coupon['discount'] ?? 0) : 0;
        $total = max(0, round($subtotal - $discount, 2));

        return view('thedolci.checkout', [
            'cartCount' => ThedolciCart::count(),
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $total,
            'deliverySlots' => ThedolciCatalog::deliverySlots(),
            'couponsEnabled' => $couponsEnabled,
            'cartSyncMessages' => (array) ($syncResult['messages'] ?? []),
        ]);
    }

    public function place(Request $request): RedirectResponse
    {
        $syncResult = ThedolciCart::reconcileWithCatalog();
        $items = $syncResult['items'];

        if ($items->isEmpty()) {
            return redirect()->route('thedolci.shop')->withErrors(['cart' => 'Your cart is empty.']);
        }

        if ($syncResult['changed']) {
            $warningMessage = collect((array) ($syncResult['messages'] ?? []))
                ->map(fn ($message) => trim((string) $message))
                ->filter()
                ->unique()
                ->implode(' ');

            $response = redirect()->route('thedolci.cart')
                ->withErrors(['cart' => 'Your cart was updated based on latest availability. Please review it before checkout.']);

            if ($warningMessage !== '') {
                $response->with('warning', $warningMessage);
            }

            return $response;
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'fulfillment_type' => ['required', 'in:delivery,pickup'],
            'delivery_address' => ['nullable', 'string', 'max:350'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_time' => ['required', 'string', 'max:50'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['fulfillment_type'] === 'delivery' && blank($data['delivery_address'])) {
            return back()->withErrors(['delivery_address' => 'Delivery address is required for delivery orders.'])->withInput();
        }

        $subtotal = ThedolciCart::subtotal();
        $discount = 0;
        $couponCode = null;
        $couponsEnabled = $this->couponsEnabledForCurrentUser();

        if ($couponsEnabled) {
            $requestCoupon = trim((string) ($data['coupon_code'] ?? ''));
            $sessionCoupon = ThedolciCart::coupon();
            $preferredCoupon = trim((string) session('thedolci.preferred_coupon', ''));

            if ($requestCoupon !== '') {
                $validation = ThedolciCatalog::validateCoupon($requestCoupon, $subtotal);

                if (! $validation['valid']) {
                    return back()->withErrors(['coupon_code' => $validation['message']])->withInput();
                }

                $discount = (float) $validation['discount'];
                $couponCode = $validation['code'];
                ThedolciCart::setCoupon(['code' => $couponCode, 'discount' => $discount]);
            } elseif ($sessionCoupon && isset($sessionCoupon['code'])) {
                $revalidation = ThedolciCatalog::validateCoupon((string) $sessionCoupon['code'], $subtotal);

                if ($revalidation['valid']) {
                    $discount = (float) $revalidation['discount'];
                    $couponCode = (string) $revalidation['code'];
                } else {
                    ThedolciCart::clearCoupon();
                }
            } elseif ($preferredCoupon !== '') {
                $preferredValidation = ThedolciCatalog::validateCoupon($preferredCoupon, $subtotal);

                if ($preferredValidation['valid']) {
                    $discount = (float) $preferredValidation['discount'];
                    $couponCode = (string) $preferredValidation['code'];

                    ThedolciCart::setCoupon([
                        'code' => $couponCode,
                        'discount' => $discount,
                    ]);
                }
            }
        } else {
            ThedolciCart::clearCoupon();
            session()->forget('thedolci.preferred_coupon');
        }

        $total = max(0, round($subtotal - $discount, 2));
        $orderNumber = $this->generateOrderNumber();
        $points = (int) floor($total / 10);

        $orderPayload = [
            'order_number' => $orderNumber,
            'customer_name' => $data['customer_name'],
            'customer_email' => $this->normalizeEmail($data['customer_email']),
            'customer_phone' => $data['customer_phone'],
            'fulfillment_type' => $data['fulfillment_type'],
            'delivery_address' => $data['fulfillment_type'] === 'delivery' ? $data['delivery_address'] : null,
            'delivery_date' => $data['delivery_date'],
            'delivery_time' => $data['delivery_time'],
            'coupon_code' => $couponCode,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'total' => $total,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'new',
            'notes' => $data['notes'] ?? null,
            'items' => $items->map(function (array $item) {
                return [
                    'slug' => $item['slug'],
                    'name' => $item['name'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'customizations' => $item['customizations'] ?? [],
                ];
            })->values()->all(),
            'loyalty_points_earned' => $points,
        ];

        $orderSaved = false;

        if (Schema::hasTable('thedolci_orders')) {
            try {
                ThedolciOrder::query()->create($orderPayload);
                $this->incrementCouponUsage($couponCode);
                $orderSaved = true;
            } catch (QueryException) {
                Log::warning('Failed to persist thedolci order, using session fallback.', [
                    'order_number' => $orderNumber,
                ]);
            }
        }

        session()->put('thedolci.last_order', $orderPayload);

        if (! $orderSaved) {
            $this->storeFallbackOrder($orderPayload);
        }

        ThedolciCart::clear();

        return redirect()->route('thedolci.order.success', ['orderNumber' => $orderNumber]);
    }

    public function success(?string $orderNumber = null): View|RedirectResponse
    {
        $order = null;
        $normalizedOrderNumber = $orderNumber ? $this->normalizeOrderNumber($orderNumber) : null;

        if ($orderNumber && Schema::hasTable('thedolci_orders')) {
            $order = ThedolciOrder::query()
                ->whereRaw('UPPER(REPLACE(TRIM(order_number), " ", "")) = ?', [$normalizedOrderNumber])
                ->first();
        }

        if (! $order && $normalizedOrderNumber) {
            $order = $this->findFallbackOrderByNumber($normalizedOrderNumber);
        }

        if (! $order && is_array(session('thedolci.last_order'))) {
            $lastOrder = session('thedolci.last_order');
            $lastOrderNumber = $this->normalizeOrderNumber((string) data_get($lastOrder, 'order_number', ''));
            $isExpectedOrder = ! $normalizedOrderNumber || $normalizedOrderNumber === $lastOrderNumber;

            if ($isExpectedOrder) {
                $order = $lastOrder;
            }
        }

        if (! $order) {
            return redirect()->route('home');
        }

        $whatsappNumber = $this->whatsappNumberDigits();

        return view('thedolci.success', [
            'cartCount' => ThedolciCart::count(),
            'order' => $order,
            'whatsappNumber' => $whatsappNumber,
            'whatsappUrl' => $this->buildOrderWhatsappUrl($order, $whatsappNumber),
        ]);
    }

    public function track(Request $request): View
    {
        $order = null;
        $lookupFailed = false;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'order_number' => ['required', 'string', 'max:50'],
                'customer_email' => ['required', 'email', 'max:120'],
            ]);

            $orderNumber = $this->normalizeOrderNumber($data['order_number']);
            $customerEmail = $this->normalizeEmail($data['customer_email']);

            if (Schema::hasTable('thedolci_orders')) {
                $order = ThedolciOrder::query()
                    ->whereRaw('UPPER(REPLACE(TRIM(order_number), " ", "")) = ?', [$orderNumber])
                    ->whereRaw('LOWER(TRIM(customer_email)) = ?', [$customerEmail])
                    ->first();
            }

            if (! $order) {
                $order = $this->findFallbackOrder($orderNumber, $customerEmail);
            }

            $lookupFailed = $order === null;
        }

        return view('thedolci.track-order', [
            'cartCount' => ThedolciCart::count(),
            'order' => $order,
            'lookupFailed' => $lookupFailed,
        ]);
    }

    public function deliverySlots(): JsonResponse
    {
        return response()->json([
            'slots' => ThedolciCatalog::deliverySlots()->values(),
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        if (! $this->couponsEnabledForCurrentUser()) {
            return response()->json([
                'valid' => false,
                'code' => null,
                'discount' => 0,
                'message' => 'Coupons are available for admin only.',
            ], 403);
        }

        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:30'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $result = ThedolciCatalog::validateCoupon($data['coupon_code'], (float) $data['subtotal']);

        return response()->json($result);
    }

    private function incrementCouponUsage(?string $couponCode): void
    {
        if (! $couponCode || ! Schema::hasTable('thedolci_coupons')) {
            return;
        }

        try {
            ThedolciCoupon::query()
                ->whereRaw('UPPER(code) = ?', [strtoupper($couponCode)])
                ->increment('usage_count');
        } catch (QueryException) {
            // No-op fallback.
        }
    }

    private function couponsEnabledForCurrentUser(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    private function generateOrderNumber(): string
    {
        return 'TDL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function normalizeOrderNumber(string $orderNumber): string
    {
        $normalized = trim($this->normalizeLocalizedDigits($orderNumber));
        $normalized = preg_replace('/\s+/', '', $normalized) ?? '';

        return Str::upper($normalized);
    }

    private function normalizeLocalizedDigits(string $value): string
    {
        $arabicIndic = ["\u{0660}", "\u{0661}", "\u{0662}", "\u{0663}", "\u{0664}", "\u{0665}", "\u{0666}", "\u{0667}", "\u{0668}", "\u{0669}"];
        $easternArabicIndic = ["\u{06F0}", "\u{06F1}", "\u{06F2}", "\u{06F3}", "\u{06F4}", "\u{06F5}", "\u{06F6}", "\u{06F7}", "\u{06F8}", "\u{06F9}"];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace(
            [...$arabicIndic, ...$easternArabicIndic],
            [...$latin, ...$latin],
            $value
        );
    }

    /**
     * @param array<string, mixed> $orderPayload
     */
    private function storeFallbackOrder(array $orderPayload): void
    {
        $orders = session(self::FALLBACK_ORDERS_SESSION_KEY, []);

        if (! is_array($orders)) {
            $orders = [];
        }

        $normalizedOrderNumber = $this->normalizeOrderNumber((string) data_get($orderPayload, 'order_number', ''));

        if ($normalizedOrderNumber === '') {
            return;
        }

        $orderPayload['order_number'] = $normalizedOrderNumber;
        $orderPayload['customer_email'] = $this->normalizeEmail((string) data_get($orderPayload, 'customer_email', ''));

        $orders[$normalizedOrderNumber] = $orderPayload;

        if (count($orders) > 10) {
            $orders = array_slice($orders, -10, null, true);
        }

        session()->put(self::FALLBACK_ORDERS_SESSION_KEY, $orders);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findFallbackOrder(string $orderNumber, string $customerEmail): ?array
    {
        $order = $this->findFallbackOrderByNumber($orderNumber);

        if (! $order) {
            return null;
        }

        $storedEmail = $this->normalizeEmail((string) data_get($order, 'customer_email', ''));

        return $storedEmail === $customerEmail ? $order : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findFallbackOrderByNumber(string $orderNumber): ?array
    {
        $orders = session(self::FALLBACK_ORDERS_SESSION_KEY, []);

        if (! is_array($orders)) {
            return null;
        }

        $order = $orders[$orderNumber] ?? null;

        return is_array($order) ? $order : null;
    }

    private function whatsappNumberDigits(): ?string
    {
        $rawNumber = (string) (config('services.whatsapp_booking.number') ?: env('WHATSAPP_BOOKING_NUMBER', ''));
        $digitsOnly = preg_replace('/\D+/', '', $rawNumber);

        return $digitsOnly ?: null;
    }

    private function buildOrderWhatsappUrl(ThedolciOrder|array $order, ?string $whatsappNumber): ?string
    {
        if (! $whatsappNumber) {
            return null;
        }

        $orderData = $order instanceof ThedolciOrder ? $order->toArray() : $order;
        $message = $this->formatOrderWhatsappMessage($orderData);

        return sprintf('https://wa.me/%s?text=%s', $whatsappNumber, rawurlencode($message));
    }

    /**
     * @param array<string, mixed> $order
     */
    private function formatOrderWhatsappMessage(array $order): string
    {
        $items = collect($order['items'] ?? [])
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $name = trim((string) ($item['name'] ?? 'Product'));
                $size = trim((string) ($item['size'] ?? ''));
                $quantity = (int) ($item['quantity'] ?? 1);

                $details = [];
                if ($size !== '') {
                    $details[] = 'Size: ' . $size;
                }

                if (! empty(data_get($item, 'customizations.add_pepper'))) {
                    $details[] = 'Pepper add-on';
                }

                $packagingType = trim((string) data_get($item, 'customizations.packaging_type', ''));
                if ($packagingType !== '') {
                    $details[] = 'Packaging: ' . $packagingType;
                }

                $suffix = empty($details) ? '' : ' (' . implode(' - ', $details) . ')';

                return sprintf('- %s x%d%s', $name, $quantity, $suffix);
            })
            ->filter()
            ->implode("\n");

        $orderNumber = (string) ($order['order_number'] ?? '-');
        $customerName = (string) ($order['customer_name'] ?? '-');
        $customerPhone = (string) ($order['customer_phone'] ?? '-');
        $fulfillmentType = (string) ($order['fulfillment_type'] ?? 'delivery');
        $deliveryDate = (string) ($order['delivery_date'] ?? '-');
        $deliveryTime = (string) ($order['delivery_time'] ?? '-');
        $deliveryAddress = trim((string) ($order['delivery_address'] ?? ''));
        $total = number_format((float) ($order['total'] ?? 0), 2);
        $notes = trim((string) ($order['notes'] ?? ''));

        $fulfillmentLabel = $fulfillmentType === 'pickup' ? 'Pickup' : 'Delivery';
        $addressLine = $deliveryAddress !== '' ? $deliveryAddress : 'N/A';
        $notesLine = $notes !== '' ? $notes : 'N/A';

        return <<<MSG
Hello, I want to confirm my order and pay via Click.

Order Number: {$orderNumber}
Name: {$customerName}
Phone: {$customerPhone}
Fulfillment: {$fulfillmentLabel}
Date: {$deliveryDate}
Time: {$deliveryTime}
Address: {$addressLine}

Items:
{$items}

Total: \${$total}
Notes: {$notesLine}
MSG;
    }
}
