<?php

namespace App\Services;

use App\Exceptions\LogicalException;
use App\Models\Gift;
use App\Models\Loyalty;
use App\Models\User;
use App\Models\UserGift;

class LoyaltyService
{
    public static function addPoints(User $user, float $subTotal): void
    {
        $loyalty = Loyalty::firstOrCreate(['user_id' => $user->id]);
        $loyalty->addPoints($subTotal);
    }

    public static function validateCode(User $user, string $code, float $subTotal): Gift
    {
        $gift = Gift::where('code', '=', $code)->first();

        if (! $gift) {
            throw new LogicalException('Invalid gift code.', 'Gift not found.');
        }

        $userGift = UserGift::where('user_id', '=', $user->id)->where('gift_id', $gift->id)->first();

        if (! $userGift) {
            throw new LogicalException('Invalid gift code.', 'User gift not found.');
        }

        if ($subTotal < $gift->allowed_sub_total_price) {
            throw new LogicalException('Subtotal too low to use this gift.', 'Subtotal too low to use this gift.');
        }

        UserGift::where('user_id', '=', $user->id)->where('gift_id', $gift->id)->delete();

        return $gift;
    }

    public static function redeemGift(User $user, int $giftId): Gift
    {
        $gift = Gift::published()->where('id', '=', $giftId)->first();

        if (!$gift) {
            throw new LogicalException('Gift not found.', 'Gift not found.');
        }

        if ($user->gifts()->where('gift_id', '=', $giftId)->exists()) {
            throw new LogicalException('Gift already redeemed.', 'Gift already redeemed.');
        }

        if ($gift->points > $user->loyalty->points) {
            throw new LogicalException('Not enough points to redeem this gift.', 'Not enough points to redeem this gift.');
        }

        $user->loyalty->redeemPoints($gift->points);
        $user->gifts()->attach($giftId);

        return $gift;
    }
}
