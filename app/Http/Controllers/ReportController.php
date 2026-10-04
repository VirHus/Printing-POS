<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input(
            'from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $to = $request->input(
            'to',
            now()->format('Y-m-d')
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Dates
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        $stores = $request->user()->isAdministrator() ? Store::orderBy('name')->get() : collect();

        $fromDate = Carbon::parse($from)
            ->startOfDay();

        $toDate = Carbon::parse($to)
            ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        $sales = Sale::visibleTo($request->user(), $request->integer('store_id'))->with([
            'user',
            'items.product',
            'store',
        ])
            ->whereBetween(
                'created_at',
                [$fromDate, $toDate]
            )
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalRevenue = $sales->sum(
            'total_amount'
        );

        $transactionCount = $sales->count();

        $totalItems = $sales->sum(function ($sale) {

            return $sale->items->sum(
                'quantity'
            );

        });

        /*
        |--------------------------------------------------------------------------
        | Sales By Service
        |--------------------------------------------------------------------------
        */

        $serviceSales = SaleItem::with([
            'product',
            'sale',
        ])
            ->whereHas('sale', function ($query) use (
                $fromDate,
                $toDate,
                $request
            ) {

                $query->visibleTo($request->user(), $request->integer('store_id'));

                $query->whereBetween(
                    'created_at',
                    [$fromDate, $toDate]
                );

            })
            ->get()
            ->groupBy(function ($item) {

                return $item->product->name;

            })
            ->map(function ($items) {

                return [
                    'quantity' => $items->sum('quantity'),

                    'revenue' => $items->sum(
                        'subtotal'
                    ),
                ];

            })
            ->sortByDesc('revenue');

        /*
        |--------------------------------------------------------------------------
        | Sales By Cashier
        |--------------------------------------------------------------------------
        */

        $cashierSales = $sales
            ->groupBy(function ($sale) {

                return $sale->user->name
                    ?? 'Unknown';

            })
            ->map(function ($sales) {

                return [
                    'transactions' => $sales->count(),

                    'revenue' => $sales->sum(
                        'total_amount'
                    ),
                ];

            })
            ->sortByDesc('revenue');

        return view(
            'reports.index',
            compact(
                'from',
                'to',
                'totalRevenue',
                'transactionCount',
                'totalItems',
                'serviceSales',
                'cashierSales',
                'sales',
                'stores'
            )
        );
    }
}
