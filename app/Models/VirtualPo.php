<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualPo extends Model
{
    use SoftDeletes;

    protected $table = 'virtual_pos';

    protected $fillable = [
        'vpo_number', 'quotation_id', 'vendor_code', 'po_date', 'expected_delivery_date',
        'unit_price', 'ordered_qty', 'received_qty', 'total_amount', 'currency', 'status',
        'sap_po_number', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'po_date'               => 'date',
        'expected_delivery_date'=> 'date',
        'approved_at'           => 'datetime',
        'unit_price'            => 'decimal:4',
        'ordered_qty'           => 'decimal:4',
        'received_qty'          => 'decimal:4',
        'total_amount'          => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_code', 'code');
    }

    public static function generateNumber(): string
    {
        $prefix = 'VPO-' . now()->format('Y') . '-';
        $last   = static::withTrashed()->where('vpo_number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $next   = $last ? ((int) substr($last->vpo_number, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
