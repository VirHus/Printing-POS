<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['inventory_item_id', 'user_id', 'sale_id', 'type', 'quantity', 'unit_cost', 'total_cost', 'notes'];

    protected $casts = ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:4', 'total_cost' => 'decimal:2'];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
