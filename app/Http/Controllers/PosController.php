<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with([
                'products' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get();

        $todaySales = Sale::whereDate('created_at', today())
            ->sum('total_amount');

        return view('pos.index', compact(
            'categories',
            'todaySales'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if (!$product->is_active) {
                return back()->withErrors([
                    'items' => "The service {$product->name} is inactive."
                ]);
            }

            $total += $product->price * $item['quantity'];
        }

        if ($validated['cash_received'] < $total) {
            return back()->withErrors([
                'cash_received' => 'Cash received is not enough.'
            ])->withInput();
        }

        $change = $validated['cash_received'] - $total;

        DB::transaction(function () use (
            $validated,
            $total,
            $change
        ) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'cash_received' => $validated['cash_received'],
                'change_amount' => $change,
                'payment_method' => 'Cash',
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
        });

        return redirect()
            ->route('pos.index')
            ->with('success', 'Sale completed successfully.');
    }
}