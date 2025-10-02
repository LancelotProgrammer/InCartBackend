<?php

namespace App\ExternalServices;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\BasePaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MoyasarPaymentGateway extends BasePaymentGateway implements PaymentGatewayInterface
{
    protected string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.moyasar.secret');
        $this->baseUrl = config('services.moyasar.baseUrl');
    }

    public function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Order::where('payment_token', $token)->exists());

        return $token;
    }

    /**
     * Process a payment
     */
    public function pay(Request $request): void
    {
        $orderId = $request->input('order_id');
        $paymentMethodId = $request->input('payment_method_id');
        $token = $request->input('token');
        $metadata = $request->input('metadata');

        // process...

        // $this->payOrder(Order::where('payment_token', '=', $token)->first());
    }

    /**
     * Handle Moyasar callback
     */
    public function callback(Request $request): void
    {
        $orderId = $request->input('order_id');
        $paymentMethodId = $request->input('payment_method_id');
        $token = $request->input('token');
        $metadata = $request->input('metadata');

        // process...

        // $this->payOrder(Order::where('payment_token', '=', $token)->first());
    }
}
