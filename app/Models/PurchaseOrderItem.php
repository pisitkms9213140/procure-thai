<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_code', 'description', 'unit',
        'quantity', 'unit_price', 'discount_percent', 'line_total', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function calculateLineTotal(): float
    {
        $gross = (float) $this->quantity * (float) $this->unit_price;
        $discount = $gross * ((float) $this->discount_percent / 100);
        return round($gross - $discount, 2);
    }
}
