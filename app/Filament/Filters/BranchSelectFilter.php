<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;

class BranchSelectFilter
{
    public static function configure(): SelectFilter
    {
        return SelectFilter::make('branch')->relationship('branch', 'title');
    }
}
