<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_code', 'item_name', 'item_name_en', 'item_type', 'item_group',
        'uom_id', 'uom_code', 'default_vendor_code', 'last_purchase_price',
        'min_order_qty', 'lead_time_days', 'requires_lot_tracking',
        'requires_expiry_date', 'is_active', 'sap_item_code',
    ];

    protected $casts = [
        'requires_lot_tracking' => 'boolean',
        'requires_expiry_date'  => 'boolean',
        'is_active'             => 'boolean',
        'last_purchase_price'   => 'decimal:4',
        'min_order_qty'         => 'decimal:4',
    ];

    public function uom(): BelongsTo { return $this->belongsTo(UomMaster::class, 'uom_id'); }
    public function defaultVendor(): BelongsTo { return $this->belongsTo(Supplier::class, 'default_vendor_code', 'code'); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
