<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetProductDetails
{
    public function __invoke(Request $request, Closure $next)
    {
        

        return $next();
    }
}
