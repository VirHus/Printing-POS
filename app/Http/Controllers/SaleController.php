<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['store_id' => 'nullable|integer|exists:stores,id']);
        $stores = $request->user()->isAdministrator() ? Store::orderBy('name')->get() : collect();
        $sales = Sale::visibleTo($request->user(), $request->integer('store_id'))->with(['items.product', 'user', 'store'])
            ->latest()
            ->paginate(20)->withQueryString();

        return view('sales.index', compact('sales', 'stores'));
    }

    public function show(Request $request, Sale $sale): View
    {
        abort_unless($request->user()->isAdministrator() || ($request->user()->store_id !== null && $sale->store_id === $request->user()->store_id), 404);
        $sale->load(['items.product', 'user', 'store']);

        return view('sales.show', compact('sale'));
    }
}
