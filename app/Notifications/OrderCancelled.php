<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification
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
            ->title("Order #{$this->order->order_number} Cancelled")
            ->body(
                "Customer {$this->order->customer?->name}'s order totaling {$this->order->total_price} has been cancelled."
                .($this->order->cancel_reason ? " Reason: {$this->order->cancel_reason}" : '')
            )
            ->getDatabaseMessage();
    }
}
