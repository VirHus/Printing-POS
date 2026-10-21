<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:day,week,month',
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);
        $period = $validated['period'] ?? 'day';
        $storeId = $request->user()->isAdministrator() ? ($validated['store_id'] ?? null) : null;
        $start = match ($period) {
            'week' => today()->subDays(6),
            'month' => today()->startOfMonth(),
            default => today(),
        };
        $end = today()->addDay();
        $days = (int) $start->diffInDays($end);
        $baseQuery = Sale::visibleTo($request->user(), $storeId);
        $currentQuery = (clone $baseQuery)->where('created_at', '>=', $start)->where('created_at', '<', $end);
        $todaySales = (float) (clone $currentQuery)->sum('total_amount');
        $todayTransactions = (clone $currentQuery)->count();
        $previousSales = (float) (clone $baseQuery)->where('created_at', '>=', $start->copy()->subDays($days))
            ->where('created_at', '<', $start)->sum('total_amount');
        $salesGrowth = $previousSales > 0 ? round(($todaySales - $previousSales) / $previousSales * 100, 1) : null;
        $periodLabel = match ($period) {
            'week' => 'Last 7 days',
            'month' => 'This month',
            default => 'Today',
        };
        $activeServices = Product::where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->count();
        $totalServices = Product::count();
        $recentSales = (clone $currentQuery)->with(['user', 'store'])->latest()->limit(10)->get();
        $stores = $request->user()->isAdministrator() ? Store::orderBy('name')->get() : collect();
        $reportFilters = ['from' => $start->toDateString(), 'to' => $end->copy()->subDay()->toDateString()];
        if ($storeId) {
            $reportFilters['store_id'] = $storeId;
        }

        $bucketExpression = $period === 'day'
            ? 'SUBSTR(created_at, 12, 2)'
            : 'DATE(created_at)';
        $buckets = (clone $currentQuery)->selectRaw("{$bucketExpression} as bucket, SUM(total_amount) as revenue, COUNT(*) as transactions")
            ->groupByRaw($bucketExpression)->get()->keyBy('bucket');
        $chart = [];
        $bucketCount = $period === 'day' ? 24 : $days;
        for ($index = 0; $index < $bucketCount; $index++) {
            $date = $start->copy()->addDays($index);
            $key = $period === 'day' ? sprintf('%02d', $index) : $date->toDateString();
            $chart[] = [
                'label' => $period === 'day' ? sprintf('%02d:00', $index) : $date->format('M j'),
                'revenue' => (float) ($buckets->get($key)?->revenue ?? 0),
                'transactions' => (int) ($buckets->get($key)?->transactions ?? 0),
            ];
        }
        $chartMaximum = max(1, ceil(max(array_column($chart, 'revenue')) / 4) * 4);
        $transactionMaximum = max(1, max(array_column($chart, 'transactions')));
        $chartPoints = [];
        $sparkPoints = [];
        foreach ($chart as $index => $bucket) {
            $fraction = $index / max(1, $bucketCount - 1);
            $chartPoints[] = round(70 + $fraction * 620, 2).','.round(195 - $bucket['revenue'] / $chartMaximum * 170, 2);
            $sparkPoints[] = round($fraction * 440, 2).','.round(76 - $bucket['revenue'] / $chartMaximum * 68, 2);
        }

        return view('dashboard', compact('todaySales', 'todayTransactions', 'activeServices', 'totalServices', 'recentSales',
            'period', 'periodLabel', 'storeId', 'stores', 'salesGrowth', 'previousSales', 'reportFilters',
            'chart', 'chartMaximum', 'transactionMaximum', 'chartPoints', 'sparkPoints'));
    }
}
