<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }
        $store = Store::firstOrCreate(['name' => 'Demo Store'], ['is_active' => false]);
        Expense::firstOrCreate(['store_id' => $store->id, 'description' => 'Demo electricity expense', 'expense_date' => now()->toDateString()], ['category' => 'Utilities', 'amount' => 100]);
    }
}
