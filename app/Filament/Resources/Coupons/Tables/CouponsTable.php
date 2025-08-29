<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Filament\Filters\BranchSelectFilter;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('type')->badge(),
                TextColumn::make('published_at')->dateTime(),
                TextColumn::make('created_at')->dateTime(),
                TextColumn::make('branch.title'),
            ])
            ->filters([
                BranchSelectFilter::configure(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
