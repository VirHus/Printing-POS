<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'total_amount',
        'cash_received',
        'change_amount',
        'payment_method',
        'payment_reference',
        'customer_name',
        'order_notes',
        'cost_total',
        'cost_complete',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'cost_complete' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user, ?int $storeId = null): void
    {
        if (! $user->isAdministrator()) {
            $query->where('store_id', $user->store_id ?? 0);
        } elseif ($storeId) {
            $query->where('store_id', $storeId);
        }
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
