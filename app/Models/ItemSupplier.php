<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSupplier extends Model
{
    protected $table = 'item_suppliers';

    protected $fillable = [
        'item_id', 'vendor_code', 'vendor_item_code', 'price',
        'lead_time_days', 'min_order_qty', 'is_preferred', 'notes',
    ];

    protected $casts = [
        'price'         => 'decimal:4',
        'min_order_qty' => 'decimal:4',
        'is_preferred'  => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_code', 'code');
    }
}
