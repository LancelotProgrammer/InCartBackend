<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class SendFirebaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *  php artisan firebase:send-token <token>
     *  php artisan firebase:send-token --topic=all-users
     */
    protected $signature = 'firebase:send-token
                            {token? : The Firebase device token (optional if using --topic)}
                            {--topic= : The Firebase topic name (optional)}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test Firebase notification to a device token or topic';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (App::environment(['production', 'staging'])) {
            return;
        }

        $token = $this->argument('token');
        $topic = $this->option('topic');

        if (! $token && ! $topic) {
            $this->error('❌ Please provide either a token or a topic.');
            $this->line('Example:');
            $this->line('  php artisan firebase:send-token <token>');
            $this->line('  php artisan firebase:send-token --topic=all-users');

            return;
        }

        Log::channel('app_log')->debug('Commands: Sending Firebase notification.', [
            'token' => $token,
            'topic' => $topic,
        ]);

        $messaging = Firebase::messaging();

        // Create the base notification
        $notification = Notification::create(
            '🚀 Test Notification',
            'This is a test message from Laravel console command.'
        );

        // Build message with common data
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData(['type' => 'test']);

        // Send either to topic or token
        if ($topic) {
            $message = $message->toTopic($topic);
            $this->info("📢 Sending to topic: {$topic}");
        } else {
            $message = $message->toToken($token);
            $this->info("📱 Sending to token: {$token}");
        }

        try {
            $response = $messaging->send($message);

            $this->info('✅ Notification sent successfully!');
            $this->line('Response: ' . json_encode($response));
            Log::channel('app_log')->debug('Commands: Notification sent successfully.', [
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            $this->error('❌ Failed to send notification: ' . $e->getMessage());
            Log::channel('app_log')->debug('Commands: Failed to send notification.', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::channel('app_log')->debug('Commands: send firebase notification finished.');

        return;
    }
}
