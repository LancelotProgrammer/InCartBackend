<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Services\SessionService;
use App\Traits\CanManagePermissions;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class User extends Authenticatable implements AuditableContract, FilamentUser, MustVerifyEmail
{
    use Auditable, CanManagePermissions, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'pending_email',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'blocked_at',
        'approved_at',
        'role_id',
        'city_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $auditInclude = [
        'name',
        'phone',
        'email',
        'pending_email',
        'email_verified_at',
        'phone_verified_at',
        'blocked_at',
        'approved_at',
        'role_id',
        'city_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permission(): HasManyThrough
    {
        return $this->hasManyThrough(Permission::class, Role::class);
    }

    public function advertisements(): BelongsToMany
    {
        return $this->belongsToMany(Advertisement::class, 'advertisement_user')->using(AdvertisementUser::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')->using(BranchUser::class);
    }

    public function gifts(): BelongsToMany
    {
        return $this->belongsToMany(Gift::class, 'user_gift')->using(UserGift::class);
    }

    public function loyalty(): HasOne
    {
        return $this->hasOne(Loyalty::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function firebaseTokens(): HasMany
    {
        return $this->hasMany(UserFirebaseToken::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function unfinishedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id')
            ->whereNotIn('order_status', [OrderStatus::FINISHED, OrderStatus::CANCELLED]);
    }

    public function managerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'manager_id');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(File::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isBlocked()) {
            return false;
        }

        if ($this->role->code === Role::ROLE_CUSTOMER_CODE) {
            return false;
        }

        if (!in_array(
            $this->role->code,
            [Role::ROLE_SUPER_ADMIN_CODE, Role::ROLE_DEVELOPER_CODE]
        )) {
            return $this->branches()->exists();
        }

        return true;
    }

    protected function ensureHasBlockedAt(): void
    {
        if (! array_key_exists('blocked_at', $this->attributes)) {
            throw new InvalidArgumentException(sprintf(
                "The model %s does not have a 'blocked_at' attribute.",
                static::class
            ));
        }
    }

    public function block(): void
    {
        $this->ensureHasBlockedAt();
        $this->blocked_at = now();
        $this->save();

        $this->tokens()->delete();
        SessionService::deleteUserSessions($this->id);
    }

    public function unBlock(): void
    {
        $this->ensureHasBlockedAt();
        $this->blocked_at = null;
        $this->save();
    }

    public function scopeBlock(Builder $query): void
    {
        $query->whereNotNull('blocked_at');
    }

    public function scopeUnblock(Builder $query): void
    {
        $query->whereNull('blocked_at');
    }

    public function isBlocked(): bool
    {
        return ! is_null($this->blocked_at);
    }

    protected function ensureHasApprovedAt(): void
    {
        if (! array_key_exists('approved_at', $this->attributes)) {
            throw new InvalidArgumentException(sprintf(
                "The model %s does not have a 'approved_at' attribute.",
                static::class
            ));
        }
    }

    public function approve(): void
    {
        $this->ensureHasApprovedAt();
        $this->approved_at = now();
        $this->save();
    }

    public function unApprove(): void
    {
        $this->ensureHasApprovedAt();
        $this->approved_at = null;
        $this->save();
    }

    public function scopeApproved(Builder $query): void
    {
        $query->whereNotNull('approved_at');
    }

    public function scopeUnapproved(Builder $query): void
    {
        $query->whereNull('approved_at');
    }

    public function isApproved(): bool
    {
        return ! is_null($this->approved_at);
    }

    public function auditsLogs(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function canImpersonate()
    {
        return $this->hasPermission('impersonate-user');
    }

    public function canBeImpersonated()
    {
        return !in_array(
            $this->role->code,
            [Role::ROLE_SUPER_ADMIN_CODE, Role::ROLE_DEVELOPER_CODE, Role::ROLE_CUSTOMER_CODE]
        );
    }
}
