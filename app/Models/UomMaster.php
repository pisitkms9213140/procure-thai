<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UomMaster extends Model
{
    protected $table = 'uom_masters';
    protected $fillable = ['code', 'name', 'sap_code', 'is_active'];

    public function scopeActive($query) { return $query->where('is_active', true); }
}
