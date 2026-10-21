<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'name', 'unit', 'quantity', 'unit_cost', 'low_stock_threshold', 'purchase_unit', 'units_per_pack'];

    protected $casts = ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:4', 'low_stock_threshold' => 'decimal:3', 'units_per_pack' => 'decimal:3'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
