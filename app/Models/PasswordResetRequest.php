<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'code', 'token', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];
}
