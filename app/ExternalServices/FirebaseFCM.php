<?php

namespace App\ExternalServices;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class FirebaseFCM
{
    private const ORDER_DEEP_LINK = '/order-details';

    private const TICKET_DEEP_LINK = '/ticket-details';

    public static function sendOrderStatusNotification(Order $order): void
    {
        $messaging = Firebase::messaging();

        $tokens = $order->customer->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        [$title, $body] = match ($order->order_status->value) {
            2 => ['Order Processing', 'Your order is being processed.'],
            3 => ['Order Delivering', 'Your order is on the way!'],
            4 => ['Order Finished', 'Your order has been delivered successfully.'],
            5 => ['Order Cancelled', 'Your order has been cancelled.'],
            default => throw new InvalidArgumentException('Can not create message for this order'),
        };

        $notification = Notification::create(
            $title,
            $body
        );

        foreach ($tokens as $token) {
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData(['route' => self::ORDER_DEEP_LINK."/$order->id"])
                ->toToken($token);
            try {
                $messaging->send($message);
            } catch (Throwable $e) {
                Log::channel('error')->error("Firebase notification failed for token {$token}: ".$e->getMessage());
            }
        }
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        $messaging = Firebase::messaging();

        $tokens = $ticket->user->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $maxLength = 150;
        $body = strlen($reply) > $maxLength
            ? substr($reply, 0, $maxLength).'...'
            : $reply;

        $notification = Notification::create(
            'We’ve replied to your support request',
            $body
        );

        foreach ($tokens as $token) {
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData(['route' => self::TICKET_DEEP_LINK."/$ticket->id"])
                ->toToken($token);
            try {
                $messaging->send($message);
            } catch (Throwable $e) {
                Log::channel('error')->error("Firebase notification failed for token {$token}: ".$e->getMessage());
            }
        }
    }
}
