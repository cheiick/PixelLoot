<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::whereIn('status', ['paid', 'shipped', 'delivered'])->sum('total');
        $monthRevenue = Order::whereIn('status', ['paid', 'shipped', 'delivered'])
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');
        $orderCount = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStockProducts = Product::where('stock', '<', 5)->orderBy('stock')->limit(8)->get();
        $customerCount = User::where('is_admin', false)->count();
        $productCount = Product::count();
        $latestOrders = Order::with('user')->latest()->limit(6)->get();
        $topProducts = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as quantity_sold')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'monthRevenue' => $monthRevenue,
            'orderCount' => $orderCount,
            'pendingOrders' => $pendingOrders,
            'lowStockProducts' => $lowStockProducts,
            'customerCount' => $customerCount,
            'productCount' => $productCount,
            'latestOrders' => $latestOrders,
            'topProducts' => $topProducts,
            'couponCount' => Coupon::count(),
        ]);
    }
}
