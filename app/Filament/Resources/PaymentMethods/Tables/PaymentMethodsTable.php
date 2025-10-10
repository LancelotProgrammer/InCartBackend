<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Filament\Actions\PublishActions;
use App\Models\PaymentMethod;
use App\Traits\HandleDeleteDependencies;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    use HandleDeleteDependencies;

    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('order'),
                TextColumn::make('published_at'),
                TextColumn::make('branch.title'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
                ...PublishActions::configure(PaymentMethod::class),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
