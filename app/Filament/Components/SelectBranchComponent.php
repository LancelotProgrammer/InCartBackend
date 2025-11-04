<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class SelectBranchComponent
{
    public static function configure(): Select
    {
        return Select::make('branch_id')
            ->required() ->relationship('branch', 'title', function (Builder $query) {
                $user = auth()->user();
                if ($user && $user->shouldFilterBranchContent()) {
                    $query->whereIn('id', $user->branches()->pluck('branches.id'));
                }
            });
    }
}
