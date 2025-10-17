<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function pay(Order $order, ?array $payload): void;

    public function payCallBack(Request $request): void;

    public function refund(Order $order): void;

    public function refundCallBack(Request $request): void;
}
