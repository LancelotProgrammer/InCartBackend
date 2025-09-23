<?php

namespace App\Models;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Models\Scopes\BranchScope;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use Spatie\Translatable\HasTranslations;
use stdClass;

#[ScopedBy([BranchScope::class])]
class Advertisement extends Model
{
    use HasFactory, HasPublishAttribute, HasTranslations;

    protected $fillable = ['title', 'description', 'order', 'type', 'url', 'published_at', 'branch_id', 'product_id', 'category_id'];

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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'advertisement_user')->using(AdvertisementUser::class);
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

    public function getLinkAttribute(): mixed
    {
        return self::getLink($this);
    }

    public static function getLinkValue(stdClass $advertisement): mixed
    {
        return self::getLink($advertisement);
    }

    public static function getLink(stdClass|self $advertisement): mixed
    {
        return match (true) {
            $advertisement->product_id && $advertisement->category_id => AdvertisementLink::PRODUCT,
            $advertisement->category_id && ! $advertisement->product_id => AdvertisementLink::CATEGORY,
            ! $advertisement->category_id && ! $advertisement->product_id => AdvertisementLink::EXTERNAL,
            default => throw new InvalidArgumentException('Advertisement link is not supported'),
        };
    }
}
