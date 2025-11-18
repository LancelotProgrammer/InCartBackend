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

        Log::info('Scopes: filtering data by branch.', [
            'model' => get_class($model),
        ]);

        if ($branchId = request()->attributes->get('currentBranchId')) {
            Log::info('Scopes: filtering data by branch using request.', [
                'branchId' => $branchId,
                'isAPI' => request()->is('api/*'),
            ]);
            $builder->where($model->getTable().'.branch_id', $branchId);
        }

        $user = auth('web')->user();
        if ($user) {
            $allow = $user->shouldFilterBranchContent();
            if ($allow) {
                Log::info('Scopes: filtering data by branch using user permission.', [
                    'userId' => $user->id,
                    'permissionResult' => $allow,
                    'branches' => $user->branches?->pluck('id')->toArray(),
                    'isAPI' => request()->is('api/*'),
                ]);
                $builder->where($model->getTable().'.branch_id', $user->branches->first()->id);
            }
        }

        Log::info('Scopes: filtered data by branch.');
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
