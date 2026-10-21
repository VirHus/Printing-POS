<?php

namespace App\Http\Controllers;

use App\InventoryConsumption;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with([
                'products' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        $todaySales = Sale::where('store_id', auth()->user()->store_id ?? 0)->whereDate('created_at', today())
            ->sum('total_amount');

        return view('pos.index', compact(
            'categories',
            'todaySales'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $request->user()->store;
        if (! $store || ! $store->is_active) {
            return back()->withErrors(['store' => 'Please ask an administrator to assign you to an active store.'])->withInput();
        }
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['Cash', 'GCash', 'Maya', 'Bank Transfer', 'Card'])],
            'payment_reference' => ['required_unless:payment_method,Cash', 'nullable', 'string', 'max:100'],
            'cash_received' => ['required_if:payment_method,Cash', 'nullable', 'numeric', 'min:0'],
            'customer_name' => 'nullable|string|max:255',
            'order_notes' => 'nullable|string|max:1000',
        ]);

        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::with('category')->findOrFail($item['product_id']);

            if (! $product->is_active || ! $product->category?->is_active) {
                return back()->withErrors([
                    'items' => "The service {$product->name} is inactive.",
                ]);
            }

            $total += $product->price * $item['quantity'];
        }

        $isCashPayment = $validated['payment_method'] === 'Cash';
        $cashReceived = $isCashPayment ? (float) $validated['cash_received'] : $total;

        if ($isCashPayment && $cashReceived < $total) {
            return back()->withErrors([
                'cash_received' => 'Cash received is not enough.',
            ])->withInput();
        }

        $change = $isCashPayment ? $cashReceived - $total : 0;

        $sale = DB::transaction(function () use (
            $validated,
            $total,
            $change,
            $cashReceived,
            $store
        ) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'store_id' => $store->id,
                'total_amount' => $total,
                'cash_received' => $cashReceived,
                'change_amount' => $change,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'order_notes' => $validated['order_notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {

                $product = Product::findOrFail(
                    $item['product_id']
                );

                $subtotal =
                    $product->price * $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            app(InventoryConsumption::class)->consume($sale);

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale completed successfully.');
    }
}
