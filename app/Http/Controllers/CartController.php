<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    /**
     * Display the cart page
     */
    public function index(): View
    {
        return view('cart');
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'tool_id' => 'required|exists:tools,id',
            'quantity' => 'integer|min:1|max:10'
        ]);

        $tool = Tool::findOrFail($request->tool_id);
        $quantity = $request->quantity ?? 1;
        
        // Get user ID or session ID
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        // Check if item already exists in cart
        $existingItem = Cart::forUser($userId, $sessionId)
            ->where('tool_id', $tool->id)
            ->first();

        if ($existingItem) {
            // Update quantity and ensure price is correct
            $existingItem->increment('quantity', $quantity);
            if ($existingItem->price != $tool->price) {
                $existingItem->update(['price' => $tool->price]);
            }
            $message = 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„ÙƒÙ…ÙŠØ© ÙÙŠ Ø§Ù„Ø³Ù„Ø©';
        } else {
            Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'tool_id' => $tool->id,
                'quantity' => $quantity,
                'price' => $tool->price,
                'amazon_url' => $tool->amazon_url,
                'affiliate_url' => $tool->affiliate_url
            ]);
            $message = 'ØªÙ… Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ù…Ù†ØªØ¬ Ø¥Ù„Ù‰ Ø§Ù„Ø³Ù„Ø©';
        }

        $cartCount = $this->getCartCount();
        
        // Get the quantity for this specific tool
        $toolQuantity = Cart::forUser($userId, $sessionId)
            ->where('tool_id', $tool->id)
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_count' => $cartCount,
            'tool_quantity' => $toolQuantity
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        if (!$this->cartItemBelongsToCurrentUser($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.'
            ], 404);
        }

        $cart->update(['quantity' => $request->quantity]);

        $cartCount = $this->getCartCount();
        $total = round($this->getCartItems()->sum('total_price'), 2);

        return response()->json([
            'success' => true,
            'message' => 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„ÙƒÙ…ÙŠØ©',
            'cart_count' => $cartCount,
            'item_total' => round($cart->total_price, 2),
            'cart_total' => $total
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Cart $cart): JsonResponse
    {
        if (!$this->cartItemBelongsToCurrentUser($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.'
            ], 404);
        }

        $cart->delete();

        $cartCount = $this->getCartCount();
        $total = round($this->getCartItems()->sum('total_price'), 2);

        return response()->json([
            'success' => true,
            'message' => 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ù…Ù†ØªØ¬ Ù…Ù† Ø§Ù„Ø³Ù„Ø©',
            'cart_count' => $cartCount,
            'cart_total' => $total
        ]);
    }

    /**
     * Remove item from cart by tool ID
     */
    public function removeByToolId(Request $request): JsonResponse
    {
        $request->validate([
            'tool_id' => 'required|integer|exists:tools,id'
        ]);

        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        $cartItem = Cart::forUser($userId, $sessionId)
            ->where('tool_id', $request->tool_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ø§Ù„Ù…Ù†ØªØ¬ ØºÙŠØ± Ù…ÙˆØ¬ÙˆØ¯ ÙÙŠ Ø§Ù„Ø³Ù„Ø©'
            ], 404);
        }

        $cartItem->delete();

        $cartCount = $this->getCartCount();
        $total = round($this->getCartItems()->sum('total_price'), 2);

        return response()->json([
            'success' => true,
            'message' => 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ù…Ù†ØªØ¬ Ù…Ù† Ø§Ù„Ø³Ù„Ø©',
            'cart_count' => $cartCount,
            'cart_total' => $total
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        Cart::forUser($userId, $sessionId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'ØªÙ… Ù…Ø³Ø­ Ø§Ù„Ø³Ù„Ø© Ø¨Ø§Ù„ÙƒØ§Ù…Ù„',
            'cart_count' => 0
        ]);
    }

    /**
     * Get cart items for current user/session
     */
    private function getCartItems()
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        $cartItems = Cart::forUser($userId, $sessionId)
            ->with('tool')
            ->get();

        // Fix any items that have incorrect price (total instead of unit price)
        foreach ($cartItems as $item) {
            if ($item->tool && $item->price != $item->tool->price) {
                $item->update(['price' => $item->tool->price]);
            }
        }

        return $cartItems;
    }

    /**
     * Ensure current user/session owns the target cart item
     */
    private function cartItemBelongsToCurrentUser(Cart $cart): bool
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        return Cart::forUser($userId, $sessionId)
            ->whereKey($cart->id)
            ->exists();
    }

    /**
     * Get cart count for current user/session
     */
    private function getCartCount(): int
    {
        return $this->getCartItems()->sum('quantity');
    }

    /**
     * Get cart count for API
     */
    public function count(): JsonResponse
    {
        $count = $this->getCartCount();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get quantities for all tools in cart
     */
    public function quantities(): JsonResponse
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        $cartItems = Cart::forUser($userId, $sessionId)
            ->select('tool_id', 'quantity')
            ->get();

        $quantities = [];
        foreach ($cartItems as $item) {
            $quantities[$item->tool_id] = $item->quantity;
        }

        return response()->json([
            'success' => true,
            'quantities' => $quantities
        ]);
    }

    /**
     * Redirect to Amazon cart with selected items
     */
    public function checkoutAmazon(): RedirectResponse
    {
        $cartItems = $this->getCartItems();
        
        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Ø§Ù„Ø³Ù„Ø© ÙØ§Ø±ØºØ©');
        }

        // Get Amazon URLs from cart items
        $amazonUrls = $cartItems->pluck('amazon_url')->filter()->unique();
        
        if ($amazonUrls->isEmpty()) {
            return redirect()->back()->with('error', 'Ù„Ø§ ØªÙˆØ¬Ø¯ Ù…Ù†ØªØ¬Ø§Øª Amazon ÙÙŠ Ø§Ù„Ø³Ù„Ø©');
        }

        // If there's only one product, redirect directly to it
        if ($amazonUrls->count() === 1) {
            return redirect($amazonUrls->first());
        }

        // For multiple products, create Amazon cart URL
        $amazonCartUrl = $this->buildAmazonCartUrl($cartItems);
        
        return redirect($amazonCartUrl);
    }

    /**
     * Build Amazon cart URL with multiple items using Amazon Cart API
     */
    private function buildAmazonCartUrl($cartItems)
    {
        // Amazon Cart API base URL
        $baseUrl = 'https://www.amazon.ae/gp/aws/cart/add.html';
        
        // Get affiliate tag from config or use default
        $affiliateTag = config('services.amazon.affiliate_tag', 'wasfah-21');
        
        // Build query parameters
        $params = [
            'AssociateTag' => $affiliateTag
        ];
        
        // Add each item to the cart
        $itemIndex = 1;
        $validItems = 0;
        
        foreach ($cartItems as $item) {
            if ($item->amazon_url) {
                // Extract ASIN from Amazon URL
                $asin = $this->extractAsinFromUrl($item->amazon_url);
                
                if ($asin) {
                    $params["ASIN.{$itemIndex}"] = $asin;
                    $params["Quantity.{$itemIndex}"] = $item->quantity;
                    $itemIndex++;
                    $validItems++;
                    
                    // Log for debugging
                    \Log::info("Amazon Cart: Added item {$item->tool->name} with ASIN {$asin} and quantity {$item->quantity}");
                } else {
                    \Log::warning("Amazon Cart: Could not extract ASIN from URL: {$item->amazon_url}");
                }
            }
        }
        
        // If no valid ASINs found, redirect to first item
        if ($validItems === 0) {
            \Log::warning("Amazon Cart: No valid ASINs found, redirecting to first item");
            return $cartItems->first()->amazon_url;
        }
        
        // Build the final URL
        $finalUrl = $baseUrl . '?' . http_build_query($params);
        
        \Log::info("Amazon Cart: Generated URL with {$validItems} items: {$finalUrl}");
        
        return $finalUrl;
    }

    /**
     * Extract ASIN from Amazon URL
     */
    private function extractAsinFromUrl($url)
    {
        // Clean the URL first
        $url = urldecode($url);
        
        // Try to extract ASIN from various Amazon URL formats
        $patterns = [
            // Standard product URLs
            '/\/dp\/([A-Z0-9]{10})/',
            '/\/product\/([A-Z0-9]{10})/',
            '/\/gp\/product\/([A-Z0-9]{10})/',
            '/\/gp\/aw\/d\/([A-Z0-9]{10})/',
            '/\/exec\/obidos\/ASIN\/([A-Z0-9]{10})/',
            
            // Query parameters
            '/[?&]asin=([A-Z0-9]{10})/',
            '/[?&]ASIN=([A-Z0-9]{10})/',
            '/[?&]m=([A-Z0-9]{10})/',
            '/[?&]ref=([A-Z0-9]{10})/',
            
            // Mobile URLs
            '/\/m\/([A-Z0-9]{10})/',
            
            // Other formats
            '/\/[^\/]*\/([A-Z0-9]{10})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $asin = $matches[1];
                // Validate ASIN format (10 characters, alphanumeric)
                if (preg_match('/^[A-Z0-9]{10}$/', $asin)) {
                    return $asin;
                }
            }
        }
        
        // If no ASIN found, try to extract from the URL path
        $pathParts = explode('/', parse_url($url, PHP_URL_PATH));
        foreach ($pathParts as $part) {
            if (preg_match('/^[A-Z0-9]{10}$/', $part)) {
                return $part;
            }
        }
        
        return null;
    }

    /**
     * Fix cart prices (ensure all prices are unit prices, not total prices)
     */
    public function fixCartPrices(): JsonResponse
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        $cartItems = Cart::forUser($userId, $sessionId)
            ->with('tool')
            ->get();

        $fixedCount = 0;
        $fixedItems = [];
        
        foreach ($cartItems as $item) {
            if ($item->tool && $item->price != $item->tool->price) {
                $oldPrice = $item->price;
                $item->update(['price' => $item->tool->price]);
                $fixedCount++;
                
                $fixedItems[] = [
                    'tool_name' => $item->tool->name,
                    'old_price' => $oldPrice,
                    'new_price' => $item->tool->price,
                    'quantity' => $item->quantity
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "ØªÙ… Ø¥ØµÙ„Ø§Ø­ {$fixedCount} Ù…Ù†ØªØ¬ ÙÙŠ Ø§Ù„Ø³Ù„Ø©",
            'fixed_count' => $fixedCount,
            'fixed_items' => $fixedItems
        ]);
    }

    /**
     * Fix all cart prices in database (admin only)
     */
    public function fixAllCartPrices(): JsonResponse
    {
        // This should be admin only in production
        $allCartItems = Cart::with('tool')->get();
        
        $fixedCount = 0;
        $fixedItems = [];
        
        foreach ($allCartItems as $item) {
            if ($item->tool && $item->price != $item->tool->price) {
                $oldPrice = $item->price;
                $item->update(['price' => $item->tool->price]);
                $fixedCount++;
                
                $fixedItems[] = [
                    'cart_id' => $item->id,
                    'tool_name' => $item->tool->name,
                    'old_price' => $oldPrice,
                    'new_price' => $item->tool->price,
                    'quantity' => $item->quantity
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "ØªÙ… Ø¥ØµÙ„Ø§Ø­ {$fixedCount} Ø¹Ù†ØµØ± ÙÙŠ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª",
            'fixed_count' => $fixedCount,
            'fixed_items' => $fixedItems
        ]);
    }

    /**
     * Test Amazon cart functionality
     */
    public function testAmazonCart()
    {
        $cartItems = $this->getCartItems();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'error' => 'Ø§Ù„Ø³Ù„Ø© ÙØ§Ø±ØºØ©',
                'message' => 'Ø£Ø¶Ù Ø¨Ø¹Ø¶ Ø§Ù„Ù…Ù†ØªØ¬Ø§Øª Ø¥Ù„Ù‰ Ø§Ù„Ø³Ù„Ø© Ø£ÙˆÙ„Ø§Ù‹ Ù„Ø§Ø®ØªØ¨Ø§Ø± ÙˆØ¸ÙŠÙØ© Amazon Cart'
            ]);
        }

        $testResults = [];
        $amazonUrls = $cartItems->pluck('amazon_url')->filter();
        
        foreach ($cartItems as $item) {
            $asin = $this->extractAsinFromUrl($item->amazon_url);
            $testResults[] = [
                'tool_name' => $item->tool->name,
                'amazon_url' => $item->amazon_url,
                'extracted_asin' => $asin,
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'total_price' => $item->total_price,
                'is_valid' => !is_null($asin)
            ];
        }

        $amazonCartUrl = $this->buildAmazonCartUrl($cartItems);
        
        return response()->json([
            'cart_items_count' => $cartItems->count(),
            'test_results' => $testResults,
            'amazon_cart_url' => $amazonCartUrl,
            'affiliate_tag' => config('services.amazon.affiliate_tag', 'wasfah-21'),
            'commission_info' => [
                'commission_rate' => '1% - 10% Ø­Ø³Ø¨ ÙØ¦Ø© Ø§Ù„Ù…Ù†ØªØ¬',
                'tracking_period' => '24 Ø³Ø§Ø¹Ø© Ù…Ù† Ø§Ù„Ù†Ù‚Ø± Ø¹Ù„Ù‰ Ø§Ù„Ø±Ø§Ø¨Ø·',
                'payment_method' => 'ØªØ­ÙˆÙŠÙ„ Ø¨Ù†ÙƒÙŠ Ø£Ùˆ Ø´ÙŠÙƒ',
                'minimum_payment' => '100 Ø¯Ø±Ù‡Ù… Ø¥Ù…Ø§Ø±Ø§ØªÙŠ',
                'note' => 'Ø³ØªØ­ØµÙ„ Ø¹Ù„Ù‰ Ø¹Ù…ÙˆÙ„Ø© Ø¹Ù†Ø¯ Ø´Ø±Ø§Ø¡ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…ÙŠÙ† Ù„Ù„Ù…Ù†ØªØ¬Ø§Øª'
            ]
        ]);
    }
}
