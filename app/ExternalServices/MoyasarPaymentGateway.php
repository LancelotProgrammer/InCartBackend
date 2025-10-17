<?php

namespace App\ExternalServices;

use App\Models\Order;
use App\Services\BasePaymentGateway;
use App\Services\PaymentGatewayInterface;
use Illuminate\Http\Request;

class MoyasarPaymentGateway extends BasePaymentGateway implements PaymentGatewayInterface
{
    protected string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.moyasar.secret');
        $this->baseUrl = config('services.moyasar.baseUrl');
    }

    public function pay(Order $order, ?array $payload): void
    {
        // process...

        // $this->payOrder($order);
    }

    public function payCallback(Request $request): void
    {
        // process...
    }

    public function refund(Order $order): void
    {
        // process...

        // $this->refundOrder($order);
    }

    public function refundCallback(Request $request): void
    {
        // process...
    }
}
