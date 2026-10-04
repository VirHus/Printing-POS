<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreIsolationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cashier_cannot_read_other_store_even_with_filter_or_receipt_id(): void
    {
        $store = Store::create(['name' => 'Own Store']);
        $otherStore = Store::create(['name' => 'Other Store']);
        $cashier = User::factory()->create(['role' => 'Cashier', 'store_id' => $store->id, 'is_active' => true]);
        $ownSale = $this->createSale($cashier, $store, 10);
        $otherSale = $this->createSale($cashier, $otherStore, 900);

        $this->actingAs($cashier)->get(route('sales.index', ['store_id' => $otherStore->id]))
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 1 && $sales->first()->id === $ownSale->id);
        $this->get(route('sales.show', $otherSale))->assertNotFound();
        $this->get(route('reports.index', ['store_id' => $otherStore->id]))->assertViewHas('totalRevenue', 10);
        $this->get(route('dashboard'))->assertViewHas('todaySales', 10)->assertViewHas('todayTransactions', 1);
        $this->get(route('stores.index'))->assertForbidden();
    }

    public function test_administrator_can_filter_stores_and_manage_locations(): void
    {
        $store = Store::create(['name' => 'North Store']);
        $other = Store::create(['name' => 'South Store']);
        $admin = User::factory()->create(['role' => 'Administrator', 'store_id' => $store->id, 'is_active' => true]);
        $this->createSale($admin, $store, 10);
        $sale = $this->createSale($admin, $other, 25);

        $this->actingAs($admin)->get(route('sales.index', ['store_id' => $other->id]))
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 1 && $sales->first()->id === $sale->id);
        $this->get(route('reports.index', ['store_id' => $other->id]))->assertViewHas('totalRevenue', 25);
        $this->get(route('sales.show', $sale))->assertSee('South Store');
        $this->get(route('stores.index'))->assertSee('North Store')->assertSee('South Store');
        $this->get(route('users.edit', $admin))->assertSee('Assigned store')->assertSee('South Store');
        $this->post(route('stores.store'), ['name' => 'New Branch', 'address' => 'Main Street'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stores', ['name' => 'New Branch', 'address' => 'Main Street']);
    }

    public function test_sale_uses_assigned_store_and_does_not_move_when_cashier_moves(): void
    {
        $store = Store::create(['name' => 'First Store']);
        $other = Store::create(['name' => 'Second Store']);
        $cashier = User::factory()->create(['role' => 'Cashier', 'store_id' => $store->id, 'is_active' => true]);
        $category = Category::create(['name' => 'Print']);
        $product = Product::create(['name' => 'Page', 'category_id' => $category->id, 'price' => 2, 'unit' => 'page', 'is_active' => true]);

        $this->actingAs($cashier)->post(route('pos.store'), [
            'store_id' => $other->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash', 'cash_received' => 2,
        ])->assertSessionHasNoErrors();

        $sale = Sale::firstOrFail();
        $cashier->update(['store_id' => $other->id]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'store_id' => $store->id]);
    }

    public function test_unassigned_cashier_has_no_access_and_cannot_record_sale(): void
    {
        $cashier = User::factory()->create(['role' => 'Cashier', 'store_id' => null, 'is_active' => true]);

        $this->actingAs($cashier)->get(route('sales.index'))->assertViewHas('sales', fn ($sales) => $sales->total() === 0);
        $this->post(route('pos.store'), [])->assertSessionHasErrors('store');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_inactive_store_cannot_record_sale_but_keeps_receipts(): void
    {
        $store = Store::create(['name' => 'Closed Store', 'is_active' => false]);
        $cashier = User::factory()->create(['role' => 'Cashier', 'store_id' => $store->id, 'is_active' => true]);
        $sale = $this->createSale($cashier, $store, 10);

        $this->actingAs($cashier)->post(route('pos.store'), [])->assertSessionHasErrors('store');
        $this->get(route('sales.show', $sale))->assertSee('Closed Store');
        $this->assertDatabaseCount('sales', 1);
    }

    public function test_service_report_excludes_other_store_items(): void
    {
        $store = Store::create(['name' => 'Local Store']);
        $other = Store::create(['name' => 'Remote Store']);
        $cashier = User::factory()->create(['role' => 'Cashier', 'store_id' => $store->id, 'is_active' => true]);
        $category = Category::create(['name' => 'Print']);
        $product = Product::create(['name' => 'Print Page', 'category_id' => $category->id, 'price' => 2, 'unit' => 'page']);
        $sale = $this->createSale($cashier, $store, 10);
        $otherSale = $this->createSale($cashier, $other, 100);
        $sale->items()->create(['product_id' => $product->id, 'quantity' => 5, 'price' => 2, 'subtotal' => 10]);
        $otherSale->items()->create(['product_id' => $product->id, 'quantity' => 50, 'price' => 2, 'subtotal' => 100]);

        $this->actingAs($cashier)->get(route('reports.index'))
            ->assertViewHas('serviceSales', fn ($sales) => $sales['Print Page']['quantity'] === 5 && (float) $sales['Print Page']['revenue'] === 10.0);
    }

    private function createSale(User $user, Store $store, int $amount): Sale
    {
        return Sale::create(['user_id' => $user->id, 'store_id' => $store->id, 'total_amount' => $amount, 'cash_received' => $amount, 'change_amount' => 0, 'payment_method' => 'Cash']);
    }
}
