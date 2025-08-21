<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetBranches
{
    public function __invoke(Request $request, Closure $next)
    {
        

        return $next();
    }
}
