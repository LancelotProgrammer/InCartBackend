<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class PaymentMethod extends Model
{
    use HasFactory, HasTranslations;

    public $timestamps = false;

    protected $fillable = ['title', 'order', 'branch_id', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    public array $translatable = ['title'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
