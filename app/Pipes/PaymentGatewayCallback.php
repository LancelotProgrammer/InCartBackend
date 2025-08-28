<?php

namespace App\Pipes;

use App\Services\MoyasarPaymentGateway;
use Closure;
use Exception;
use Illuminate\Http\Request;

class PaymentGatewayCallback
{
    public function __invoke(Request $request, Closure $next)
    {
        app(match ($request->route()->getName()) {
            'moyasar.callback' => MoyasarPaymentGateway::class,
            default => throw new Exception('Unsupported payment gateway callback'),
        })->callback($request);

        return $next();
    }
}
