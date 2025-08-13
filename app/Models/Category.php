<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations;

    public static int $maxDepth = 3;

    protected $fillable = ['title', 'description', 'published_at', 'parent_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'published_at' => 'datetime',
    ];

    public array $translatable = ['title', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allParents(): mixed
    {
        return $this->parent->with('allParents');
    }

    public function allChildren(): mixed
    {
        return $this->children()->with('allChildren');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }

    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($category) {
            if ($category->parent_id) {
                $parent = Category::find($category->parent_id);
                if (! $parent) {
                    throw new Exception('Parent category does not exist.');
                }
                $depth = 1;
                $currentParent = $parent;
                while ($currentParent) {
                    $depth++;
                    if ($depth > self::$maxDepth) {
                        throw new Exception('Category hierarchy depth cannot exceed 3 levels.');
                    }
                    $currentParent = $currentParent->parent;
                }
            }
        });
    }

    public function descendantIds(): Collection
    {
        $ids = collect();

        foreach ($this->children as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($child->descendantIds());
        }

        return $ids;
    }

    public function getDepthAttribute(): int
    {
        $depth = 1;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
