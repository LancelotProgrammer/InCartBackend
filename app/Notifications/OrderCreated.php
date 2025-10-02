<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification
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
            ->body(
                "Customer {$this->order->customer?->name} placed an order with a total of {$this->order->total_price}."
                    .($this->order->delivery_date ? " Scheduled for {$this->order->delivery_date->format('Y-m-d H:i')}." : '')
            )
            ->actions([
                Action::make('manage')
                    ->label('Manage Order')
                    ->url(route('filament.admin.resources.orders.edit', $this->order->id))
                    ->openUrlInNewTab(),
            ])
            ->getDatabaseMessage();
    }
}
