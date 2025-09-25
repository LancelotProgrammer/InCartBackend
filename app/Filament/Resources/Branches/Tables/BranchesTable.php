<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Actions\IsDefaultActions;
use App\Filament\Actions\PublishActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('city.name')->numeric()->sortable(),
                TextColumn::make('latitude'),
                TextColumn::make('longitude'),
                IconColumn::make('is_default')->boolean(),
                TextColumn::make('published_at')->dateTime(),
            ])
            ->groups([
                'city.id',
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ...IsDefaultActions::configure(),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
