<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Traits\HandleDeleteDependencies;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    use HandleDeleteDependencies;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
