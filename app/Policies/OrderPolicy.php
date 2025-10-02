<?php

namespace App\Policies;

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
        return $user->hasPermission('view-order');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-order');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('update-order');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission('delete-order');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasPermission('cancel-order');
    }

    public function approve(User $user, Order $order): bool
    {
        return $user->hasPermission('approve-order');
    }

    public function selectDelivery(User $user, Order $order): bool
    {
        return $user->hasPermission('select-delivery-order');
    }

    public function finish(User $user, Order $order): bool
    {
        return $user->hasPermission('finish-order');
    }

    public function archive(User $user, Order $order): bool
    {
        return $user->hasPermission('archive-order');
    }

    public function viewInvoice(User $user, Order $order): bool
    {
        return $user->hasPermission('view-invoice-order');
    }
}
