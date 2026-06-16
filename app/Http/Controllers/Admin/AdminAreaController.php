<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThedolciOrder;
use App\Models\ThedolciProduct;
use App\Models\ThedolciReview;
use Illuminate\Support\Facades\Schema;

class AdminAreaController extends Controller
{
    /**
     * Display thedolci-focused admin area page.
     */
    public function index()
    {
        $hasProductsTable = Schema::hasTable('thedolci_products');
        $hasOrdersTable = Schema::hasTable('thedolci_orders');
        $hasReviewsTable = Schema::hasTable('thedolci_reviews');
        $hasLimitedEditionColumn = $hasProductsTable && Schema::hasColumn('thedolci_products', 'show_limited_edition');

        $productCount = $hasProductsTable ? ThedolciProduct::query()->count() : 0;
        $activeProductCount = $hasProductsTable ? ThedolciProduct::query()->where('is_active', true)->count() : 0;
        $limitedEditionCount = $hasLimitedEditionColumn
            ? ThedolciProduct::query()->where('show_limited_edition', true)->count()
            : 0;

        $orderCount = $hasOrdersTable ? ThedolciOrder::query()->count() : 0;
        $newOrderCount = $hasOrdersTable ? ThedolciOrder::query()->where('status', 'new')->count() : 0;
        $completedOrderCount = $hasOrdersTable ? ThedolciOrder::query()->where('status', 'completed')->count() : 0;
        $pendingPaymentCount = $hasOrdersTable ? ThedolciOrder::query()->where('payment_status', 'pending')->count() : 0;
        $revenue = $hasOrdersTable
            ? (float) ThedolciOrder::query()
                ->where('status', '!=', 'cancelled')
                ->sum('total')
            : 0;

        $reviewCount = $hasReviewsTable ? ThedolciReview::query()->count() : 0;
        $activeReviewCount = $hasReviewsTable ? ThedolciReview::query()->where('is_active', true)->count() : 0;
        $featuredReviewCount = $hasReviewsTable ? ThedolciReview::query()->where('is_featured', true)->count() : 0;

        $schemaWarnings = [];

        if (! $hasProductsTable) {
            $schemaWarnings[] = 'The thedolci products table is not available yet.';
        }

        if (! $hasOrdersTable) {
            $schemaWarnings[] = 'The thedolci orders table is not available yet.';
        }

        if (! $hasReviewsTable) {
            $schemaWarnings[] = 'The thedolci reviews table is not available yet.';
        }

        $attentionItems = [
            [
                'label' => 'New Orders',
                'value' => $newOrderCount,
                'route' => 'thedolci.admin.orders.index',
                'cta' => 'Process now',
                'empty_state' => 'No new orders',
            ],
            [
                'label' => 'Pending Payments',
                'value' => $pendingPaymentCount,
                'route' => 'thedolci.admin.orders.index',
                'cta' => 'Review payments',
                'empty_state' => 'No pending payments',
            ],
            [
                'label' => 'Limited Edition Items',
                'value' => $limitedEditionCount,
                'route' => 'thedolci.admin.products.index',
                'cta' => 'Manage visibility',
                'empty_state' => 'No limited items enabled',
            ],
        ];

        $metrics = [
            [
                'label' => 'Products',
                'value' => $productCount,
                'route' => 'thedolci.admin.products.index',
                'hint' => 'Total products in catalog',
            ],
            [
                'label' => 'Active Products',
                'value' => $activeProductCount,
                'route' => 'thedolci.admin.products.index',
                'hint' => 'Visible on storefront',
            ],
            [
                'label' => 'Orders',
                'value' => $orderCount,
                'route' => 'thedolci.admin.orders.index',
                'hint' => 'All order records',
            ],
            [
                'label' => 'Completed Orders',
                'value' => $completedOrderCount,
                'route' => 'thedolci.admin.orders.index',
                'hint' => 'Delivered or fulfilled orders',
            ],
            [
                'label' => 'Revenue',
                'value' => $revenue,
                'route' => 'thedolci.admin.orders.index',
                'format' => 'currency',
                'hint' => 'Excluding cancelled orders',
            ],
            [
                'label' => 'Reviews',
                'value' => $reviewCount,
                'route' => 'thedolci.admin.reviews.index',
                'hint' => 'Total customer testimonials',
            ],
            [
                'label' => 'Active Reviews',
                'value' => $activeReviewCount,
                'route' => 'thedolci.admin.reviews.index',
                'hint' => 'Currently published',
            ],
            [
                'label' => 'Featured Reviews',
                'value' => $featuredReviewCount,
                'route' => 'thedolci.admin.reviews.index',
                'hint' => 'Pinned on storefront sections',
            ],
        ];

        $latestOrders = $hasOrdersTable
            ? ThedolciOrder::query()->latest()->take(6)->get([
                'id',
                'order_number',
                'customer_name',
                'status',
                'payment_status',
                'total',
                'created_at',
            ])
            : collect();

        $latestReviews = $hasReviewsTable
            ? ThedolciReview::query()->latest()->take(6)->get([
                'id',
                'customer_name',
                'rating',
                'review_text',
                'is_featured',
                'is_active',
                'created_at',
            ])
            : collect();

        $quickActions = [
            [
                'label' => 'Dashboard',
                'route' => 'thedolci.admin.dashboard',
            ],
            [
                'label' => 'Storefront',
                'route' => 'thedolci.admin.storefront.edit',
            ],
            [
                'label' => 'Products',
                'route' => 'thedolci.admin.products.index',
            ],
            [
                'label' => 'Add Product',
                'route' => 'thedolci.admin.products.create',
            ],
            [
                'label' => 'Reviews',
                'route' => 'thedolci.admin.reviews.index',
            ],
            [
                'label' => 'Add Review',
                'route' => 'thedolci.admin.reviews.create',
            ],
            [
                'label' => 'Orders',
                'route' => 'thedolci.admin.orders.index',
            ],
            [
                'label' => 'Open Store',
                'route' => 'thedolci.shop',
            ],
        ];

        $managementSections = [
            [
                'title' => 'Catalog',
                'description' => 'Manage products, visibility, pricing, and limited edition stock.',
                'icon' => 'fa-plus-circle',
                'items' => [
                    [
                        'label' => 'All Products',
                        'route' => 'thedolci.admin.products.index',
                    ],
                    [
                        'label' => 'Add Product',
                        'route' => 'thedolci.admin.products.create',
                    ],
                ],
            ],
            [
                'title' => 'Orders',
                'description' => 'Track order flow, payment state, and fulfillment progress.',
                'icon' => 'fa-receipt',
                'items' => [
                    [
                        'label' => 'All Orders',
                        'route' => 'thedolci.admin.orders.index',
                    ],
                ],
            ],
            [
                'title' => 'Reviews',
                'description' => 'Moderate testimonials and control featured storefront social proof.',
                'icon' => 'fa-star',
                'items' => [
                    [
                        'label' => 'All Reviews',
                        'route' => 'thedolci.admin.reviews.index',
                    ],
                    [
                        'label' => 'Add Review',
                        'route' => 'thedolci.admin.reviews.create',
                    ],
                ],
            ],
            [
                'title' => 'Storefront Content',
                'description' => 'Edit hero content and landing copy shown on the store homepage.',
                'icon' => 'fa-store',
                'items' => [
                    [
                        'label' => 'Storefront Settings',
                        'route' => 'thedolci.admin.storefront.edit',
                    ],
                ],
            ],
        ];

        return view('admin.admin-area', compact(
            'schemaWarnings',
            'metrics',
            'attentionItems',
            'latestOrders',
            'latestReviews',
            'quickActions',
            'managementSections'
        ));
    }
}
