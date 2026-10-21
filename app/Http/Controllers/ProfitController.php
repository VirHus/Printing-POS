<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfitController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['store_id' => 'nullable|integer|exists:stores,id', 'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?: now()->toDateString();
        if ($from > $to) {
            throw ValidationException::withMessages(['to' => 'End date must be on or after the start date.']);
        }
        $storeId = $request->integer('store_id');
        $sales = Sale::when($storeId, fn ($query) => $query->where('store_id', $storeId))->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
        $revenue = (float) (clone $sales)->sum('total_amount');
        $materialCosts = (float) (clone $sales)->sum('cost_total');
        $uncosted = (clone $sales)->where('cost_complete', false)->count();
        $expenseQuery = Expense::when($storeId, fn ($query) => $query->where('store_id', $storeId))->whereDate('expense_date', '>=', $from)->whereDate('expense_date', '<=', $to);
        $operatingExpenses = (float) (clone $expenseQuery)->sum('amount');
        $expenses = (clone $expenseQuery)->latest('expense_date')->paginate(20);
        $stockLosses = (float) StockMovement::whereIn('type', ['Waste', 'Adjustment out'])->whereHas('inventoryItem', fn ($query) => $query->when($storeId, fn ($query) => $query->where('store_id', $storeId)))->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])->sum('total_cost');
        $grossProfit = round($revenue - $materialCosts, 2);
        $netProfit = round($grossProfit - $operatingExpenses - $stockLosses, 2);
        $stores = Store::orderBy('name')->get();

        return view('reports.profit', compact('from', 'to', 'storeId', 'stores', 'revenue', 'materialCosts', 'uncosted', 'operatingExpenses', 'expenses', 'stockLosses', 'grossProfit', 'netProfit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['store_id' => 'required|integer|exists:stores,id', 'category' => 'required|in:Rent,Utilities,Salaries,Maintenance,Transport,Other', 'description' => 'required|string|max:255', 'amount' => 'required|numeric|min:0.01|max:100000000|decimal:0,2', 'expense_date' => 'required|date_format:Y-m-d|before_or_equal:today']);
        Expense::create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('profit.index', ['store_id' => $data['store_id'], 'from' => Carbon::parse($data['expense_date'])->startOfMonth()->toDateString(), 'to' => now()->toDateString()])->with('success', 'Operating expense recorded.');
    }
}
