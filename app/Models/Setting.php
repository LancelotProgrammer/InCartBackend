<?php

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class Setting extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type', 'group', 'is_locked'];

    protected $casts = [
        'type' => SettingType::class,
    ];

    protected $auditInclude = ['key', 'value', 'type', 'group', 'is_locked'];

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}
