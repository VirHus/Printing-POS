<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_category_management_requires_an_operational_administrator(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $superadmin = User::factory()->create(['role' => 'Superadmin', 'is_active' => true]);
        $this->actingAs($superadmin)->get(route('categories.index'))->assertForbidden();
    }

    public function test_admin_can_manage_categories_without_deleting_services(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'role' => 'Administrator']);
        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Lamination'])->assertRedirect(route('categories.index'));
        $category = Category::where('name', 'Lamination')->firstOrFail();
        $product = Product::create(['category_id' => $category->id, 'name' => 'Film', 'unit' => 'document', 'price' => 20, 'is_active' => true]);
        $this->get(route('categories.index'))->assertOk()->assertSee('Lamination');
        $this->put(route('categories.update', $category), ['name' => 'Finishing', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Finishing', 'is_active' => false]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->get(route('pos.index'))->assertViewHas('categories', fn ($categories) => $categories->isEmpty());
        $this->get(route('dashboard'))->assertViewHas('activeServices', 0);
        $this->get(route('products.edit', $product))->assertOk()->assertSee('Finishing')->assertSee('document');
    }

    public function test_category_validation_and_custom_service_units(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'role' => 'Administrator']);
        $category = Category::create(['name' => 'Design']);
        $this->actingAs($admin)->post(route('categories.store'), [])->assertSessionHasErrors('name');
        $this->post(route('categories.store'), ['name' => 'Design'])->assertSessionHasErrors('name');
        $this->post(route('products.store'), ['name' => 'Design session', 'category_id' => $category->id, 'price' => 100, 'unit' => 'hour'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['name' => 'Design session', 'unit' => 'hour']);
        $this->get(route('products.create'))->assertOk()->assertSee('value="hour"', false);
    }

    public function test_cashier_cannot_manage_categories_or_purchase_disabled_category(): void
    {
        $store = Store::create(['name' => 'Branch']);
        $cashier = User::factory()->create(['is_active' => true, 'role' => 'Cashier', 'store_id' => $store->id]);
        $category = Category::create(['name' => 'Disabled', 'is_active' => false]);
        $product = Product::create(['name' => 'Hidden service', 'category_id' => $category->id, 'price' => 2, 'unit' => 'page', 'is_active' => true]);
        $this->actingAs($cashier)->get(route('categories.index'))->assertForbidden();
        $this->post(route('categories.store'), ['name' => 'Unauthorized'])->assertForbidden();
        $this->put(route('categories.update', $category), ['name' => 'Unauthorized', 'is_active' => 1])->assertForbidden();
        $this->post(route('pos.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]], 'payment_method' => 'Cash', 'cash_received' => 2])->assertSessionHasErrors('items');
        $this->assertDatabaseCount('sales', 0);
    }
}
