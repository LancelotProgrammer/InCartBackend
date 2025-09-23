<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Stack::make([
                        TextColumn::make('email')
                            ->icon(Heroicon::Envelope)
                            ->searchable(),
                        TextColumn::make('email_verified_at')
                            ->icon(Heroicon::CheckCircle)
                            ->prefix('Email verified at: ')
                            ->date(),
                        TextColumn::make('phone')
                            ->icon(Heroicon::DevicePhoneMobile)
                            ->searchable(),
                        TextColumn::make('phone_verified_at')
                            ->icon(Heroicon::CheckCircle)
                            ->prefix('Phone verified at: ')
                            ->date()
                    ]),
                    Stack::make([
                        TextColumn::make('blocked_at')->icon(Heroicon::ExclamationCircle)->prefix('Blocked At: ')->date(),
                        TextColumn::make('city.name')->icon(Heroicon::OutlinedGlobeAlt),
                        TextColumn::make('role.title')->icon(Heroicon::UserGroup),
                    ]),
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
                TernaryFilter::make('email_verified_at')->label('Email verified')
                    ->nullable(),
                TernaryFilter::make('phone_verified_at')->label('Phone verified')
                    ->nullable(),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make(),
                    EditAction::make(),
                ]),
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
