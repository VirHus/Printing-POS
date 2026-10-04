<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::withCount(['users', 'sales'])->orderBy('name')->get();

        return view('stores.index', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        Store::create($request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Store created. Assign cashiers through Users.');
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        abort_if(! $request->user()->isSuperadmin() && ($request->hasFile('image') || $request->boolean('remove_image')), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096|dimensions:max_width=4000,max_height=4000',
            'remove_image' => 'nullable|boolean',
        ]);
        unset($validated['image'], $validated['remove_image']);
        $oldPath = null;
        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $oldPath = $store->image_path;
            $validated['image_path'] = $request->hasFile('image') ? $request->file('image')->store('stores', 'public') : null;
            abort_if($validated['image_path'] === false, 500, 'The store image could not be stored.');
        }
        $store->update($validated);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'Store updated successfully.');
    }
}
