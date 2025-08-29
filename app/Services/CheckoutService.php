<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CheckoutService
{
    public static function validateRequest(Request $request)
    {
        $request->validate([
            'order_id' => 'required|int|exists:orders,id',
            'payment_method_id' => 'required|int|exists:payment_methods,id',
            'token' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);
    }

    public static function checkout(Request $request): void
    {
        $paymentMethod = PaymentMethod::findOrFail($request->input('payment_method_id'));
        $order = Order::where('payment_token', '=', 'order_id')->first();
        if ($paymentMethod->code === 'pay-on-delivery') {
            throw new LogicalException('Payment method is pay-on-delivery and it is already checked out');
        }
        if ($order->payment_status === PaymentStatus::PAID->value) {
            throw new LogicalException('order is already payed');
        }
        self::resolvePaymentGateway($paymentMethod, $request);
    }

    private static function resolvePaymentGateway(PaymentMethod $paymentMethod, Request $request): void
    {
        $map = [
            'apple-pay' => MoyasarPaymentGateway::class,
            'google-pay' => MoyasarPaymentGateway::class,
            'mada-pay' => MoyasarPaymentGateway::class,
            'stc-pay' => MoyasarPaymentGateway::class,
        ];

        $class = $map[$paymentMethod->code] ?? null;

        if (! $class || ! in_array(PaymentGatewayInterface::class, class_implements($class))) {
            throw new InvalidArgumentException("Payment gateway not found for {$paymentMethod->code}");
        }

        app($class)->pay($request);
    }
}
