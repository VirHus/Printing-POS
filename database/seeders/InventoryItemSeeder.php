<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Store;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Store::all() as $store) {
            InventoryItem::firstOrCreate(['store_id' => $store->id, 'name' => 'A4 bond paper'], ['unit' => 'sheet', 'quantity' => 0, 'unit_cost' => 0, 'low_stock_threshold' => 50]);
        }
    }
}
