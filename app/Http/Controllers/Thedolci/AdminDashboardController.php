<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciOrder;
use App\Models\ThedolciProduct;
use App\Support\ThedolciCatalog;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        ThedolciCatalog::bootstrapDefaultsInDatabase();

        if (! Schema::hasTable('thedolci_orders')) {
            return view('thedolci.admin.dashboard', [
                'ordersCount' => 0,
                'revenue' => 0,
                'averageOrderValue' => 0,
                'productsCount' => Schema::hasTable('thedolci_products') ? ThedolciProduct::query()->count() : 0,
                'latestOrders' => collect(),
            ]);
        }

        $ordersCount = ThedolciOrder::query()->count();
        $revenue = (float) ThedolciOrder::query()
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $averageOrderValue = $ordersCount > 0
            ? round($revenue / $ordersCount, 2)
            : 0;

        $latestOrders = ThedolciOrder::query()
            ->latest()
            ->take(10)
            ->get();

        return view('thedolci.admin.dashboard', [
            'ordersCount' => $ordersCount,
            'revenue' => round($revenue, 2),
            'averageOrderValue' => $averageOrderValue,
            'productsCount' => Schema::hasTable('thedolci_products') ? ThedolciProduct::query()->count() : 0,
            'latestOrders' => $latestOrders,
        ]);
    }
}
