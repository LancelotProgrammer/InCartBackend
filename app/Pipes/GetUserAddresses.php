<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserAddresses
{
    public function __invoke(Request $request, Closure $next)
    {
        $addresses = $request->user()->addresses()->get();

        if (! $addresses) {
            throw new LogicalException('No addresses found', 'User has no addresses', 404);
        }

        return $next($addresses);
    }
}
