<?php

namespace App\Pipes;

use App\ExternalServices\MoyasarPaymentGateway;
use Closure;
use Illuminate\Http\Request;

class MoyasarPaymentGatewayRefundCallback
{
    public function __invoke(Request $request, Closure $next): array
    {
        app(MoyasarPaymentGateway::class)->refundCallback($request);

        return $next([]);
    }
}
