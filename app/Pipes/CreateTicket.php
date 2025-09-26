<?php

namespace App\Pipes;

use App\Models\Ticket;
use App\Services\Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CreateTicket
{
    public function __invoke(Request $request, Closure $next): array
    {
        $key = 'ticket-submit:' . $request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 5)) { // TODO: get from settings
            return $next([]);
        }
        RateLimiter::hit($key, 86400);

        $data = $request->validate([
            'question' => 'required|string|min:5',
        ]);

        Ticket::create([
            'user_id' => $request->user()->id,
            'question' => $data['question'],
        ]);

        Cache::deleteTodaySupportCount();

        return $next([]);
    }
}
