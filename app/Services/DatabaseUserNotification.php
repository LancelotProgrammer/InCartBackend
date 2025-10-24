<?php

namespace App\Services;

use App\Constants\DeepLinks;
use App\Enums\UserNotificationType;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\UserNotification;

class DatabaseUserNotification
{
    public static function sendOrderStatusNotification(Order $order): void
    {
        [$title, $body] = Order::getOrderNotificationMessage($order);

        UserNotification::create([
            'user_id' => $order->customer->id,
            'title' => $title,
            'body' => $body,
            'type' => UserNotificationType::GENERAL->value,
            'config' => ['route' => DeepLinks::ORDER_DEEP_LINK."/$order->id"],
            'mark_as_read' => false,
        ]);
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        UserNotification::create([
            'user_id' => $ticket->user->id,
            'title' => 'We’ve replied to your support request',
            'body' => Ticket::trimTicketNotificationReply($reply),
            'type' => UserNotificationType::GENERAL->value,
            'config' => ['route' => DeepLinks::TICKET_DEEP_LINK],
            'mark_as_read' => false,
        ]);
    }
}
