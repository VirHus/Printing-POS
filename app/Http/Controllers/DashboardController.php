<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::visibleTo(auth()->user())->whereDate('created_at', today())
            ->sum('total_amount');

        $todayTransactions = Sale::visibleTo(auth()->user())->whereDate('created_at', today())
            ->count();

        $activeServices = Product::where('is_active', true)
            ->count();

        $recentSales = Sale::visibleTo(auth()->user())->with(['items.product', 'user', 'store'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'todaySales',
            'todayTransactions',
            'activeServices',
            'recentSales'
        ));
    }
}
