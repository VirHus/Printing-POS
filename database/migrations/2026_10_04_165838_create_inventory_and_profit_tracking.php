<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->restrictOnDelete();
                $table->string('name');
                $table->string('unit', 40);
                $table->decimal('quantity', 14, 3)->default(0);
                $table->decimal('unit_cost', 14, 4)->default(0);
                $table->decimal('low_stock_threshold', 14, 3)->default(0);
                $table->timestamps();
                $table->unique(['store_id', 'name']);
            });
        }
        if (! Schema::hasTable('service_cost_profiles')) {
            Schema::create('service_cost_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->restrictOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('estimated_unit_cost', 14, 4)->default(0);
                $table->timestamps();
                $table->unique(['store_id', 'product_id']);
            });
        }
        if (! Schema::hasTable('service_materials')) {
            Schema::create('service_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_cost_profile_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $table->decimal('quantity', 14, 3);
                $table->timestamps();
                $table->unique(['service_cost_profile_id', 'inventory_item_id'], 'service_material_pair_unique');
            });
        }
        if (! Schema::hasIndex('service_materials', 'service_material_pair_unique')) {
            Schema::table('service_materials', function (Blueprint $table) {
                $table->unique(['service_cost_profile_id', 'inventory_item_id'], 'service_material_pair_unique');
            });
        }
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 4);
            $table->decimal('total_cost', 14, 2);
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->index(['inventory_item_id', 'created_at']);
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 60);
            $table->string('description');
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->timestamps();
            $table->index(['store_id', 'expense_date']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('cost_total', 14, 2)->nullable();
            $table->boolean('cost_complete')->default(false);
        });
        $settings = DB::table('system_settings')->where('id', 1)->first();
        if ($settings) {
            $values = json_decode($settings->values, true);
            if (isset($values['sidebar_items'])) {
                foreach (['inventory.index' => 'Inventory', 'profit.index' => 'Profit & Expenses'] as $destination => $label) {
                    if (! in_array($destination, array_column($values['sidebar_items'], 'destination'), true)) {
                        $values['sidebar_items'][] = ['label' => $label, 'section' => 'Management', 'destination' => $destination, 'url' => '', 'icon' => $destination === 'inventory.index' ? 'store' : 'chart', 'admin' => true, 'cashier' => $destination === 'inventory.index'];
                    }
                }
                DB::table('system_settings')->where('id', 1)->update(['values' => json_encode($values)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cost_total', 'cost_complete']);
        });
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('service_materials');
        Schema::dropIfExists('service_cost_profiles');
        Schema::dropIfExists('inventory_items');
    }
};
