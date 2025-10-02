<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Permission extends Model
{
    use HasFactory, HasTranslations;

    public $timestamps = false;

    protected $fillable = ['title', 'code'];

    protected $casts = [
        'title' => 'array',
    ];

    public array $translatable = ['title'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
