<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseMaster extends Model
{
    protected $table = 'warehouse_masters';
    protected $fillable = ['code', 'name', 'type', 'location', 'is_cold_storage', 'is_active', 'sap_warehouse_code'];
    protected $casts = ['is_cold_storage' => 'boolean', 'is_active' => 'boolean'];
    public function scopeActive($q) { return $q->where('is_active', true); }
}
