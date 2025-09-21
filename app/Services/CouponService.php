<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\UserAddress;
use Illuminate\Support\Carbon;

class CouponService
{
    public Carbon $time;

    public UserAddress $userAddress;

    public Cart $cart;

    public int $userId;

    public int $branchId;

    public array $productsIds;

    public array $categoriesIds;

    public function __construct(
        Carbon $time,
        UserAddress $userAddress,
        Cart $cart,
        int $userId,
        int $branchId,
        array $productsIds,
        array $categoriesIds,
    ) {
        $this->time = $time;
        $this->userAddress = $userAddress;
        $this->cart = $cart;
        $this->userId = $userId;
        $this->branchId = $branchId;
        $this->productsIds = $productsIds;
        $this->categoriesIds = $categoriesIds;
    }

    public function calculateDiscount(Coupon $coupon): float
    {
        return $coupon->type->calculateDiscount($this, $coupon);
    }
}
