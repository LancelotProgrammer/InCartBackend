<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function pay(Order $order, array $payload): PaymentGatewayResult;

    public function payCallBack(Request $request): PaymentGatewayResult;

    public function refund(Order $order, string $paymentToken): PaymentGatewayResult;

    public function refundCallBack(Request $request): PaymentGatewayResult;
}
