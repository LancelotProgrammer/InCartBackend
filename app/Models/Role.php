<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Role extends Model
{
    use HasFactory, HasTranslations;

    const ROLE_SUPER_ADMIN_CODE = 'super-admin';

    const ROLE_DEVELOPER_CODE = 'developer';

    const ROLE_MANAGER_CODE = 'manager';

    const ROLE_DELIVERY_CODE = 'delivery';

    const ROLE_CUSTOMER_CODE = 'customer';

    public $timestamps = false;

    protected $fillable = ['title', 'code'];

    protected $casts = [
        'title' => 'array',
    ];

    public array $translatable = ['title'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
