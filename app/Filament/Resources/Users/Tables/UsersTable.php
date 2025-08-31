<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    // ImageColumn::make('avatar.url'),
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Stack::make([
                        TextColumn::make('email')
                            ->icon(Heroicon::Envelope)
                            ->searchable()
                            ->placeholder('No Email'),
                        TextColumn::make('phone')
                            ->icon(Heroicon::DevicePhoneMobile)
                            ->searchable()
                            ->placeholder('No Phone'),
                    ]),
                    TextColumn::make('city.name')->icon(Heroicon::UserGroup),
                    TextColumn::make('role.title')->icon(Heroicon::OutlinedGlobeAlt),
                ]),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                SelectFilter::make('city')->relationship('city', 'name'),
                SelectFilter::make('role')->relationship('role', 'title'),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
                ViewAction::make(),
                EditAction::make(),
                // TODO: improve the design of these actions
                Action::make('Block')
                    ->action(function ($record) {
                        return $record->Block();
                    })
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        return ! $record->isBlocked();
                    }),
                Action::make('un-block')
                    ->action(function ($record) {
                        return $record->unBlock();
                    })
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        return $record->isBlocked();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
