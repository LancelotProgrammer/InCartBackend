<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Models\Scopes\BranchScope;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;
use Spatie\Translatable\HasTranslations;

#[ScopedBy([BranchScope::class])]
class Coupon extends Model implements AuditableContract
{
    use HasFactory, HasPublishAttribute, HasTranslations, Auditable;

    protected $fillable = ['title', 'description', 'code', 'type', 'config', 'published_at', 'branch_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'type' => CouponType::class,
        'config' => 'array',
        'published_at' => 'datetime',
    ];

    public array $translatable = ['title', 'description'];

    protected $auditInclude = [
        'title',
        'description',
        'type',
        'config',
        'published_at',
    ];

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
