<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Product::distinct()->orderBy('unit')->pluck('unit');

        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Service added successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orWhere('id', $product->category_id)->orderBy('name')->get();
        $units = Product::distinct()->orderBy('unit')->pluck('unit');

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function toggle(Product $product)
    {
        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Service status updated.');
    }
}
