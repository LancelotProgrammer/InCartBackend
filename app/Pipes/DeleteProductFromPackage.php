<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class DeleteProductFromPackage
{
    public function __invoke(Request $request, Closure $next): array
    {

        return $next();
    }
}
