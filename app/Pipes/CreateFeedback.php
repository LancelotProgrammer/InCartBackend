<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Branch;
use App\Models\Feedback;
use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CreateFeedback
{
    public function __invoke(Request $request, Closure $next): array
    {
        $key = 'feedback-submit:'.$request->user()->id;
        $maxAttempts = SettingsService::getAllowedTicketCount();
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new LogicalException('Limit reached', "The user has submitted more than $maxAttempts feedback");
        }
        RateLimiter::hit($key, 86400);

        $data = $request->validate([
            'feedback' => 'required|string|min:5',
        ]);

        $branchId = Branch::query()
            ->where('city_id', $request->user()->city_id)
            ->where('is_default', true)
            ->value('id');

        Feedback::create([
            'user_id' => $request->user()->id,
            'feedback' => $data['feedback'],
            'branch_id' => $branchId,
        ]);

        return $next([]);
    }
}
