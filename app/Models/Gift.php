<?php

namespace App\Models;

use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Gift extends Model
{
    use HasFactory, HasPublishAttribute, HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'points',
        'code',
        'discount',
        'allowed_sub_total_price',
    ];

    public array $translatable = ['title', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_gift')->using(UserGift::class);
    }
}
