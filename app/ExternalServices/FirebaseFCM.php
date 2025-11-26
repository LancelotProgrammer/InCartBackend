<?php

namespace App\ExternalServices;

use App\Constants\DeepLinks;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
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
    public static function sendOrderStatusNotification(Order $order): void
    {
        Log::channel('app_log')->info('ExternalServices(FirebaseFCM): Sending order status notification', [
            'order_id' => $order->id
        ]);

        [$title, $body] = Order::getUserOrderNotificationMessage($order);

        self::sendTicketNotificationToUser(
            $order->customer,
            $title,
            $body,
            null,
            DeepLinks::ORDER_DEEP_LINK . "/$order->id"
        );
    }

    public static function sendTicketNotification(Ticket $ticket, string $reply): void
    {
        Log::channel('app_log')->info('ExternalServices(FirebaseFCM): Sending ticket notification', [
            'ticket_id' => $ticket->id
        ]);

        self::sendTicketNotificationToUser(
            $ticket->user,
            'We’ve replied to your support request',
            Ticket::trimTicketNotificationReply($reply),
            null,
            DeepLinks::TICKET_DEEP_LINK
        );
    }

    private static function sendTicketNotificationToUser(
        User $user,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $deepLink = null,
    ): void {
        $tokens = $user->firebaseTokens()->pluck('firebase_token')->toArray();

        if (empty($tokens)) {
            Log::channel('app_log')->notice('ExternalServices(FirebaseFCM): No tokens found for user', [
                'user_id' => $user->id
            ]);
            return;
        }

        foreach ($tokens as $token) {
            self::sendNotificationToToken(
                $token,
                $title,
                $body,
                $imageUrl,
                $deepLink
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
            $message = $message->withData(['path' => $deepLink]);
        }

        try {
            $response = $messaging->send($message);
            Log::channel('app_log')->info('ExternalServices(FirebaseFCM): FCM message sent successfully', [
                'token' => $token,
                'title' => $title,
                'body' => $body,
                'response' => $response,
            ]);
        } catch (NotFound $e) {
            Log::channel('app_log')->warning('ExternalServices(FirebaseFCM):FCM token not found, removing.', [
                'token' => $token,
                'errors' => $e->errors(),
                'firebase_token' => $e->token(),
            ]);
            UserFirebaseToken::where('firebase_token', $token)->delete();
        } catch (InvalidMessage $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): Invalid FCM message.', [
                'token' => $token,
                'errors' => $e->errors(),
            ]);
        } catch (ServerUnavailable $e) {
            $retryAfter = $e->retryAfter();
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): FCM server unavailable.', [
                'token' => $token,
                'retry_after' => $retryAfter?->format(\DATE_ATOM),
                'errors' => $e->errors(),
            ]);
        } catch (ServerError $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): FCM server error.', [
                'token' => $token,
                'errors' => $e->errors(),
            ]);
        } catch (MessagingException $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): Generic FCM messaging exception.', [
                'token' => $token,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): Unexpected FCM failure.', [
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
            Log::channel('app_log')->info('ExternalServices(FirebaseFCM): FCM token added to topic successfully', [
                'data' => [$token, $topics],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): Failed to add token to topic', [
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
            Log::channel('app_log')->info('ExternalServices(FirebaseFCM): FCM token removed from topic successfully', [
                'data' => [$token, $topics],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): Failed to remove token from topic', [
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
            $message = $message->withData(['path' => $deepLink]);
        }

        try {
            $response = $messaging->send($message);
            Log::channel('app_log')->info('ExternalServices(FirebaseFCM): FCM bulk message sent successfully', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(FirebaseFCM): FCM bulk message failed', [
                'data' => [$topic, $title, $body, $imageUrl, $deepLink],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
