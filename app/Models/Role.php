<?php

namespace App\Models;

use App\Events\RoleDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;
use Spatie\Translatable\HasTranslations;

class Role extends Model implements AuditableContract
{
    use Auditable, HasFactory, HasTranslations;

    const ROLE_SUPER_ADMIN_CODE = 'super-admin';

    const ROLE_DEVELOPER_CODE = 'developer';

    const ROLE_MANAGER_CODE = 'manager';

    const ROLE_DELIVERY_CODE = 'delivery';

    const ROLE_CUSTOMER_CODE = 'customer';

    protected $fillable = ['title', 'code'];

    protected $casts = [
        'title' => 'array',
    ];

    public array $translatable = ['title'];

    protected $auditInclude = ['title', 'code'];

    protected static function booted(): void
    {
        static::deleting(function (Role $role) {
            Log::channel('app_log')->info('Models: deleting role.', ['id' => $role->id]);
            $role->permissions()->detach();
        });
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->using(RolePermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected function getOriginalPermissions(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    public function isSuperAdminOrDeveloperOrCustomer(): bool
    {
        return in_array(
            $this->code,
            [
                self::ROLE_SUPER_ADMIN_CODE,
                self::ROLE_DEVELOPER_CODE,
                self::ROLE_CUSTOMER_CODE,
            ]
        );
    }
}
