<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UomMaster extends Model
{
    protected $table = 'uom_masters';
    protected $fillable = ['code', 'name', 'purchase_unit', 'conversion_factor', 'sap_code', 'is_active'];

    protected $casts = ['conversion_factor' => 'decimal:4', 'is_active' => 'boolean'];

    public function scopeActive($query) { return $query->where('is_active', true); }

    /** Convert an inventory (base-unit) quantity into purchase units, rounded up. */
    public function toPurchaseQty(float $baseQty): float
    {
        $factor = (float) ($this->conversion_factor ?: 1);

        return $factor > 0 ? ceil($baseQty / $factor) : $baseQty;
    }
}
