<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_REGISTRAR = 'registrar';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_EDITOR => 'Editor Konten',
        self::ROLE_REGISTRAR => 'Admin Pendaftaran',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = [
        'role' => self::ROLE_EDITOR,
        'is_active' => true,
    ];

    /** The panel must always keep at least one active super admin. */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            $wasActiveSuper = $user->getOriginal('role') === self::ROLE_SUPER_ADMIN && $user->getOriginal('is_active');
            $staysActiveSuper = $user->role === self::ROLE_SUPER_ADMIN && $user->is_active;
            if ($wasActiveSuper && ! $staysActiveSuper && ! static::otherActiveSuperAdminExists($user)) {
                throw ValidationException::withMessages(['role' => 'Harus ada minimal satu Super Admin aktif. Tambahkan Super Admin lain terlebih dahulu.']);
            }
        });

        static::deleting(function (User $user) {
            if ($user->role === self::ROLE_SUPER_ADMIN && $user->is_active && ! static::otherActiveSuperAdminExists($user)) {
                throw ValidationException::withMessages(['role' => 'Super Admin terakhir tidak dapat dihapus.']);
            }
        });
    }

    public static function otherActiveSuperAdminExists(User $except): bool
    {
        return static::where('role', self::ROLE_SUPER_ADMIN)->where('is_active', true)->whereKeyNot($except->getKey())->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /** Super admin can do everything; others are limited to their area. */
    public function hasRole(string ...$roles): bool
    {
        return $this->isSuperAdmin() || in_array($this->role, $roles, true);
    }
}
