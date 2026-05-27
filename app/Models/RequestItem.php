<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    protected $fillable = [
        'material_request_id', 'vendor_code', 'item_code', 'description',
        'unit', 'quantity', 'budget_price', 'status', 'notes', 'sort_order',
    ];

    protected $casts = [
        'quantity'     => 'decimal:4',
        'budget_price' => 'decimal:4',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_code', 'code');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'item_code', 'item_code');
    }
}
