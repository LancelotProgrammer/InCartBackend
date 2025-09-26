<?php

namespace App\Models;

use App\Enums\OtpType;
use Illuminate\Database\Eloquent\Model;

class PhoneVerificationRequest extends Model
{
    public $timestamps = false;

    protected $fillable = ['phone', 'code', 'type', 'created_at'];

    protected $casts = [
        'type' => OtpType::class,
        'created_at' => 'datetime',
    ];
}
