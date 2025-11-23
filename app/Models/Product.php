<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Events\ProductDeleting;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Builder;
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
            Log::channel('app_log')->info('Models: created new product and deleted home cache.', ['id' => $product->id]);
            return  CacheService::deleteHomeCache();
        });
        static::updated(function (Product $product) {
            Log::channel('app_log')->info('Models: updated product and deleted home cache.', ['id' => $product->id]);
            return  CacheService::deleteHomeCache();
        });
        static::deleting(function (Product $product) {
            Log::channel('app_log')->info('Models: deleting product.', ['id' => $product->id]);
            $product->branches()->detach();
        });
        static::deleted(function (Product $product) {
            Log::channel('app_log')->info('Models: deleted product and deleted home cache.', ['id' => $product->id]);
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

    public function isPublishedInBranches(): bool
    {
        return $this->branchProducts->whereNotNull('published_at')->isNotEmpty();
    }

    public function firstBranchProduct(): ?BranchProduct
    {
        return $this->branchProducts->whereNotNull('published_at')->first();
    }

    public function toApiArray(): array
    {
        $branchProduct = $this->firstBranchProduct();
        $image = $this->files->first()?->url;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $image,
            'max_limit' => $branchProduct?->maximum_order_quantity,
            'min_limit' => $branchProduct?->minimum_order_quantity,
            'price' => $branchProduct?->price,
            'discount' => $branchProduct?->discount,
            'discount_price' => $branchProduct?->discount_price,
            'expired_at' => $branchProduct?->expires_at,
        ];
    }
}
