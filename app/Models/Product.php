<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Events\ProductDeleting;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
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
        static::created(function (Product $product) {
            Log::info('Models: created new product and deleted home cache.', ['id' => $product->id]);
            return  CacheService::deleteHomeCache();
        });
        static::updated(function (Product $product) {
            Log::info('Models: updated product and deleted home cache.', ['id' => $product->id]);
            return  CacheService::deleteHomeCache();
        });
        static::deleting(function (Product $product){
            Log::info('Models: deleting product.', ['id' => $product->id]);
            return event(new ProductDeleting($product));
        });
        static::deleted(function (Product $product) {
            Log::info('Models: deleted product and deleted home cache.', ['id' => $product->id]);
            return  CacheService::deleteHomeCache();
        });
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
