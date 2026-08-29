<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\AdminAccess;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Table('users')]
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, MustVerifyEmail
{
    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_CATALOGUE_MANAGER = 'catalogue_manager';

    public const ROLE_ORDER_MANAGER = 'order_manager';

    public const ROLE_SUPPORT = 'support';

    public const ROLE_MARKETING = 'marketing';

    public const ROLE_FINANCE = 'finance';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;

    /**
     * Filament is an administrative boundary, not a customer feature.
     * Granular staff permissions will build on this deny-by-default gate.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if (! $user->isDirty('role')) {
                return;
            }

            $privileged = [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN];
            $wasPrivileged = in_array($user->getOriginal('role'), $privileged, true);
            $remainsPrivileged = in_array($user->role, $privileged, true);

            if ($wasPrivileged && ! $remainsPrivileged && self::query()->whereIn('role', $privileged)->count() <= 1) {
                throw new \DomainException('The final Super Admin cannot be demoted.');
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, AdminAccess::staffRoles(), true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    public function hasAdminPermission(string $permission): bool
    {
        return AdminAccess::has((string) $this->role, $permission);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function productQuestions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'actor_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }
}
