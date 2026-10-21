<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\ServiceCostProfile;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventoryProfitTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_must_sign_in(): void
    {
        $this->get(route('inventory.index'))->assertRedirect(route('login'));
        $this->get(route('profit.index'))->assertRedirect(route('login'));
        $this->post(route('inventory.store'), [])->assertRedirect(route('login'));
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_roles_cannot_manage_stock_or_see_profit(string $role): void
    {
        $user = $this->user($role);
        $item = InventoryItem::factory()->create(['store_id' => $user->store_id]);
        $product = $this->product();
        $this->actingAs($user)->post(route('inventory.store'), [])->assertForbidden();
        $this->post(route('inventory.movements.store', $item), [])->assertForbidden();
        $this->put(route('inventory.update', $item), [])->assertForbidden();
        $this->put(route('inventory.materials.update', $product), [])->assertForbidden();
        $this->post(route('expenses.store'), [])->assertForbidden();
        $this->get(route('profit.index'))->assertForbidden();
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('expenses', 0);
    }

    public static function nonAdminRoles(): array
    {
        return [['Cashier'], ['Superadmin']];
    }

    public function test_cashier_sees_only_assigned_store_stock_and_low_stock_alerts(): void
    {
        $user = $this->user('Cashier');
        $own = InventoryItem::factory()->create(['store_id' => $user->store_id, 'name' => 'Own paper', 'quantity' => 5, 'low_stock_threshold' => 10]);
        $other = InventoryItem::factory()->create(['name' => 'Secret other-store stock']);
        $this->actingAs($user)->get(route('inventory.index', ['store_id' => $other->store_id]))->assertSee('Own paper')->assertSee('Low stock')->assertDontSee('Secret other-store stock')->assertDontSee('Add inventory item');
    }

    public function test_admin_can_create_stock_record_and_update_threshold_without_overwriting_quantity(): void
    {
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->post(route('inventory.store'), ['store_id' => $admin->store_id, 'name' => 'Paper', 'unit' => 'sheet', 'quantity' => 100, 'unit_cost' => 0.5, 'low_stock_threshold' => 10])->assertSessionHasNoErrors();
        $item = InventoryItem::firstOrFail();
        $this->assertDatabaseHas('stock_movements', ['inventory_item_id' => $item->id, 'type' => 'Opening', 'quantity' => 100, 'total_cost' => 50]);
        $this->put(route('inventory.update', $item), ['name' => 'A4 Paper', 'low_stock_threshold' => 20, 'quantity' => 999])->assertSessionHasNoErrors();
        $this->assertSame('100.000', $item->fresh()->quantity);
        $this->assertSame('20.000', $item->fresh()->low_stock_threshold);
        $this->get(route('inventory.index', ['store_id' => $admin->store_id]))->assertSee('A4 Paper')->assertSee('Materials per service unit');
    }

    public function test_purchase_uses_weighted_average_cost_and_does_not_create_an_expense(): void
    {
        $admin = $this->user('Administrator');
        $item = InventoryItem::factory()->create(['store_id' => $admin->store_id, 'quantity' => 100, 'unit_cost' => 0.5]);
        $this->actingAs($admin)->post(route('inventory.movements.store', $item), ['type' => 'Purchase', 'quantity' => 100, 'unit_cost' => 1.5, 'notes' => 'Supplier invoice'])->assertSessionHasNoErrors();
        $this->assertSame('200.000', $item->fresh()->quantity);
        $this->assertSame('1.0000', $item->fresh()->unit_cost);
        $this->assertDatabaseHas('stock_movements', ['type' => 'Purchase', 'quantity' => 100, 'total_cost' => 150]);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_waste_and_adjustments_require_reason_and_cannot_exceed_stock(): void
    {
        $admin = $this->user('Administrator');
        $item = InventoryItem::factory()->create(['store_id' => $admin->store_id, 'quantity' => 10, 'unit_cost' => 2]);
        $this->actingAs($admin)->post(route('inventory.movements.store', $item), ['type' => 'Waste', 'quantity' => 11, 'notes' => 'Spoiled'])->assertSessionHasErrors('quantity');
        $this->assertSame('10.000', $item->fresh()->quantity);
        $this->post(route('inventory.movements.store', $item), ['type' => 'Waste', 'quantity' => 2])->assertSessionHasErrors('notes');
        $this->post(route('inventory.movements.store', $item), ['type' => 'Waste', 'quantity' => 2, 'notes' => 'Spoiled'])->assertSessionHasNoErrors();
        $this->assertSame('8.000', $item->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', ['type' => 'Waste', 'quantity' => -2, 'total_cost' => 4]);
        $this->post(route('inventory.movements.store', $item), ['type' => 'Adjustment in', 'quantity' => 1, 'notes' => 'Physical count'])->assertSessionHasNoErrors();
        $this->assertSame('9.000', $item->fresh()->quantity);
    }

    public function test_recipe_rejects_cross_store_materials_and_duplicate_materials(): void
    {
        $admin = $this->user('Administrator');
        $own = InventoryItem::factory()->create(['store_id' => $admin->store_id]);
        $other = InventoryItem::factory()->create();
        $product = $this->product();
        $data = ['store_id' => $admin->store_id, 'estimated_unit_cost' => 0.2, 'materials' => [['inventory_item_id' => $other->id, 'quantity' => 1]]];
        $this->actingAs($admin)->put(route('inventory.materials.update', $product), $data)->assertSessionHasErrors('materials.0.inventory_item_id');
        $data['materials'] = [['inventory_item_id' => $own->id, 'quantity' => 1], ['inventory_item_id' => $own->id, 'quantity' => 2]];
        $this->put(route('inventory.materials.update', $product), $data)->assertSessionHasErrors('materials');
        $this->assertDatabaseCount('service_cost_profiles', 0);
        $data['materials'] = [['inventory_item_id' => $own->id, 'quantity' => 1], ['inventory_item_id' => '', 'quantity' => '']];
        $this->put(route('inventory.materials.update', $product), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_materials', ['inventory_item_id' => $own->id, 'quantity' => 1]);
    }

    public function test_checkout_deducts_fractional_materials_and_snapshots_costs(): void
    {
        $cashier = $this->user('Cashier');
        $product = $this->product();
        $paper = InventoryItem::factory()->create(['store_id' => $cashier->store_id, 'quantity' => 100, 'unit_cost' => 0.5]);
        $ink = InventoryItem::factory()->create(['store_id' => $cashier->store_id, 'quantity' => 10, 'unit_cost' => 2]);
        $profile = ServiceCostProfile::create(['store_id' => $cashier->store_id, 'product_id' => $product->id, 'estimated_unit_cost' => 0.1]);
        $profile->materials()->createMany([['inventory_item_id' => $paper->id, 'quantity' => 1], ['inventory_item_id' => $ink->id, 'quantity' => 0.05]]);
        $this->actingAs($cashier)->post(route('pos.store'), $this->checkout($product, 3))->assertSessionHasNoErrors();
        $sale = Sale::firstOrFail();
        $this->assertSame('97.000', $paper->fresh()->quantity);
        $this->assertSame('9.850', $ink->fresh()->quantity);
        $this->assertSame('2.10', $sale->cost_total);
        $this->assertTrue($sale->cost_complete);
        $this->assertDatabaseHas('stock_movements', ['sale_id' => $sale->id, 'inventory_item_id' => $ink->id, 'quantity' => -0.15]);
        $paper->update(['unit_cost' => 3]);
        $profile->update(['estimated_unit_cost' => 10]);
        $this->assertSame('2.10', $sale->fresh()->cost_total);
    }

    public function test_insufficient_stock_rolls_back_sale_items_and_all_stock_changes(): void
    {
        $cashier = $this->user('Cashier');
        $product = $this->product();
        $paper = InventoryItem::factory()->create(['store_id' => $cashier->store_id, 'quantity' => 10]);
        $ink = InventoryItem::factory()->create(['store_id' => $cashier->store_id, 'quantity' => 0]);
        $profile = ServiceCostProfile::create(['store_id' => $cashier->store_id, 'product_id' => $product->id]);
        $profile->materials()->createMany([['inventory_item_id' => $paper->id, 'quantity' => 1], ['inventory_item_id' => $ink->id, 'quantity' => 0.1]]);
        $this->actingAs($cashier)->post(route('pos.store'), $this->checkout($product, 2))->assertSessionHasErrors('inventory');
        $this->assertSame('10.000', $paper->fresh()->quantity);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_duplicate_cart_lines_cannot_bypass_stock_checks(): void
    {
        $cashier = $this->user('Cashier');
        $product = $this->product();
        $paper = InventoryItem::factory()->create(['store_id' => $cashier->store_id, 'quantity' => 3]);
        $profile = ServiceCostProfile::create(['store_id' => $cashier->store_id, 'product_id' => $product->id]);
        $profile->materials()->create(['inventory_item_id' => $paper->id, 'quantity' => 1]);
        $payload = $this->checkout($product, 2);
        $payload['items'][] = ['product_id' => $product->id, 'quantity' => 2];
        $this->actingAs($cashier)->post(route('pos.store'), $payload)->assertSessionHasErrors('inventory');
        $this->assertSame('3.000', $paper->fresh()->quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_unconfigured_sale_is_allowed_but_profit_is_flagged_incomplete(): void
    {
        $admin = $this->user('Administrator');
        $product = $this->product();
        $this->actingAs($admin)->post(route('pos.store'), $this->checkout($product, 1))->assertSessionHasNoErrors();
        $this->assertFalse(Sale::firstOrFail()->cost_complete);
        $this->get(route('profit.index'))->assertSee('Incomplete cost data')->assertSee('Provisional net profit');
    }

    public function test_profit_deducts_saved_sales_costs_expenses_and_losses_and_filters_by_store(): void
    {
        $admin = $this->user('Administrator');
        Sale::create(['user_id' => $admin->id, 'store_id' => $admin->store_id, 'total_amount' => 100, 'cash_received' => 100, 'change_amount' => 0, 'payment_method' => 'Cash', 'cost_total' => 30, 'cost_complete' => true]);
        Expense::factory()->create(['store_id' => $admin->store_id, 'amount' => 20]);
        $item = InventoryItem::factory()->create(['store_id' => $admin->store_id]);
        StockMovement::create(['inventory_item_id' => $item->id, 'type' => 'Waste', 'quantity' => -10, 'unit_cost' => 0.5, 'total_cost' => 5]);
        StockMovement::create(['inventory_item_id' => $item->id, 'type' => 'Purchase', 'quantity' => 100, 'unit_cost' => 0.5, 'total_cost' => 50]);
        Expense::factory()->create(['amount' => 999]);
        $this->actingAs($admin)->get(route('profit.index', ['store_id' => $admin->store_id]))->assertViewHas('grossProfit', 70.0)->assertViewHas('netProfit', 45.0)->assertViewHas('uncosted', 0)->assertSee('Estimated operating profit')->assertDontSee('Incomplete cost data');
    }

    public function test_admin_can_record_expense_and_invalid_date_range_is_rejected(): void
    {
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->post(route('expenses.store'), ['store_id' => $admin->store_id, 'category' => 'Utilities', 'description' => 'Electricity bill', 'amount' => 50, 'expense_date' => now()->toDateString()])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('expenses', ['user_id' => $admin->id, 'amount' => 50]);
        $this->get(route('profit.index', ['from' => '2026-10-05', 'to' => '2026-10-01']))->assertSessionHasErrors('to');
    }

    private function user(string $role): User
    {
        $store = Store::create(['name' => $role.' Store', 'is_active' => true]);

        return User::factory()->create(['role' => $role, 'is_active' => true, 'store_id' => $store->id]);
    }

    public function test_paper_can_be_entered_by_ream_price_with_loose_sheets(): void
    {
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->post(route('inventory.store'), [
            'store_id' => $admin->store_id, 'name' => 'A4 Paper', 'unit' => 'sheet', 'low_stock_threshold' => 100,
            'entry_mode' => 'pack', 'purchase_unit' => 'ream', 'units_per_pack' => 500, 'pack_count' => 3, 'pack_price' => 178, 'loose_quantity' => 50,
            'quantity' => 1, 'unit_cost' => 999,
        ])->assertSessionHasNoErrors();

        $item = InventoryItem::firstOrFail();
        $this->assertSame('1550.000', $item->quantity);
        $this->assertSame('0.3560', $item->unit_cost);
        $this->assertSame('ream', $item->purchase_unit);
        $this->assertSame('500.000', $item->units_per_pack);
        $this->assertDatabaseHas('stock_movements', ['type' => 'Opening', 'total_cost' => 551.8]);
        $this->get(route('inventory.index'))->assertSee('Price per pack')->assertSee('Paper — ream / 500 sheets')->assertSee('Bottled ink');
    }

    public function test_pack_purchase_converts_stock_and_saves_weighted_average(): void
    {
        $admin = $this->user('Administrator');
        $item = InventoryItem::factory()->create(['store_id' => $admin->store_id, 'quantity' => 100, 'unit_cost' => 0.5]);
        $this->actingAs($admin)->post(route('inventory.movements.store', $item), [
            'type' => 'Purchase', 'entry_mode' => 'pack', 'purchase_unit' => 'ream', 'units_per_pack' => 500, 'pack_count' => 3, 'pack_price' => 178, 'notes' => 'Supplier invoice',
        ])->assertSessionHasNoErrors();

        $this->assertSame('1600.000', $item->fresh()->quantity);
        $this->assertSame('0.3650', $item->fresh()->unit_cost);
        $this->assertSame('ream', $item->fresh()->purchase_unit);
        $this->assertDatabaseHas('stock_movements', ['type' => 'Purchase', 'quantity' => 1500, 'total_cost' => 534]);
    }

    public function test_bottled_ink_can_be_counted_in_ml_and_refills_are_not_double_expensed(): void
    {
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->post(route('inventory.store'), [
            'store_id' => $admin->store_id, 'name' => 'Black ink', 'unit' => 'ml', 'low_stock_threshold' => 50,
            'entry_mode' => 'pack', 'purchase_unit' => 'bottle', 'units_per_pack' => 100, 'pack_count' => 3, 'pack_price' => 200,
        ])->assertSessionHasNoErrors();
        $item = InventoryItem::firstOrFail();
        $this->assertSame('300.000', $item->quantity);
        $this->assertSame('2.0000', $item->unit_cost);
        $this->post(route('inventory.movements.store', $item), ['type' => 'Refill usage', 'quantity' => 100, 'notes' => 'Printer refill; cost estimated per page'])->assertSessionHasNoErrors();
        $this->assertSame('200.000', $item->fresh()->quantity);
        $this->get(route('profit.index'))->assertViewHas('stockLosses', 0.0);
    }

    #[DataProvider('invalidPackInputs')]
    public function test_invalid_pack_entry_does_not_create_stock(array $overrides, string $field): void
    {
        $admin = $this->user('Administrator');
        $payload = array_replace(['store_id' => $admin->store_id, 'name' => 'Paper', 'unit' => 'sheet', 'low_stock_threshold' => 10, 'entry_mode' => 'pack', 'purchase_unit' => 'ream', 'units_per_pack' => 500, 'pack_count' => 1, 'pack_price' => 178], $overrides);
        $this->actingAs($admin)->post(route('inventory.store'), $payload)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('inventory_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public static function invalidPackInputs(): array
    {
        return [
            'zero size' => [['units_per_pack' => 0], 'units_per_pack'],
            'negative price' => [['pack_price' => -1], 'pack_price'],
            'fractional pack count' => [['pack_count' => 1.5], 'pack_count'],
            'missing pack unit' => [['purchase_unit' => ''], 'purchase_unit'],
            'converted stock overflow' => [['pack_count' => 1000000, 'units_per_pack' => 1000000], 'quantity'],
        ];
    }

    public function test_pack_entry_cannot_be_used_for_waste_and_purchase_requires_nonzero_packs(): void
    {
        $admin = $this->user('Administrator');
        $item = InventoryItem::factory()->create(['store_id' => $admin->store_id]);
        $payload = ['entry_mode' => 'pack', 'purchase_unit' => 'ream', 'units_per_pack' => 500, 'pack_count' => 0, 'pack_price' => 178, 'notes' => 'Test'];
        $this->actingAs($admin)->post(route('inventory.movements.store', $item), $payload + ['type' => 'Purchase'])->assertSessionHasErrors('pack_count');
        $this->post(route('inventory.movements.store', $item), $payload + ['type' => 'Waste'])->assertSessionHasErrors('entry_mode');
        $this->assertSame('100.000', $item->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function product(): Product
    {
        return Product::create(['name' => 'Photocopy', 'category_id' => Category::create(['name' => 'Printing', 'is_active' => true])->id, 'price' => 2, 'unit' => 'page', 'is_active' => true]);
    }

    private function checkout(Product $product, int $quantity): array
    {
        return ['items' => [['product_id' => $product->id, 'quantity' => $quantity]], 'payment_method' => 'Cash', 'cash_received' => 100];
    }
}
