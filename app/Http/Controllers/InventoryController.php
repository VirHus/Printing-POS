<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ServiceCostProfile;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['store_id' => 'nullable|integer|exists:stores,id']);
        $stores = Store::orderBy('name')->get();
        $storeId = $request->user()->role === 'Administrator' ? ($request->integer('store_id') ?: $request->user()->store_id ?: $stores->first()?->id) : $request->user()->store_id;
        $items = InventoryItem::where('store_id', $storeId ?? 0)->orderBy('name')->get();
        $profiles = ServiceCostProfile::where('store_id', $storeId ?? 0)->with('materials')->get()->keyBy('product_id');
        $products = $request->user()->role === 'Administrator' ? Product::orderBy('name')->get() : collect();
        $movements = StockMovement::whereHas('inventoryItem', fn ($query) => $query->where('store_id', $storeId ?? 0))->with(['inventoryItem', 'user'])->latest()->paginate(20);
        $lowStock = $items->filter(fn (InventoryItem $item): bool => $item->quantity <= $item->low_stock_threshold)->count();

        return view('products.inventory', compact('items', 'stores', 'storeId', 'products', 'profiles', 'movements', 'lowStock'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => 'required|integer|exists:stores,id', 'name' => ['required', 'string', 'max:255', Rule::unique('inventory_items')->where('store_id', $request->input('store_id'))],
            'unit' => 'required|string|max:40', 'low_stock_threshold' => 'required|numeric|min:0|max:100000000|decimal:0,3',
        ]);
        $stock = $this->stockInput($request, true);
        $data += ['quantity' => $stock['quantity'], 'unit_cost' => $stock['unit_cost'], 'purchase_unit' => $stock['purchase_unit'], 'units_per_pack' => $stock['units_per_pack']];
        DB::transaction(function () use ($data, $stock, $request): void {
            $item = InventoryItem::create($data);
            StockMovement::create(['inventory_item_id' => $item->id, 'user_id' => $request->user()->id, 'type' => 'Opening', 'quantity' => $item->quantity, 'unit_cost' => $item->unit_cost, 'total_cost' => $stock['total_cost'], 'notes' => 'Opening stock']);
        });

        return redirect()->route('inventory.index', ['store_id' => $data['store_id']])->with('success', 'Inventory item added.');
    }

    public function movement(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:Purchase,Waste,Adjustment in,Adjustment out,Refill usage',
            'notes' => 'required|string|max:500',
        ]);
        $stock = $this->stockInput($request, false, $data['type'] === 'Purchase');
        DB::transaction(function () use ($data, $stock, $inventoryItem, $request): void {
            $item = InventoryItem::lockForUpdate()->findOrFail($inventoryItem->id);
            $incoming = in_array($data['type'], ['Purchase', 'Adjustment in'], true);
            $quantity = $stock['quantity'];
            if (! $incoming && $quantity > (float) $item->quantity) {
                throw ValidationException::withMessages(['quantity' => 'The deduction exceeds available stock.']);
            }
            $unitCost = $data['type'] === 'Purchase' ? $stock['unit_cost'] : (float) $item->unit_cost;
            if ($data['type'] === 'Purchase') {
                $item->unit_cost = round(($item->quantity * $item->unit_cost + $stock['total_cost']) / ($item->quantity + $quantity), 4);
                if ($stock['purchase_unit'] !== null) {
                    $item->purchase_unit = $stock['purchase_unit'];
                    $item->units_per_pack = $stock['units_per_pack'];
                }
            }
            $item->quantity = round($item->quantity + ($incoming ? $quantity : -$quantity), 3);
            $item->save();
            StockMovement::create(['inventory_item_id' => $item->id, 'user_id' => $request->user()->id, 'type' => $data['type'], 'quantity' => $incoming ? $quantity : -$quantity, 'unit_cost' => $unitCost, 'total_cost' => $data['type'] === 'Purchase' ? $stock['total_cost'] : round($quantity * $unitCost, 2), 'notes' => $data['notes']]);
        });

        return redirect()->route('inventory.index', ['store_id' => $inventoryItem->store_id])->with('success', 'Stock movement recorded.');
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_items')->where('store_id', $inventoryItem->store_id)->ignore($inventoryItem)],
            'low_stock_threshold' => 'required|numeric|min:0|max:100000000|decimal:0,3',
        ]);
        $inventoryItem->update($data);

        return redirect()->route('inventory.index', ['store_id' => $inventoryItem->store_id])->with('success', 'Item details updated.');
    }

    /** @return array{quantity:float,unit_cost:float,total_cost:float,purchase_unit:?string,units_per_pack:?float} */
    private function stockInput(Request $request, bool $opening, bool $purchase = true): array
    {
        $request->validate(['entry_mode' => 'nullable|in:unit,pack']);
        if ($request->input('entry_mode', 'unit') === 'pack') {
            if (! $opening && ! $purchase) {
                throw ValidationException::withMessages(['entry_mode' => 'Use stock units for waste, refills, and adjustments.']);
            }
            $data = $request->validate([
                'purchase_unit' => 'required|string|max:40',
                'units_per_pack' => 'required|numeric|min:0.001|max:1000000|decimal:0,3',
                'pack_count' => 'required|integer|min:'.($opening ? '0' : '1').'|max:1000000',
                'pack_price' => 'required|numeric|min:0|max:100000000|decimal:0,2',
                'loose_quantity' => $opening ? 'nullable|numeric|min:0|max:100000000|decimal:0,3' : 'prohibited',
            ]);
            $quantity = round($data['pack_count'] * $data['units_per_pack'] + ($data['loose_quantity'] ?? 0), 3);
            $cost = round($data['pack_price'] / $data['units_per_pack'], 4);
            $total = round($data['pack_count'] * $data['pack_price'] + ($data['loose_quantity'] ?? 0) * $cost, 2);
            $purchaseUnit = $data['purchase_unit'];
            $packSize = (float) $data['units_per_pack'];
        } else {
            $data = $request->validate([
                'quantity' => 'required|numeric|min:'.($opening ? '0' : '0.001').'|max:100000000|decimal:0,3',
                'unit_cost' => ($purchase ? 'required' : 'nullable').'|numeric|min:0|max:1000000|decimal:0,4',
            ]);
            $quantity = (float) $data['quantity'];
            $cost = (float) ($data['unit_cost'] ?? 0);
            $total = round($quantity * $cost, 2);
            $purchaseUnit = null;
            $packSize = null;
        }
        if ($quantity > 100000000 || $cost > 1000000 || $total > 999999999999.99) {
            throw ValidationException::withMessages(['quantity' => 'The converted stock quantity or value is too large.']);
        }

        return ['quantity' => $quantity, 'unit_cost' => $cost, 'total_cost' => $total, 'purchase_unit' => $purchaseUnit, 'units_per_pack' => $packSize];
    }

    public function materials(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'estimated_unit_cost' => 'required|numeric|min:0|max:1000000|decimal:0,4',
            'materials' => 'nullable|array|max:10',
            'materials.*' => 'array:inventory_item_id,quantity',
            'materials.*.inventory_item_id' => ['nullable', 'integer', Rule::exists('inventory_items', 'id')->where('store_id', $request->input('store_id'))],
            'materials.*.quantity' => 'required_with:materials.*.inventory_item_id|nullable|numeric|min:0.001|max:1000000|decimal:0,3',
        ]);
        $materials = collect($data['materials'] ?? [])->filter(fn (array $row): bool => ! empty($row['inventory_item_id']))->values();
        if ($materials->pluck('inventory_item_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['materials' => 'Use each inventory item only once in a service recipe.']);
        }
        DB::transaction(function () use ($data, $materials, $product): void {
            $profile = ServiceCostProfile::firstOrCreate(['store_id' => $data['store_id'], 'product_id' => $product->id], ['estimated_unit_cost' => 0]);
            $profile = ServiceCostProfile::lockForUpdate()->findOrFail($profile->id);
            $profile->update(['estimated_unit_cost' => $data['estimated_unit_cost']]);
            $profile->materials()->delete();
            foreach ($materials as $material) {
                $profile->materials()->create($material);
            }
        });

        return redirect()->route('inventory.index', ['store_id' => $data['store_id']])->with('success', 'Service cost and material usage saved. Only future sales use this setup.');
    }
}
