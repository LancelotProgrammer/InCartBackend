<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Coupon extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title', 'description', 'code', 'type', 'config', 'published_at', 'branch_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'type' => CouponType::class,
        'config' => 'array',
        'published_at' => 'datetime',
        'datetime' => 'datetime',
    ];

    public array $translatable = ['title'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
