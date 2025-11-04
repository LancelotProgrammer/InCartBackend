<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;
use Spatie\Translatable\HasTranslations;

class CartProduct extends Pivot implements AuditableContract
{
    use Auditable, HasFactory, HasTranslations;

    public $incrementing = true;

    protected $table = 'cart_product';

    protected $fillable = ['cart_id', 'product_id', 'title', 'quantity', 'price', 'created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public array $translatable = ['title'];

    protected $auditInclude = ['cart_id', 'product_id', 'title', 'quantity', 'price'];

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
