<?php

namespace App\Models;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Models\Scopes\BranchScope;
use App\Services\CacheService;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
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

    protected static function booted(): void
    {
        static::created(function (Advertisement $advertisement) {
            Log::channel('app_log')->info('Models: created new advertisement and deleted home cache.', ['id' => $advertisement->id]);
            return CacheService::deleteHomeCache();
        });
        static::updated(function (Advertisement $advertisement) {
            Log::channel('app_log')->info('Models: updated advertisement and deleted home cache.', ['id' => $advertisement->id]);
            return CacheService::deleteHomeCache();
        });
        static::deleted(function (Advertisement $advertisement) {
            Log::channel('app_log')->info('Models: deleted advertisement and deleted home cache.', ['id' => $advertisement->id]);
            return CacheService::deleteHomeCache();
        });
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'advertisement_file')->withPivot(['order'])->using(AdvertisementFile::class);
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

    public function getCanBePublishedAttribute(): bool
    {
        $condition1 = $this->category !== null ? $this->category->published_at !== null : true;
        $condition2 = $this->product !== null ? $this->product->branchProducts()->where('branch_id', '=', $this->branch_id)->whereNotNull('published_at')->exists() : true;

        return $condition1 && $condition2;
    }

    public function getCanNotBePublishedReasonAttribute(): string
    {
        if ($this->can_be_published) {
            return trans('This advertisement can be published.');
        }

        $reasons = [];

        if ($this->category === null) {
            $reasons[] = trans('This advertisement has no category assigned.');
        } elseif ($this->category->published_at === null) {
            $reasons[] = trans('The category ":name" is not published.', [
                'name' => $this->category->getTranslation('title', app()->getLocale()) ?? trans('(Unnamed Category)'),
            ]);
        }

        if ($this->product === null) {
            $reasons[] = trans('This advertisement has no product assigned.');
        } else {
            $isProductPublishedInBranch = $this->product
                ->branchProducts()
                ->where('branch_id', $this->branch_id)
                ->whereNotNull('published_at')
                ->exists();

            if (! $isProductPublishedInBranch) {
                $reasons[] = trans('The product ":name" is not published in this branch.', [
                    'name' => $this->product->getTranslation('title', app()->getLocale()) ?? trans('(Unnamed Product)'),
                ]);
            }
        }

        if (empty($reasons)) {
            return trans('This advertisement cannot be published due to unknown reasons.');
        }

        return implode("\n• ", array_merge(
            [trans('This advertisement cannot be published because:')],
            $reasons
        ));
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
            default => self::logUnknownAndThrow($advertisement),
        };
    }

    protected static function logUnknownAndThrow($advertisement)
    {
        Log::channel('app_log')->critical('Unsupported advertisement link', [
            'advertisement' => $advertisement,
        ]);

        throw new InvalidArgumentException('Advertisement link is not supported');
    }
}
