<?php

namespace App\Pipes;

use App\Models\PaymentMethod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetPaymentMethods
{
    public function __invoke(Request $request, Closure $next): Collection
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
