<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VendorInvitation extends Model
{
    protected $fillable = ['vendor_code', 'email', 'token', 'status', 'invited_by', 'expires_at', 'accepted_at'];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'vendor_code', 'code'); }
    public function invitedBy(): BelongsTo { return $this->belongsTo(User::class, 'invited_by'); }
    public function isExpired(): bool { return $this->expires_at->isPast(); }
    public function isPending(): bool { return $this->status === 'pending' && !$this->isExpired(); }

    public static function generate(string $vendorCode, string $email, int $invitedBy): self
    {
        return static::create([
            'vendor_code' => $vendorCode,
            'email'       => $email,
            'token'       => Str::uuid(),
            'invited_by'  => $invitedBy,
            'expires_at'  => now()->addDays(7),
        ]);
    }
}
