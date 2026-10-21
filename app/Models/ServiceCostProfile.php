<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCostProfile extends Model
{
    protected $fillable = ['store_id', 'product_id', 'estimated_unit_cost'];

    public function materials(): HasMany
    {
        return $this->hasMany(ServiceMaterial::class);
    }
}
