<?php

namespace App\Services;

use App\Exceptions\SetupException;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCreated;
use Illuminate\Support\Collection;

class DatabaseManagerNotification
{
    public static function getManagers(Order $order): Collection
    {
        $users = User::whereHas('branches', function ($query) use ($order) {
            $query->where('branches.id', $order->branch_id);
        })
            ->getUsersWhoCanReceiveOrderNotifications()->get();

        if ($users->isEmpty()) {
            throw new SetupException(
                'something_went_wrong',
                'System setup error. Pleas attach the {can-receive-order-notifications} permission to a role and assign it to a user.'
            );
        }

        return $users;
    }

    public static function sendCreatedOrderNotification(Order $order): void
    {
        foreach (self::getManagers($order) as $manager) {
            $manager->notify(new OrderCreated($order));
        }
    }

    public static function sendCancelledOrderNotification(Order $order): void
    {
        foreach (self::getManagers($order) as $manager) {
            $manager->notify(new OrderCancelled($order));
        }
    }
}
