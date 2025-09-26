<?php

namespace App\Services;

use App\Enums\UserNotificationType;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\UserNotification;
use InvalidArgumentException;

class DatabaseUserNotification
{
    public static function sendOrderStatusNotification(Order $order): void
    {
        [$title, $body] = match ($order->order_status->value) {
            2 => ['Order Processing', 'Your order is being processed.'],
            3 => ['Order Delivering', 'Your order is on the way!'],
            4 => ['Order Finished', 'Your order has been delivered successfully.'],
            5 => ['Order Cancelled', 'Your order has been cancelled.'],
            default => throw new InvalidArgumentException('Can not create FirebaseFCM for this order'),
        };

        UserNotification::create([
            'user_id' => $order->customer->id,
            'title' => $title,
            'body' => $body,
            'type' => UserNotificationType::GENERAL->value,
            'deep_link' => 'temporarily_link', // TODO: fix deep link
            'mark_as_read' => false,
        ]);
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        UserNotification::create([
            'user_id' => $ticket->customer->id,
            'title' => 'We’ve replied to your support request',
            'body' => $reply,
            'type' => UserNotificationType::GENERAL->value,
            'deep_link' => 'temporarily_link', // TODO: fix deep link
            'mark_as_read' => false,
        ]);
    }
}
