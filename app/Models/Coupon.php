<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Models\Scopes\BranchScope;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[ScopedBy([BranchScope::class])]
class Coupon extends Model
{
    use HasFactory, HasPublishAttribute, HasTranslations;

    protected $fillable = ['title', 'description', 'code', 'type', 'config', 'published_at', 'branch_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'type' => CouponType::class,
        'config' => 'array',
        'published_at' => 'datetime',
    ];

    public array $translatable = ['title', 'description'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
