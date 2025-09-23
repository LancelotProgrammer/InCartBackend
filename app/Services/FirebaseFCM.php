<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class FirebaseFCM
{
    public static function sendOrderStatusNotification(Order $order): void
    {
        $messaging = Firebase::messaging();

        $tokens = $order->customer->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        // TODO: add translation
        [$title, $body] = match ($order->order_status->value) {
            2 => ['Order Processing', 'Your order is being processed.'],
            3 => ['Order Delivering', 'Your order is on the way!'],
            4 => ['Order Finished', 'Your order has been delivered successfully.'],
            5 => ['Order Cancelled', 'Your order has been cancelled.'],
            default => throw new InvalidArgumentException('Can not create FirebaseFCM for this order'),
        };

        $notification = Notification::create($title, $body);

        // TODO: use bulk
        foreach ($tokens as $token) {
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->toToken($token);
            try {
                $messaging->send($message);
            } catch (Throwable $e) {
                Log::channel('error')->error("Firebase notification failed for token {$token}: " . $e->getMessage());
            }
        }
    }
}
