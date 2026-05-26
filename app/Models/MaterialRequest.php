<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mr_number', 'created_by', 'request_date', 'required_date',
        'status', 'priority', 'department', 'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestItem::class)->orderBy('sort_order');
    }

    public static function generateNumber(): string
    {
        $prefix = 'MR-' . now()->format('Y') . '-';
        $last = static::withTrashed()->where('mr_number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->mr_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
