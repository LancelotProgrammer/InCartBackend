<?php

namespace App\Services;

use App\Exceptions\LogicalException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CouponService
{
    public static Carbon $time;

    public static UserAddress $userAddress;

    public static int $userId;

    public static int $branchId;

    public static array $productsIds;

    public static array $categoriesIds;

    public function __construct(
        Carbon $time,
        UserAddress $userAddress,
        int $userId,
        int $branchId,
        array $productsIds,
        array $categoriesIds,
    ) {
        self::$time = $time;
        self::$userAddress = $userAddress;
        self::$userId = $userId;
        self::$branchId = $branchId;
        self::$productsIds = $productsIds;
        self::$categoriesIds = $categoriesIds;
    }

    public static function calculateTimeCoupon(Coupon $coupon): float
    {
        $config = $coupon->config;

        $validator = Validator::make($config, [
            'value' => 'numeric|required',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'use_limit' => 'integer|nullable',
            'user_limit' => 'integer|nullable',
        ]);
        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid coupon config: '.$validator->errors()->first());
        }

        if (! empty($config['start_date']) && self::$time->lt(Carbon::parse($config['start_date']))) {
            throw new LogicalException('Coupon is not active yet.');
        }
        if (! empty($config['end_date']) && self::$time->gt(Carbon::parse($config['end_date']))) {
            throw new LogicalException('Coupon has expired.');
        }

        $totalUses = Order::where('coupon_id', $coupon->id)->count();
        $userUses = Order::where('coupon_id', $coupon->id)->where('user_id', self::$userId)->count();

        // Check user limit first
        if (! empty($config['user_limit']) && $userUses >= $config['user_limit']) {
            throw new LogicalException('You have already used this coupon the maximum number of times for your account.');
        }
        // Check total use limit
        if (! empty($config['use_limit']) && $totalUses >= $config['use_limit']) {
            throw new LogicalException('This coupon has reached its maximum number of uses.');
        }
        // Check if using the coupon now would exceed total limit
        if (! empty($config['use_limit']) && $totalUses + 1 > $config['use_limit']) {
            throw new LogicalException('Using this coupon now would exceed the total allowed uses.');
        }
        // Optional: prevent user from “taking” another user’s slot if near limit
        if (! empty($config['use_limit']) && ! empty($config['user_limit'])) {
            $remainingUses = $config['use_limit'] - $totalUses;
            $remainingForUser = $config['user_limit'] - $userUses;
            if ($remainingUses <= 0 || $remainingForUser <= 0) {
                throw new LogicalException('Coupon cannot be used due to limit restrictions.');
            }
        }

        return (float) $config['value'];
    }
}
