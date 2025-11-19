<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;

class BranchScope implements Scope
{
    protected static bool $enabled = true;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! static::$enabled) {
            return;
        }

        if ($branchId = request()->attributes->get('currentBranchId')) {
            Log::channel('app_log')->info('Scopes(BranchScope): filtering data by branch using request.', [
                'url' => request()->url(),
                'model' => get_class($model),
                'userId' => auth('sanctum')->id(),
                'branchId' => $branchId,
            ]);
            $builder->where($model->getTable() . '.branch_id', $branchId);
        }

        $user = auth('web')->user();
        if ($user) {
            $userBranch = $user->branches->first();
            if (! $userBranch) {
                Log::channel('app_log')->warning('Scopes(BranchScope): filtering data by branch using user permission but user has no branch.', [
                    'url' => request()->url(),
                    'model' => get_class($model),
                    'userId' => $user->id,
                    'email' => $user->email,
                ]);
                return;
            }
            $allow = $user->shouldFilterBranchContent();
            if ($allow) {
                Log::channel('app_log')->info('Scopes(BranchScope): filtering data by branch using user permission.', [
                    'url' => request()->url(),
                    'model' => get_class($model),
                    'userId' => $user->id,
                    'permissionResult' => $allow,
                    'branches' => $user->branches?->pluck('id')->toArray(),
                ]);
                $builder->where($model->getTable() . '.branch_id', $userBranch->id);
            }
        }

        Log::channel('app_log')->info('Scopes(BranchScope): skipping filtering data by branch.');
    }

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    public static function isEnabled(): bool
    {
        return static::$enabled;
    }
}
