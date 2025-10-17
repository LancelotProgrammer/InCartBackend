<?php

namespace App\ExternalServices;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\UserFirebaseToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\MessagingException;
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
        $tokens = $order->customer->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        [$title, $body] = Order::getOrderNotificationMessage($order);

        foreach ($tokens as $token) {
            self::sendNotificationToToken(
                $token,
                $title,
                $body,
                null,
                self::ORDER_DEEP_LINK . "/$order->id"
            );
        }
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        $tokens = $ticket->user->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        foreach ($tokens as $token) {
            self::sendNotificationToToken(
                $token,
                'We’ve replied to your support request',
                Ticket::getTicketNotificationReply($reply),
                null,
                self::TICKET_DEEP_LINK . "/$ticket->id"
            );
        }
    }

    private static function sendNotificationToToken(
        string $token,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $deepLink = null,
    ): void {
        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        if ($imageUrl) {
            $notification = $notification->withImageUrl($imageUrl);
        }

        $message = CloudMessage::new()
            ->toToken($token)
            ->withNotification($notification);

        if ($deepLink) {
            $message = $message->withData(['route' => $deepLink]);
        }

        try {
            $response = $messaging->send($message);
            Log::channel('debug')->info('FCM message sent successfully', [
                'token' => $token,
                'title' => $title,
                'body' => $body,
                'response' => $response,
            ]);
        } catch (NotFound $e) {
            Log::channel('error')->warning('FCM token not found, removing.', [
                'token' => $token,
                'errors' => $e->errors(),
                'firebase_token' => $e->token(),
            ]);
            UserFirebaseToken::where('firebase_token', $token)->delete();
        } catch (InvalidMessage $e) {
            Log::channel('error')->error('Invalid FCM message.', [
                'token' => $token,
                'errors' => $e->errors(),
            ]);
        } catch (ServerUnavailable $e) {
            $retryAfter = $e->retryAfter();
            Log::channel('error')->error('FCM server unavailable.', [
                'token' => $token,
                'retry_after' => $retryAfter?->format(\DATE_ATOM),
                'errors' => $e->errors(),
            ]);
        } catch (ServerError $e) {
            Log::channel('error')->error('FCM server error.', [
                'token' => $token,
                'errors' => $e->errors(),
            ]);
        } catch (MessagingException $e) {
            Log::channel('error')->error('Generic FCM messaging exception.', [
                'token' => $token,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);
        } catch (Throwable $e) {
            Log::channel('error')->error('Unexpected FCM failure.', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
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

    public static function unsubscribeFromTopics(array $token, array $topics): void
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

    public static function sendNotificationToTopic(
        string $topic,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $deepLink = null,
    ): void {
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
            Log::channel('debug')->info('FCM bulk message sent successfully', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel(channel: 'error')->debug('FCM bulk message failed', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
