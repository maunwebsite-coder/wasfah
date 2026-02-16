<?php

namespace App\Http\Controllers\Thedolci;

use App\Http\Controllers\Controller;
use App\Models\ThedolciOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(): View
    {
        $orders = Schema::hasTable('thedolci_orders')
            ? ThedolciOrder::query()->latest()->paginate(20)
            : collect();

        return view('thedolci.admin.orders.index', [
            'orders' => $orders,
            'dbReady' => Schema::hasTable('thedolci_orders'),
        ]);
    }

    public function show(ThedolciOrder $order): View
    {
        return view('thedolci.admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, ThedolciOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,confirmed,preparing,ready,out_for_delivery,completed,cancelled'],
            'payment_status' => ['required', 'in:pending,paid,failed'],
        ]);

        $order->update($data);

        return back()->with('success', 'Order status updated.');
    }
}
