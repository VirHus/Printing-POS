<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('products.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
            'description' => 'nullable|string|max:2000',
        ]);
        Category::create($validated + ['is_active' => true]);

        return redirect()->route('categories.index')->with('success', 'Category added. It is now available when creating services.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category)],
            'description' => 'nullable|string|max:2000',
            'is_active' => 'required|boolean',
        ]);
        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated. Historical transactions are unchanged.');
    }
}
