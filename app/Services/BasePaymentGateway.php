<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\ExternalServices\MoyasarPaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BasePaymentGateway
{
    public static array $map = [
        'apple-pay' => MoyasarPaymentGateway::class,
        'google-pay' => MoyasarPaymentGateway::class,
        'mada-pay' => MoyasarPaymentGateway::class,
        'stc-pay' => MoyasarPaymentGateway::class,
    ];

    public static function make(string $code): PaymentGatewayInterface
    {
        $class = BasePaymentGateway::$map[$code] ?? null;
        if (! $class || ! in_array(PaymentGatewayInterface::class, class_implements($class))) {
            Log::critical('Payment gateway not found for {code}', ['code' => $code]);
            throw new InvalidArgumentException("Payment gateway not found for {$code}");
        }

        return app($class);
    }

    public function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Order::where('payment_token', '=', $token)->exists());

        return $token;
    }

    protected function payOrder(Order $order): void
    {
        $order->update([
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $order->save();
    }

    protected function refundOrder(Order $order): void
    {
        $order->update([
            'payment_status' => PaymentStatus::REFUNDED->value,
        ]);

        $order->save();
    }
}
