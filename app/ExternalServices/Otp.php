<?php

namespace App\ExternalServices;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class Otp
{
    public static function send(string $phone, string $code): void
    {
        Log::debug("Sending OTP $code to phone: $phone");

        // $sid = config('services.twilio.account_sid');
        // $token = config('services.twilio.auth_token');
        // $number = config('services.twilio.number');
        // $appName = config('app.name');
        // $client = new Client($sid, $token);
        // $client->messages->create(
        //     $phone,
        //     [
        //         'From' => $number,
        //         'body' => "OTP code for $appName App is $code"
        //     ]
        // );
    }
}
