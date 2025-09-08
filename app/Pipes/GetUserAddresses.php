<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserAddresses
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $addresses = $request->user()->addresses()->get();

        return $next($addresses);
    }
}
