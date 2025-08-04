<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerificationRequest extends Model
{
    public $timestamps = false;
    protected $fillable = ['phone', 'code', 'created_at'];
}
