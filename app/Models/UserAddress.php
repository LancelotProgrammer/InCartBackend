<?php

namespace App\Models;

use App\Enums\UserAddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'phone',
        'type',
        'latitude',
        'longitude',
        'city_id',
        'user_id',
    ];

    protected $casts = [
        'type' => UserAddressType::class,
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
