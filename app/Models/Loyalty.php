<?php

namespace App\Models;

use App\Exceptions\LogicalException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loyalty extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'total_earned',
        'total_redeemed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addPoints(int $amount): void
    {
        $this->increment('points', $amount);
        $this->increment('total_earned', $amount);
        $this->save();
    }

    public function redeemPoints(int $amount): void
    {
        if ($this->points < $amount) {
            throw new LogicalException('Not enough points to redeem.', 'Not enough points to redeem.');
        }

        $this->decrement('points', $amount);
        $this->increment('total_redeemed', $amount);
        $this->save();
    }
}
