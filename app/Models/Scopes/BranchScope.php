<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

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
            $builder->where($model->getTable().'.branch_id', $branchId);
        }

        $user = auth('web')->user();
        if ($user) {
            if ($user->shouldFilterBranchContent()) {
                $builder->where($model->getTable().'.branch_id', $user->branches->first()->id);
            }
        }
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
