<?php

namespace App\Models;

use App\Enums\AdvertisementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'order', 'description', 'type', 'branch_id', 'product_id', 'category_id'];

    protected $casts = [
        'type' => AdvertisementType::class,
    ];

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
