<?php

namespace App\Http\Middleware;

use App\Exceptions\SetupException;
use App\Models\Branch;
use App\Models\City;
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
        // 1- Priority: from header
        if ($branchId = $request->header('X-BRANCH-ID')) {
            return $this->validateBranch((int) $branchId, 'The provided X-BRANCH-ID is not attached to any branch');
        }

        // 2- Authenticated user -> use default branch in user's city
        if ($user) {
            return $this->getDefaultBranchForCity($user->city_id, 'The default branch for your city is not found');
        }
        if (auth('sanctum')->user()?->city_id !== null) {
            return $this->getDefaultBranchForCity(auth('sanctum')->user()->city_id, 'The default branch for your city is not found');
        }

        // future: uncomment this if you want to get the branch from ip location service
        // // 3- No auth -> try location service
        // if ($position = Location::get()) {
        //     $cityId = City::where('name', $position->cityName)->value('id');
        //     if (!$cityId) {
        //         throw new InvalidArgumentException('The provided cityName from IP location service is not attached to any city');
        //     }
        //     return $this->getDefaultBranchForCity($cityId, 'The provided city_id is not attached to any branch');
        // }

        // 4-Absolute fallback -> use the global default branch
        return $this->getFallbackBranch();
    }

    protected function validateBranch(int $branchId, string $errorMessage): int
    {
        $exists = Branch::where('id', $branchId)->published()->first();
        if (! $exists) {
            throw new InvalidArgumentException($errorMessage);
        }

        return $branchId;
    }

    protected function getDefaultBranchForCity(?int $cityId, string $errorMessage): int
    {
        $query = Branch::query()->published()->where('is_default', true);
        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        $branchId = $query->value('id');
        if (! $branchId) {
            throw new InvalidArgumentException($errorMessage);
        }

        return $branchId;
    }

    protected function getFallbackBranch(): int
    {
        $branchId = Branch::query()->published()->where('is_default', true)->value('id');
        if (! $branchId) {
            throw new SetupException('Something went wrong', 'System setup error. Please setup a branch from the dashboard as default and publish it');
        }

        return $branchId;
    }
}
