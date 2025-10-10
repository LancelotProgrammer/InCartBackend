<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Services\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title', 'description', 'unit', 'brand', 'sku'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'unit' => UnitType::class,
    ];

    public array $translatable = ['title', 'description'];

    protected static function booted(): void
    {
        static::created(fn (Product $product) => Cache::deleteHomeCache());
        static::updated(fn (Product $product) => Cache::deleteHomeCache());
        static::deleted(fn (Product $product) => Cache::deleteHomeCache());
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'product_file')->withPivot(['order'])->using(ProductFile::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_product')->using(BranchProduct::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_product')->using(PackageProduct::class);
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_product')->using(CartProduct::class);
    }

    public function branchProducts(): HasMany
    {
        return $this->hasMany(BranchProduct::class);
    }
}
