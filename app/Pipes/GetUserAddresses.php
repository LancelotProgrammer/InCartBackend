<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetUserAddresses
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $addresses = $request->user()->addresses()->get();

        return $next($addresses);
    }
}
