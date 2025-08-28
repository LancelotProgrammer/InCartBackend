<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class BasePaymentGateway
{
    public function generateToken(): string 
    {
        do {
            $token = Str::random(32);
        } while (Order::where('payment_token', '=', $token)->exists());

        return $token;
    }

    public function payOrder(Order $order): void 
    {
        $order::update([
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $order->save();
    }
}
