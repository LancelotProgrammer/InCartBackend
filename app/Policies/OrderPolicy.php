<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-order');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('view-order')
            || (
                $user->role->code = Role::ROLE_DELIVERY_CODE &&
                $order->delivery_id === $user->id &&
                $order->delivery_date->isToday()
            );
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-order');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('update-order') &&
            ! ($order->order_status === OrderStatus::FINISHED || $order->order_status === OrderStatus::CANCELLED);
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

    public static function isEnabled(Order $order, $pageClass): bool
    {
        return $order->paymentMethod?->code === 'pay-on-delivery'
            && in_array($order->order_status, [
                OrderStatus::PENDING,
                OrderStatus::PROCESSING,
            ], true) && ! (app($pageClass) instanceof ViewRecord);
    }
}
