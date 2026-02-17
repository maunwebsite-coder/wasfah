<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciStorefrontSetting;
use App\Support\ThedolciCart;
use App\Support\ThedolciCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    private const NEWSLETTER_COUPON_CODE = 'FIRST10';

    public function home(): View
    {
        ThedolciCatalog::bootstrapDefaultsInDatabase();

        $products = ThedolciCatalog::products();
        $hasLimitedEditionColumn = Schema::hasTable('thedolci_products')
            && Schema::hasColumn('thedolci_products', 'show_limited_edition');

        $featuredSeasonal = $hasLimitedEditionColumn
            ? $products->firstWhere('show_limited_edition', true)
            : $products->firstWhere('is_seasonal', true);

        return view('thedolci.home', [
            'cartCount' => ThedolciCart::count(),
            'hero' => ThedolciStorefrontSetting::heroContent(),
            'featuredSeasonal' => $featuredSeasonal,
            'bestSellers' => ThedolciCatalog::bestSellers()->take(4),
            'reviews' => ThedolciCatalog::featuredReviews(),
            'instagramPosts' => ThedolciCatalog::instagramFeed(),
            'products' => $products,
        ]);
    }

    public function shop(Request $request): View
    {
        $filter = strtolower((string) $request->query('filter', 'all'));

        $products = ThedolciCatalog::products();

        if ($filter === 'seasonal') {
            $products = $products->where('is_seasonal', true)->values();
        } elseif ($filter === 'best-sellers') {
            $products = $products->where('is_best_seller', true)->values();
        }

        return view('thedolci.shop', [
            'cartCount' => ThedolciCart::count(),
            'products' => $products,
            'activeFilter' => $filter,
        ]);
    }

    public function product(string $slug): View
    {
        $product = ThedolciCatalog::findProduct($slug);

        abort_if(! $product, 404);

        $related = ThedolciCatalog::products()
            ->where('slug', '!=', $slug)
            ->take(3)
            ->values();

        return view('thedolci.product', [
            'cartCount' => ThedolciCart::count(),
            'product' => $product,
            'relatedProducts' => $related,
        ]);
    }

    public function seasonal(): View
    {
        return view('thedolci.seasonal', [
            'cartCount' => ThedolciCart::count(),
            'seasonalProducts' => ThedolciCatalog::seasonalProducts(),
        ]);
    }

    public function about(): View
    {
        return view('thedolci.about', [
            'cartCount' => ThedolciCart::count(),
        ]);
    }

    public function reviews(): View
    {
        return view('thedolci.reviews', [
            'cartCount' => ThedolciCart::count(),
            'reviews' => ThedolciCatalog::reviews(),
        ]);
    }

    public function loyalty(): View
    {
        return view('thedolci.loyalty', [
            'cartCount' => ThedolciCart::count(),
        ]);
    }

    public function faq(): View
    {
        return view('thedolci.faq', [
            'cartCount' => ThedolciCart::count(),
        ]);
    }

    public function contact(): View
    {
        return view('thedolci.contact', [
            'cartCount' => ThedolciCart::count(),
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:120'],
        ]);

        $subscribers = collect(session('thedolci.newsletter', []))
            ->push([
                'email' => strtolower($data['email']),
                'subscribed_at' => now()->toDateTimeString(),
            ])
            ->unique('email')
            ->values()
            ->all();

        session(['thedolci.newsletter' => $subscribers]);
        session(['thedolci.preferred_coupon' => self::NEWSLETTER_COUPON_CODE]);

        $couponCode = self::NEWSLETTER_COUPON_CODE;
        $activeCoupon = ThedolciCart::coupon();
        $activeCouponCode = strtoupper((string) ($activeCoupon['code'] ?? ''));

        if ($activeCouponCode === $couponCode && ((float) ($activeCoupon['discount'] ?? 0)) > 0) {
            return back()->with('success', 'Subscribed successfully. Your FIRST10 discount is already active.');
        }

        $validation = ThedolciCatalog::validateCoupon($couponCode, ThedolciCart::subtotal());

        if ($validation['valid']) {
            ThedolciCart::setCoupon([
                'code' => $validation['code'],
                'discount' => $validation['discount'],
            ]);

            return back()->with('success', 'Subscribed successfully. FIRST10 has been activated for your cart.');
        }

        return back()->with('success', 'Subscribed successfully. FIRST10 is saved and ready when your cart qualifies.');
    }

    public function instagramFeed(): JsonResponse
    {
        return response()->json([
            'posts' => ThedolciCatalog::instagramFeed()->values(),
        ]);
    }

    public function featuredReviews(): JsonResponse
    {
        return response()->json([
            'reviews' => ThedolciCatalog::featuredReviews()->values(),
        ]);
    }
}
