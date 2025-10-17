<?php

namespace App\Pipes;

use App\ExternalServices\MoyasarPaymentGateway;
use Closure;
use Illuminate\Http\Request;

class MoyasarPaymentGatewayPayCallback
{
    public function __invoke(Request $request, Closure $next): array
    {
        app(MoyasarPaymentGateway::class)->payCallback($request);

        return $next([]);
    }
}
