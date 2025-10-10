<?php

namespace App\Models;

use App\Traits\HasIsDefaultAttribute;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Branch extends Model
{
    use HasIsDefaultAttribute, HasPublishAttribute, HasTranslations;

    protected $fillable = ['title', 'description', 'is_default', 'published_at', 'latitude', 'longitude', 'city_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'is_default' => 'boolean',
        'published_at' => 'datetime',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public array $translatable = ['title', 'description'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'branch_product')->using(BranchProduct::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')->using(BranchUser::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function deliveryUsers(): BelongsToMany
    {
        return $this->users()->whereHas('role', function ($query) {
            $query->where('roles.code', Role::ROLE_DELIVERY_CODE);
        });
    }

    public function notificationUsers(): BelongsToMany
    {
        return $this->users()->whereHas('role.permissions', function ($query) {
            $query->where('permissions.code', 'can-receive-order-notifications');
        });
    }
}
