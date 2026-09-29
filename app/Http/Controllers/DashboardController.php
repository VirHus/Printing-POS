<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', today())
            ->sum('total_amount');

        $todayTransactions = Sale::whereDate('created_at', today())
            ->count();

        $activeServices = Product::where('is_active', true)
            ->count();

        $recentSales = Sale::with(['items.product', 'user'])
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