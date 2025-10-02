<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class DeliveryOrderNotification extends Notification
{
    public function __construct(private readonly Order $order)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title("New Order #{$this->order->order_number}")
            ->getDatabaseMessage();
    }
}
