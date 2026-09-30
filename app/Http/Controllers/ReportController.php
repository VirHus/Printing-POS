<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        ]);


        $fromDate = Carbon::parse($from)
            ->startOfDay();

        $toDate = Carbon::parse($to)
            ->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        $sales = Sale::with([
            'user',
            'items.product'
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
            'sale'
        ])
        ->whereHas('sale', function ($query) use (
            $fromDate,
            $toDate
        ) {

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
                'sales'
            )
        );
    }
}