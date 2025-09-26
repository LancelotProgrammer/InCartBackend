<?php

namespace App\Pipes;

use App\Models\Feedback;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CreateFeedback
{
    public function __invoke(Request $request, Closure $next): array
    {
        $key = 'feedback-submit:' . $request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 5)) { // TODO: get from settings
            return $next([]);
        }
        RateLimiter::hit($key, 86400);

        $data = $request->validate([
            'feedback' => 'required|string|min:5',
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'feedback' => $data['feedback'],
        ]);

        return $next([]);
    }
}
