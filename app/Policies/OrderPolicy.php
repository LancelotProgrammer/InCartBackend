<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-order');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('view-order') || ($user->isDelivery() && $order->delivery_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-order');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('update-order') &&
            self::isEnabled($order) &&
            $user->id === $order->manager_id &&
            $user->belongsToUserBranch($order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission('delete-order') && $user->belongsToUserBranch($order);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasPermission('cancel-order') && $user->id === $order->manager_id && $user->belongsToUserBranch($order);
    }

    public function approve(User $user, Order $order): bool
    {
        return $user->hasPermission('approve-order') && $user->belongsToUserBranch($order);
    }

    public function forceApprove(User $user, Order $order): bool
    {
        return $user->hasPermission('force-approve-order') && $user->belongsToUserBranch($order);
    }

    public function selectDelivery(User $user, Order $order): bool
    {
        return $user->hasPermission('select-delivery-order') && $user->id === $order->manager_id && $user->belongsToUserBranch($order);
    }

    public function finish(User $user, Order $order): bool
    {
        return $user->hasPermission('finish-order') &&
            ($user->id === $order->manager_id || $user->id === $order->delivery_id) &&
            $user->belongsToUserBranch($order);
    }

    public function close(User $user, Order $order): bool
    {
        return $user->hasPermission('close-order') && $user->id === $order->manager_id && $user->belongsToUserBranch($order);
    }

    public function archive(User $user, Order $order): bool
    {
        return $user->hasPermission('archive-order') && $user->belongsToUserBranch($order);
    }

    public function viewInvoice(User $user, Order $order): bool
    {
        return $user->hasPermission('view-invoice-order') && $user->belongsToUserBranch($order);
    }

    public static function isEnabled(Order $order): bool
    {
        if ($order->payment_status !== PaymentStatus::UNPAID) {
            return false;
        }

        return $order->order_status === OrderStatus::PROCESSING;
    }
}
