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
    public Carbon $time;

    public UserAddress $userAddress;

    public int $userId;

    public int $branchId;

    public array $productsIds;

    public array $categoriesIds;

    public function __construct(
        Carbon $time,
        UserAddress $userAddress,
        int $userId,
        int $branchId,
        array $productsIds,
        array $categoriesIds,
    ) {
        $this->time = $time;
        $this->userAddress = $userAddress;
        $this->userId = $userId;
        $this->branchId = $branchId;
        $this->productsIds = $productsIds;
        $this->categoriesIds = $categoriesIds;
    }

    public function calculateTimeCoupon(Coupon $coupon): float
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

        if (! empty($config['start_date']) && $this->time->lt(Carbon::parse($config['start_date']))) {
            throw new LogicalException('Coupon is not active yet.');
        }
        if (! empty($config['end_date']) && $this->time->gt(Carbon::parse($config['end_date']))) {
            throw new LogicalException('Coupon has expired.');
        }

        $totalUses = Order::where('coupon_id', $coupon->id)->count();
        $userUses = Order::where('coupon_id', $coupon->id)->where('user_id', $this->userId)->count();

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
