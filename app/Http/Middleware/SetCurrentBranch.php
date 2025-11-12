<?php

namespace App\Http\Middleware;

use App\Exceptions\SetupException;
use App\Models\Branch;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $request->attributes->set('currentBranchId', $this->determineBranchId($user, $request));

        return $next($request);
    }

    protected function determineBranchId(?User $user, Request $request): int
    {
        // 1. Header
        if ($branchId = $request->header('X-BRANCH-ID')) {
            return $this->validateBranch((int) $branchId, 'The provided X-BRANCH-ID is not attached to any branch');
        }

        // 2. Authenticated user
        if ($user && $user->city_id) {
            return $this->getDefaultBranchForCity($user->city_id, "Default branch for city ID {$user->city_id} not found");
        }

        // 3. future: (Optional) IP-based location — disabled for now
        // if ($position = Location::get()) { ... }

        // 4. Fallback
        return $this->getFallbackBranch();
    }

    protected function validateBranch(int $branchId, string $errorMessage): int
    {
        if (! Branch::published()->where('id', $branchId)->exists()) {
            throw new InvalidArgumentException($errorMessage);
        }
        return $branchId;
    }

    protected function getDefaultBranchForCity(?int $cityId, string $errorMessage): int
    {
        $branchId = Branch::published()
            ->where('is_default', true)
            ->when($cityId, fn($query) => $query->where('city_id', $cityId))
            ->value('id');

        if (! $branchId) {
            throw new SetupException('Something went wrong', $errorMessage);
        }

        return $branchId;
    }

    protected function getFallbackBranch(): int
    {
        $branchId = Branch::published()
            ->where('is_default', true)
            ->orderByDesc('id')
            ->value('id');

        if (! $branchId) {
            throw new SetupException(
                'Something went wrong',
                'System setup error: Please set one default published branch at least in the dashboard.'
            );
        }

        return $branchId;
    }
}
