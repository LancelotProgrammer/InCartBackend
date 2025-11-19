<?php

namespace App\Services;

use App\Constants\DeepLinks;
use App\Enums\UserNotificationType;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class DatabaseUserNotification
{
    public static function sendOrderStatusNotification(Order $order): void
    {
        Log::channel('app_log')->info('Services(DatabaseUserNotification): Sending order status notification', [
            'order_id' => $order->id
        ]);

        [$title, $body] = Order::getUserOrderNotificationMessage($order);

        UserNotification::create([
            'user_id' => $order->customer->id,
            'title' => $title,
            'body' => $body,
            'type' => UserNotificationType::GENERAL->value,
            'config' => ['route' => DeepLinks::ORDER_DEEP_LINK . "/$order->id"],
            'mark_as_read' => false,
        ]);
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        Log::channel('app_log')->info('Services(DatabaseUserNotification): Sending ticket notification', [
            'ticket_id' => $ticket->id
        ]);

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
