<?php

namespace App\Models;

use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations, HasPublishAttribute;

    protected $fillable = ['name', 'boundary', 'code', 'published_at'];

    public $casts = [
        'name' => 'array',
        'boundary' => 'array',
        'published_at' => 'datetime',
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
