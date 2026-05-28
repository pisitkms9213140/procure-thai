<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'tax_id', 'email', 'phone', 'contact_person',
        'address', 'province', 'postcode', 'type', 'channel', 'status',
        'payment_terms', 'notes',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'SUP-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /** Synthesized login email for this vendor: code without hyphen/space @ subdomain. */
    public function vendorEmail(): string
    {
        $id   = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $this->code));
        $host = (tenant('id') ?: 'app') . '.procurethai.uk';

        return $id . '@' . $host;
    }

    /** The linked vendor-role user (if any). */
    public function vendorUser(): ?User
    {
        return User::where('vendor_code', $this->code)->first();
    }
}
