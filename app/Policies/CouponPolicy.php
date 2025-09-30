<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-coupon');
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('view-coupon');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-coupon');
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('update-coupon');
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('delete-coupon');
    }

    public function publish(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('publish-coupon');
    }

    public function unpublish(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('unpublish-coupon');
    }

    public function showCode(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('show-code-coupon');
    }
}
