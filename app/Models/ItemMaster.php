<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_code', 'item_name', 'item_name_en', 'barcode', 'item_type', 'item_group',
        'item_group_name', 'uom_id', 'uom_code', 'purchase_unit', 'conversion_factor',
        'default_vendor_code', 'default_warehouse_code', 'last_purchase_price',
        'min_order_qty', 'lead_time_days', 'requires_lot_tracking',
        'requires_expiry_date', 'is_active', 'sap_item_code', 'old_item_code', 'sap_raw',
    ];

    protected $casts = [
        'requires_lot_tracking' => 'boolean',
        'requires_expiry_date'  => 'boolean',
        'is_active'             => 'boolean',
        'last_purchase_price'   => 'decimal:4',
        'min_order_qty'         => 'decimal:4',
        'conversion_factor'     => 'decimal:4',
        'sap_raw'               => 'array',
    ];

    /** Convert an inventory (base-unit) quantity into purchase units, rounded up. */
    public function toPurchaseQty(float $baseQty): float
    {
        $factor = (float) ($this->conversion_factor ?: 1);

        return $factor > 0 ? ceil($baseQty / $factor) : $baseQty;
    }

    public function uom(): BelongsTo { return $this->belongsTo(UomMaster::class, 'uom_id'); }
    public function defaultVendor(): BelongsTo { return $this->belongsTo(Supplier::class, 'default_vendor_code', 'code'); }
    public function vendors(): HasMany { return $this->hasMany(ItemSupplier::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
