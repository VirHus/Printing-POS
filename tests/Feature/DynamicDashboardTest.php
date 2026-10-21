<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DynamicDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_period_controls_use_recorded_sales_and_fill_missing_buckets(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        $store = Store::create(['name' => 'Branch']);
        $admin = User::factory()->create(['is_active' => true, 'role' => 'Administrator', 'store_id' => $store->id]);
        foreach (['2026-10-15 09:00:00' => 20, '2026-10-14 10:00:00' => 10, '2026-10-01 10:00:00' => 50] as $date => $amount) {
            $sale = Sale::create(['user_id' => $admin->id, 'store_id' => $store->id, 'total_amount' => $amount, 'cash_received' => $amount, 'change_amount' => 0]);
            $sale->forceFill(['created_at' => $date])->save();
        }
        $this->actingAs($admin)->get(route('dashboard', ['period' => 'day']))->assertOk()
            ->assertViewHas('todaySales', 20)->assertViewHas('salesGrowth', 100)
            ->assertViewHas('chart', fn ($chart) => count($chart) === 24 && $chart[9]['revenue'] === 20.0 && $chart[8]['revenue'] === 0.0);
        $this->get(route('dashboard', ['period' => 'week']))->assertOk()->assertViewHas('todaySales', 30)
            ->assertViewHas('todayTransactions', 2)->assertViewHas('recentSales', fn ($sales) => $sales->count() === 2);
        $this->get(route('dashboard', ['period' => 'month']))->assertOk()->assertViewHas('todaySales', 80)
            ->assertViewHas('reportFilters', ['from' => '2026-10-01', 'to' => '2026-10-15'])
            ->assertViewHas('chart', fn ($chart) => count($chart) === 15 && $chart[0]['revenue'] === 50.0);
    }

    public function test_store_filter_does_not_expose_another_branch_to_cashier(): void
    {
        $store = Store::create(['name' => 'Own']);
        $other = Store::create(['name' => 'Other']);
        $cashier = User::factory()->create(['is_active' => true, 'role' => 'Cashier', 'store_id' => $store->id]);
        Sale::create(['user_id' => $cashier->id, 'store_id' => $other->id, 'total_amount' => 900, 'cash_received' => 900, 'change_amount' => 0]);
        $this->actingAs($cashier)->get(route('dashboard', ['period' => 'month', 'store_id' => $other->id]))->assertOk()
            ->assertViewHas('todaySales', 0)->assertViewHas('salesGrowth', null)->assertSee('No recorded sales for this period.');
        $admin = User::factory()->create(['is_active' => true, 'role' => 'Administrator']);
        $this->actingAs($admin)->get(route('dashboard', ['store_id' => $other->id]))->assertViewHas('todaySales', 900);
        $this->get(route('dashboard', ['period' => 'invalid']))->assertSessionHasErrors('period');
    }
}
