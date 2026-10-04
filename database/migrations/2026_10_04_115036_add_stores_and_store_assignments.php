<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        $storeId = DB::table('stores')->insertGetId(['name' => 'Main Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->restrictOnDelete();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->restrictOnDelete();
            $table->index(['store_id', 'created_at']);
        });
        DB::table('users')->update(['store_id' => $storeId]);
        DB::table('sales')->update(['store_id' => $storeId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'created_at']);
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::dropIfExists('stores');
    }
};
