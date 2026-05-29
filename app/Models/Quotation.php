<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    protected $fillable = [
        'qt_number', 'request_item_id', 'vendor_code', 'unit_price', 'quantity',
        'delivery_date', 'lead_time_days', 'payment_terms', 'status',
        'vendor_notes', 'reviewer_notes', 'reviewed_by', 'reviewed_at', 'document_path',
    ];

    protected $casts = [
        'unit_price'    => 'decimal:4',
        'quantity'      => 'decimal:4',
        'delivery_date' => 'date',
        'reviewed_at'   => 'datetime',
    ];

    public function requestItem(): BelongsTo
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'QT-' . now()->format('Y') . '-';
        $last   = static::where('qt_number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $next   = $last ? ((int) substr($last->qt_number, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
