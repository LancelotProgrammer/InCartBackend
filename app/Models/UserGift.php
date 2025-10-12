<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGift extends Model
{
    public $incrementing = true;

    protected $table = 'user_gift';

    protected $fillable = [
        'gift_id',
        'user_id',
    ];

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
