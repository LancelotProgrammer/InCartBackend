<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[ScopedBy([BranchScope::class])]
class PaymentMethod extends Model
{
    public const PAY_ON_DELIVERY_CODE = 'pay-on-delivery';

    use HasFactory, HasPublishAttribute, HasTranslations;

    public $timestamps = false;

    protected $fillable = ['title', 'code', 'order', 'published_at', 'branch_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'published_at' => 'datetime',
    ];

    public array $translatable = ['title'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
