<?php

namespace App\Http\Middleware;

use App\Exceptions\SetupException;
use App\Models\Branch;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::channel('app_log')->info('Middleware: determining current branch.');

        // 1. Header
        if ($branchId = $request->header('X-BRANCH-ID')) {
            $xHeaderResult = $this->validateBranch((int) $branchId, 'The provided X-BRANCH-ID is not attached to any branch');
            Log::channel('app_log')->info('Middleware: X-BRANCH-ID header found.',  [
                'xHeaderResult' => $xHeaderResult
            ]);
            return $xHeaderResult;
        }

        // 2. Authenticated user
        // 2.a get user from auth method
        if ($user) {
            $userResult = $this->getDefaultBranchForCity($user->city_id, 'The default branch for your city is not found');
            Log::channel('app_log')->info('Middleware: Authenticated user found.', [
                'userResult' => $userResult
            ]);
            return $userResult;
        }
        // 2.b get user from optional auth method
        if (auth('sanctum')->user()?->city_id !== null) {
            $userSanctumResult = $this->getDefaultBranchForCity(auth('sanctum')->user()->city_id, 'The default branch for your city is not found');
            Log::channel('app_log')->info('Middleware: Authenticated sanctum user found.', [
                'userSanctumResult' => $userSanctumResult
            ]);
            return $userSanctumResult;
        }

        // 3. future: (Optional) IP-based location — disabled for now
        // if ($position = Location::get()) { ... }

        // 4. Fallback
        $defaultResult = $this->getFallbackBranch();

        Log::channel('app_log')->info('Middleware: Fallback branch found.', [
            'defaultResult' => $defaultResult
        ]);

        return $defaultResult;
    }

    protected function validateBranch(int $branchId, string $errorMessage): int
    {
        if (! Branch::published()->where('id', $branchId)->exists()) {
            Log::channel('app_log')->critical('Middleware: provided X-BRANCH-ID is not attached to any published branch.', [
                'branchId' => $branchId
            ]);
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
