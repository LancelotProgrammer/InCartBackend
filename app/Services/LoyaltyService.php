<?php

namespace App\Services;

use App\Exceptions\LogicalException;
use App\Models\Gift;
use App\Models\Loyalty;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public static function addPoints(User $user, int $subTotal): void
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
            throw new LogicalException('Subtotal too low to use this gift.', 'Rise the price of the cart or cancel the coupon.');
        }

        return $gift;
    }

    public static function applyGift(int $userId, int $giftId): void
    {
        UserGift::where('user_id', '=', $userId)
            ->where('gift_id', '=', $giftId)
            ->delete();
    }

    public static function redeemGift(User $user, int $giftId): Gift
    {
        $gift = Gift::published()->where('id', '=', $giftId)->first();

        if (! $gift) {
            throw new LogicalException('Gift not found.', 'Gift not found.');
        }

        if ($user->gifts()->where('gift_id', '=', $giftId)->exists()) {
            throw new LogicalException('Gift already redeemed.', 'You can not redeem a gift twice.');
        }

        $userLoyaltyPoints = $user->loyalty->points ?? 0;

        if ($gift->points > $userLoyaltyPoints) {
            throw new LogicalException('Not enough points to redeem.', 'The current user points are less than the gift points.');
        }

        DB::transaction(function () use ($user, $gift) {
            $user->loyalty->redeemPoints($gift->points);
            $user->gifts()->attach($gift->id);
        });

        return $gift;
    }
}
