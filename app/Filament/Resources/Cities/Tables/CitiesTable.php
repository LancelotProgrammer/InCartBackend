<?php

namespace App\Filament\Resources\Cities\Tables;

use App\Filament\Actions\PublishActions;
use App\Models\City;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('code'),
                TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ...PublishActions::configure(City::class),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
