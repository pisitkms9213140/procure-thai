<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenPo extends Model
{
    protected $table = 'open_pos';

    protected $fillable = [
        'po_number', 'vendor_code', 'item_code', 'item_name', 'uom_code',
        'warehouse_code', 'ordered_qty', 'received_qty', 'unit_price',
        'po_date', 'required_date', 'status', 'sap_doc_entry', 'sap_doc_num',
        'source', 'imported_at',
    ];

    protected $casts = [
        'po_date'      => 'date',
        'required_date'=> 'date',
        'imported_at'  => 'datetime',
        'ordered_qty'  => 'decimal:4',
        'received_qty' => 'decimal:4',
        'unit_price'   => 'decimal:4',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'vendor_code', 'code'); }
    public function item(): BelongsTo { return $this->belongsTo(ItemMaster::class, 'item_code', 'item_code'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(WarehouseMaster::class, 'warehouse_code', 'code'); }
    public function scopeOpen($q) { return $q->whereIn('status', ['open', 'partial']); }
}
