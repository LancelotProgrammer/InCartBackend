<?php

namespace App\Models;

use App\Enums\SettingType;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class Setting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type', 'group', 'is_locked'];

    protected $casts = [
        'type' => SettingType::class,
    ];

    protected $auditInclude = ['key', 'value', 'type', 'group', 'is_locked'];

    protected static function booted(): void
    {
        static::created(function (Setting $setting) {
            Log::channel('app_log')->info('Models: created new setting and deleted settings cache.', ['id' => $setting->id]);
            return CacheService::deleteSettingsCache();
        });
        static::updated(function (Setting $setting) {
            Log::channel('app_log')->info('Models: updated setting and deleted settings cache.', ['id' => $setting->id]);
            return CacheService::deleteSettingsCache();
        });
        static::deleted(function (Setting $setting) {
            Log::channel('app_log')->info('Models: deleted setting and deleted settings cache.', ['id' => $setting->id]);
            return CacheService::deleteSettingsCache();
        });
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}
