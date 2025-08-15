<?php

namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'mime', 'size', 'url'];

    protected $casts = [
        'type' => FileType::class,
    ];

    public function advertisements(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'advertisement_file')->using(AdvertisementFile::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'category_file')->using(CategoryFile::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'product_file')->using(ProductFile::class);
    }
}
