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
    public function checkout(Request $request): self
    {
        $paymentMethod = PaymentMethod::find($request->input('payment_method_id'));

        if (! $paymentMethod) {
            throw new LogicalException('Payment method not found');
        }

        $order = Order::where('payment_token', '=', 'order_id')->first();
        if ($paymentMethod->code === PaymentMethod::PAY_ON_DELIVERY_CODE) {
            throw new LogicalException('Checkout error', 'Payment method is pay-on-delivery and it is already checked out');
        }
        if ($order->payment_status === PaymentStatus::PAID) {
            throw new LogicalException('Checkout error', 'order is already payed');
        }
        $this->resolvePaymentGateway($paymentMethod, $request);

        return $this;
    }

    private function resolvePaymentGateway(PaymentMethod $paymentMethod, Request $request): void
    {
        $class = BasePaymentGateway::$map[$paymentMethod->code] ?? null;

        if (! $class || ! in_array(PaymentGatewayInterface::class, class_implements($class))) {
            throw new InvalidArgumentException("Payment gateway not found for {$paymentMethod->code}");
        }

        app($class)->pay($request);
    }
}
