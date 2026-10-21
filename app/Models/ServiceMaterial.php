<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMaterial extends Model
{
    protected $fillable = ['service_cost_profile_id', 'inventory_item_id', 'quantity'];
}
