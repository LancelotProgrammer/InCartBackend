<?php

namespace App\Filament\Resources\Feedback\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('feedback')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at'),
                TextColumn::make('created_at')->dateTime()->toggleable(),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('is_important')
                    ->label('Important')
                    ->query(fn($query) => $query->where('is_important', true)),
                TernaryFilter::make('processed')
                    ->label('Processed')
                    ->nullable()
                    ->queries(
                        true: fn($query) => $query->whereNotNull('processed_at'),
                        false: fn($query) => $query->whereNull('processed_at'),
                        blank: fn($query) => $query,
                    ),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                Action::make('mark_important')
                    ->label('Mark Important')
                    ->icon('heroicon-o-star')
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$record->is_important)
                    ->action(fn($record) => $record->update(['is_important' => true])),
                Action::make('unmark_important')
                    ->label('Unmark Important')
                    ->icon('heroicon-o-star')
                    ->color('secondary')
                    ->visible(fn($record) => $record->is_important)
                    ->action(fn($record) => $record->update(['is_important' => false])),
                Action::make('process')
                    ->label('Process')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->processed_at === null)
                    ->action(function ($record) {
                        $record->update(['processed_at' => now()]);
                        Notification::make()
                            ->title('Feedback processed')
                            ->body("Feedback #{$record->id} has been processed.")
                            ->sendToDatabase($record->user);
                    }),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ]);
    }
}
