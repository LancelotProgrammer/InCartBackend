<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class BranchSelectFilter
{
    public static function configure(): SelectFilter
    {
        return SelectFilter::make('branch')->relationship('branch', 'title', fn (Builder $query) => $query->orderBy('id'));
    }
}
