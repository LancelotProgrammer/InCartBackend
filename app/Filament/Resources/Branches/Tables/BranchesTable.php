<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Actions\PublishActions;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('longitude'),
                TextColumn::make('latitude'),
                TextColumn::make('created_at')->dateTime(),
                TextColumn::make('published_at')->dateTime(),
                TextColumn::make('city.name')->numeric()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
