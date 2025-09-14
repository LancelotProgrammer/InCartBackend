<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'pending_email',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'blocked_at',
        'role_id',
        'city_id',
        'file_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function notifications(): HasMany
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(File::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->email === null) {
            return false;
        }

        return str_ends_with($this->email, '@admin.com');
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
}
