<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Filament\Actions\PublishActions;
use App\Filament\Filters\BranchSelectFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class PaymentMethodsTable
{
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
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                BranchSelectFilter::configure(),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
