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
    public const ORDER_DEEP_LINK = '/order-details';

    public const TICKET_DEEP_LINK = '/support/1';

    public const PRODUCT_DEEP_LINK = '/product-details';

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
                $response = $messaging->send($message);
                Log::channel('debug')->info('FCM message sent successfully', [
                    'data' => [$order],
                    'response' => $response,
                ]);
            } catch (Throwable $e) {
                Log::channel('error')->debug('FCM message failed', [
                    'data' => [$order],
                    'error' => $e->getMessage(),
                ]);
                throw $e;
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
                $response = $messaging->send($message);
                Log::channel('debug')->info('FCM message sent successfully', [
                    'data' => [$ticket, $reply],
                    'response' => $response,
                ]);
            } catch (Throwable $e) {
                Log::channel('error')->debug('FCM message failed', [
                    'data' => [$ticket, $reply],
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    public static function subscribeToTopics(string $token, array $topics): void
    {
        try {
            $messaging = Firebase::messaging();
            $response = $messaging->subscribeToTopics($topics, $token);
            Log::channel('debug')->info('FCM token added to topic successfully', [
                'data' => [$token, $topics],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('error')->debug('Failed to add token to topic', [
                'data' => [$token, $topics],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public static function unsubscribeFromTopics(string $token, array $topics): void
    {
        try {
            $messaging = Firebase::messaging();
            $response = $messaging->unsubscribeFromTopics($topics, $token);
            Log::channel('debug')->info('FCM token removed from topic successfully', [
                'data' => [$token, $topics],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('error')->debug('Failed to remove token from topic', [
                'data' => [$token, $topics],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public static function sendNotificationToTopic(string $topic, string $title, string $body, ?string $imageUrl, ?string $deepLink): void
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        if ($imageUrl) {
            $notification = $notification->withImageUrl($imageUrl);
        }

        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification($notification);

        if ($deepLink) {
            $message = $message->withData(['route' => $deepLink]);
        }

        try {
            $response = $messaging->send($message);
            Log::channel('debug')->info('FCM message sent successfully', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('error')->debug('FCM message failed', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
