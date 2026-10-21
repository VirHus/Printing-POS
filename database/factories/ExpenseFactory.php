<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return ['store_id' => fn (): int => Store::create(['name' => fake()->unique()->company()])->id, 'category' => 'Utilities', 'description' => 'Electricity', 'amount' => 100, 'expense_date' => now()->toDateString()];
    }
}
