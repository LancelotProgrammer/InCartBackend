<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        Log::channel('app_log')->info('Notifications: DeliveryOrderNotification', ['order' => $this->order, 'notifiable' => $notifiable]);

        return FilamentNotification::make()
            ->title("New Order #{$this->order->order_number}")
            ->getDatabaseMessage();
    }
}
