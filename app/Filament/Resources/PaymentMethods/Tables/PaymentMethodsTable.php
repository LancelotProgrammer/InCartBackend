<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Filament\Actions\PublishActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('order'),
                TextColumn::make('published_at'),
                TextColumn::make('branch.title'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ...PublishActions::configure()
            ])
            ->toolbarActions([
                //
            ]);
    }
}
