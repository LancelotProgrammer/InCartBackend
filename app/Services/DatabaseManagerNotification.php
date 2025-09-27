<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCreated;
use Illuminate\Support\Collection;

class DatabaseManagerNotification
{
    public static function getManagers(Order $order): Collection
    {
        return User::where('role_id', Role::where('code', '=', 'manager')->first()->id)
            ->where('branch_id', $order->branch_id)
            ->get();
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
