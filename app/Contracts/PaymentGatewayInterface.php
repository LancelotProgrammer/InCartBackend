<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function pay(Request $request): void;

    public function callBack(Request $request): void;
}
