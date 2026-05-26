<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'supplier_id', 'created_by', 'po_date', 'delivery_date',
        'status', 'subtotal', 'vat_amount', 'total_amount', 'currency',
        'shipping_address', 'payment_terms', 'notes', 'sent_at', 'acknowledged_at',
    ];

    protected $casts = [
        'po_date' => 'date',
        'delivery_date' => 'date',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function generatePoNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "PO-{$year}{$month}-";
        $last = static::withTrashed()
            ->where('po_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();
        $next = $last ? ((int) substr($last->po_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $vat = round($subtotal * 0.07, 2);
        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'total_amount' => $subtotal + $vat,
        ]);
    }
}
