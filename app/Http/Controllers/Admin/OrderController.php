<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

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
    public function updateStatus(Request $request, Order $order)
{
    $request->validate([
        'status' => ['required', 'in:pending,paid,shipped,delivered'],
    ]);

    $order->status = $request->input('status');
    $order->save();

    return redirect()->route('admin.orders.index')
    ->with('success', 'Statut de la commande mis à jour.');
}
}
