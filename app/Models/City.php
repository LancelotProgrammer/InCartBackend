<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name'];

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
