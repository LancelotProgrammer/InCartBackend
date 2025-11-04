<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Branch;
use App\Models\Ticket;
use App\Services\CacheService;
use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CreateTicket
{
    public function __invoke(Request $request, Closure $next): array
    {
        $key = 'ticket-submit:'.$request->user()->id;
        $maxAttempts = SettingsService::getAllowedTicketCount();
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new LogicalException('Limit reached', "The user has submitted more than $maxAttempts ticket/s");
        }
        RateLimiter::hit($key, 86400);

        $data = $request->validate([
            'question' => 'required|string|min:5',
        ]);

        $branchId = Branch::query()
            ->where('city_id', $request->user()->city_id)
            ->where('is_default', true)
            ->value('id');

        Ticket::create([
            'user_id' => $request->user()->id,
            'branch_id' => $branchId,
            'question' => $data['question'],
        ]);

        CacheService::deleteTodaySupportCount();

        return $next([]);
    }
}
