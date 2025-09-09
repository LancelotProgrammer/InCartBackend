<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class AddProductToPackage
{
    public function __invoke(Request $request, Closure $next): array
    {

        return $next();
    }
}
