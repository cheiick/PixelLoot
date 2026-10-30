<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderStatusNotifier;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, Order $order, OrderStatusNotifier $notifier)
{
    $validated = $request->validate([
        'status' => ['required', 'in:pending,paid,shipped,delivered'],
    ]);

    $statusChanged = $order->status !== $validated['status'];
    $order->status = $validated['status'];
    $order->save();

    if ($statusChanged || ! $order->customer_status_emailed_at) {
        $notifier->sendIfNeeded($order->fresh());
    }

    return redirect()->route('admin.orders.index')
    ->with('success', 'Statut de la commande mis à jour.');
}
}
