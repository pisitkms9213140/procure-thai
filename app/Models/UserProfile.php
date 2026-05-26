<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'vendor_code', 'role', 'phone', 'position'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_code', 'code');
    }

    public function isVendor(): bool { return $this->role === 'vendor'; }
    public function isPurchaser(): bool { return $this->role === 'purchaser'; }
    public function isWarehouse(): bool { return $this->role === 'warehouse'; }
    public function isAccountant(): bool { return $this->role === 'accountant'; }
}
