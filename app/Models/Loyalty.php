<?php

namespace App\Models;

use App\Exceptions\LogicalException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

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
        Log::channel('app_log')->info('Models: Start adding points', [
            'user_id' => $this->user_id,
            'amount' => $amount
        ]);

        $this->increment('points', $amount);
        $this->increment('total_earned', $amount);
        $this->save();

        Log::channel('app_log')->info('Models: Finish adding points', [
            'user_id' => $this->user_id,
            'amount' => $amount,
            'points' => $this->points
        ]);
    }

    public function redeemPoints(int $amount): void
    {
        Log::channel('app_log')->info('Models: Start redeeming points', [
            'user_id' => $this->user_id,
            'amount' => $amount
        ]);

        if ($this->points < $amount) {
            throw new LogicalException('Not enough points to redeem.', 'Not enough points to redeem.');
        }

        $this->decrement('points', $amount);
        $this->increment('total_redeemed', $amount);
        $this->save();

        Log::channel('app_log')->info('Models: Finish redeeming points', [
            'user_id' => $this->user_id,
            'amount' => $amount,
            'points' => $this->points
        ]);
    }
}
