<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'avatar_url', 'signature_url', 'role', 'vendor_code', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ─── Role constants ───────────────────────────────────────────────
    const ROLE_MANAGER    = 'manager';
    const ROLE_SUPERVISOR = 'supervisor';
    const ROLE_STAFF      = 'staff';
    const ROLE_VENDOR     = 'vendor';

    public static function roleOptions(): array
    {
        return [
            self::ROLE_MANAGER    => '👑 Manager (Admin)',
            self::ROLE_SUPERVISOR => '🔷 Supervisor',
            self::ROLE_STAFF      => '👤 Staff',
            self::ROLE_VENDOR     => '🚚 Vendor (ซัพพลายเออร์)',
        ];
    }

    public static function roleBadgeColors(): array
    {
        return [
            self::ROLE_MANAGER    => 'warning',
            self::ROLE_SUPERVISOR => 'info',
            self::ROLE_STAFF      => 'gray',
            self::ROLE_VENDOR     => 'success',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleOptions()[$this->role] ?? '👤 Staff';
    }

    public function isManager(): bool    { return $this->role === self::ROLE_MANAGER; }
    public function isSupervisor(): bool { return $this->role === self::ROLE_SUPERVISOR; }
    public function isStaff(): bool      { return $this->role === self::ROLE_STAFF; }
    public function isVendor(): bool     { return $this->role === self::ROLE_VENDOR; }

    /** Manager + Supervisor can do anything a manager can */
    public function canManage(): bool    { return in_array($this->role, [self::ROLE_MANAGER, self::ROLE_SUPERVISOR]); }

    /** The supplier this vendor-user is linked to (via vendor_code → Supplier.code). */
    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_code', 'code');
    }

    // ─── Filament ─────────────────────────────────────────────────────
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar_url)
            : null;
    }

    // ─── Casts ────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
