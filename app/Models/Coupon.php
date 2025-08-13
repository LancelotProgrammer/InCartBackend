<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'code', 'type', 'config'];

    protected $casts = [
        'type' => CouponType::class,
        'config' => 'array',
    ];
}
