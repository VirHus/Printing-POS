<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InventoryItem> */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return ['store_id' => fn (): int => Store::create(['name' => fake()->unique()->company()])->id, 'name' => fake()->unique()->words(3, true), 'unit' => 'sheet', 'quantity' => 100, 'unit_cost' => 0.5, 'low_stock_threshold' => 20];
    }
}
