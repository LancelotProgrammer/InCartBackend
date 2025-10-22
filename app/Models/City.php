<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'boundary'];

    public $casts = [
        'name' => 'array',
        'boundary' => 'array',
    ];

    public array $translatable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
