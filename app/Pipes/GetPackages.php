<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetPackages
{
    public function __invoke(Request $request, Closure $next): Closure
    {

        return $next([]);
    }
}
