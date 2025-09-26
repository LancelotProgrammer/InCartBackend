<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCreated;
use Illuminate\Support\Collection;

class DatabaseManagerNotification
{
    public static function getManagers(): Collection
    {
        return User::whereIn('id', SettingsService::getNotificationManagers())->get();
    }

    public static function sendCreatedOrderNotification(Order $order): void
    {
        foreach (self::getManagers() as $manager) {
            $manager->notify(new OrderCreated($order));
        }
    }

    public static function sendCancelledOrderNotification(Order $order): void
    {
        foreach (self::getManagers() as $manager) {
            $manager->notify(new OrderCancelled($order));
        }
    }
}
