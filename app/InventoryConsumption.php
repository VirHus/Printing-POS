<?php

namespace App;

use App\Models\InventoryItem;
use App\Models\Sale;
use App\Models\ServiceCostProfile;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class InventoryConsumption
{
    public function consume(Sale $sale): void
    {
        $sale->load('items');
        $profiles = ServiceCostProfile::where('store_id', $sale->store_id)->whereIn('product_id', $sale->items->pluck('product_id'))->orderBy('id')->lockForUpdate()->with('materials')->get()->keyBy('product_id');
        $needed = [];
        $cost = 0;
        $complete = true;
        foreach ($sale->items as $line) {
            $profile = $profiles->get($line->product_id);
            if (! $profile) {
                $complete = false;

                continue;
            }
            $cost += (float) $profile->estimated_unit_cost * $line->quantity;
            foreach ($profile->materials as $material) {
                $needed[$material->inventory_item_id] = ($needed[$material->inventory_item_id] ?? 0) + (int) round($material->quantity * 1000) * $line->quantity;
            }
        }
        $stock = InventoryItem::where('store_id', $sale->store_id)->whereIn('id', array_keys($needed))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($needed as $id => $milliquantity) {
            $item = $stock->get($id);
            if (! $item || (int) round($item->quantity * 1000) < $milliquantity) {
                throw ValidationException::withMessages(['inventory' => 'Not enough stock for '.($item?->name ?? 'a configured material').'. Ask an administrator to restock or correct the service materials.']);
            }
            $quantity = $milliquantity / 1000;
            $materialCost = round($quantity * $item->unit_cost, 2);
            $item->quantity = ((int) round($item->quantity * 1000) - $milliquantity) / 1000;
            $item->save();
            StockMovement::create(['inventory_item_id' => $id, 'user_id' => $sale->user_id, 'sale_id' => $sale->id, 'type' => 'Sale usage', 'quantity' => -$quantity, 'unit_cost' => $item->unit_cost, 'total_cost' => $materialCost, 'notes' => 'Automatic POS deduction']);
            $cost += $materialCost;
        }
        $sale->update(['cost_total' => round($cost, 2), 'cost_complete' => $complete]);
    }
}
