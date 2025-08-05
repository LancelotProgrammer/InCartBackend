<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHeaderValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO

        // if (
        //     ! $request->hasHeader('X-API-KEY') ||
        //     ! $request->hasHeader('X-APP-VERSION') ||
        //     ! $request->hasHeader('X-APP-SIGNATURE')
        // ) {
        //     return response()->json(['error' => 'Missing required headers'], 403);
        // }

        // if (
        //     $request->header('X-API-KEY') !== config('sanctum.x-api-key') ||
        //     $request->header('X-APP-VERSION') !== config('sanctum.x-app-version') ||
        //     $request->header('X-APP-SIGNATURE') !== config('sanctum.x-app-signature')
        // ) {
        //     return response()->json(['error' => 'Invalid headers'], 403);
        // }

        return $next($request);
    }
}
