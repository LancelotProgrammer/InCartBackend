<?php

namespace App\Pipes;

use App\Services\HomeService;
use Closure;
use Illuminate\Http\Request;

class GetHome
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        return $next(HomeService::getHomeContent($request));
    }
}
