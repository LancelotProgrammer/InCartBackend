<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class ValidateUser
{
    public function __invoke(Request $request, Closure $next): array
    {
        $user = $request->user();

        if (! $user) {
            throw new LogicalException('User not authenticated', 'No user instance found', 401);
        }

        return $next($request);
    }
}
