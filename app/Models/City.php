<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    public $timestamps = false;

    protected $fillable = ['name', 'latitude', 'longitude'];

    public $casts = [
        'name' => 'array',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public array $translatable = ['name'];

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
