<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\ExternalServices\MoyasarPaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
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
            Log::channel('app_log')->critical('Services(BasePaymentGateway): Payment gateway not found for {code}', ['code' => $code]);
            throw new InvalidArgumentException("Payment gateway not found for {$code}");
        }

        return app($class);
    }

    protected function createOrderPaymentToken(Order $order, string $paymentToken): void
    {
        $order->update([
            'payment_token' => $paymentToken
        ]);

        $order->save();
    }

    protected function payOrder(Order $order): void
    {
        $order->update([
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $order->save();
    }

    protected function revokeOrderPaymentToken(Order $order): void
    {
        $order->update([
            'payment_token' => null
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
