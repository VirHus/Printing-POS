<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_completed_sale_saves_customer_details_and_opens_receipt(): void
    {
        $category = Category::create(['name' => 'Photocopy', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Photocopy', 'price' => 2, 'unit' => 'page', 'is_active' => true]);

        $response = $this->actingAs($this->createCashier())->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_method' => 'Cash',
            'cash_received' => 100,
            'customer_name' => 'Alex',
            'order_notes' => '<script>alert(1)</script>',
        ]);

        $sale = Sale::firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'total_amount' => 6, 'change_amount' => 94, 'customer_name' => 'Alex', 'order_notes' => '<script>alert(1)</script>']);
        $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id, 'quantity' => 3, 'subtotal' => 6]);
        $this->get(route('sales.show', $sale))->assertSee('Alex')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertSee('New Sale')->assertSee('Print Receipt');
    }

    public function test_overlong_customer_details_do_not_create_sale(): void
    {
        $category = Category::create(['name' => 'Photocopy', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Photocopy', 'price' => 2, 'unit' => 'page', 'is_active' => true]);

        $this->actingAs($this->createCashier())->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'cash_received' => 2,
            'customer_name' => str_repeat('a', 256),
            'order_notes' => str_repeat('a', 1001),
        ])->assertSessionHasErrors(['customer_name', 'order_notes']);

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_digital_payment_saves_reference_without_cash_change(): void
    {
        $category = Category::create(['name' => 'Printing', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Color Print', 'price' => 15, 'unit' => 'page', 'is_active' => true]);

        $response = $this->actingAs($this->createCashier())->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'GCash',
            'payment_reference' => 'GC-123456',
        ]);

        $sale = Sale::firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'total_amount' => 30,
            'cash_received' => 30,
            'change_amount' => 0,
            'payment_method' => 'GCash',
            'payment_reference' => 'GC-123456',
        ]);
    }

    public function test_digital_payment_requires_a_reference(): void
    {
        $category = Category::create(['name' => 'Printing', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Color Print', 'price' => 15, 'unit' => 'page', 'is_active' => true]);

        $this->actingAs($this->createCashier())->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'GCash',
        ])->assertSessionHasErrors(['payment_reference']);

        $this->assertDatabaseCount('sales', 0);
    }

    private function createCashier(): User
    {
        $store = Store::create(['name' => 'Checkout Store']);

        return User::factory()->create(['role' => 'Cashier', 'store_id' => $store->id, 'is_active' => true]);
    }
}
