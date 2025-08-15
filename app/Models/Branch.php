<?php

namespace App\Models;

use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Branch extends Model
{
    use HasFactory, HasPublishAttribute, HasTranslations;

    protected $fillable = ['title', 'description', 'published_at', 'longitude', 'latitude', 'city_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
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

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function UserAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }
}
