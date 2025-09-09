<?php

namespace App\Pipes;

use App\Models\PaymentMethod;
use Closure;
use Illuminate\Http\Request;

class GetPaymentMethods
{
    public function __invoke(Request $request, Closure $next): array
    {
        return $next(PaymentMethod::published()->get()->map(function (PaymentMethod $paymentMethod) {
            return [
                'id' => $paymentMethod->id,
                'order' => $paymentMethod->order,
                'name' => $paymentMethod->title,
            ];
        }));
    }
}
