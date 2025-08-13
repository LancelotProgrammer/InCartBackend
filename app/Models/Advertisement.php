<?php

namespace App\Models;

use App\Enums\AdvertisementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Advertisement extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title', 'description', 'order', 'type', 'published_at', 'branch_id', 'product_id', 'category_id'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'type' => AdvertisementType::class,
        'published_at' => 'datetime',
    ];

    public array $translatable = ['title', 'description'];

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'advertisement_file')->using(AdvertisementFile::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
