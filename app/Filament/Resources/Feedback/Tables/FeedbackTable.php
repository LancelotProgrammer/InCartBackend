<?php

namespace App\Filament\Resources\Feedback\Tables;

use App\Filament\Actions\MarkImportantActions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('feedback')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at'),
                TextColumn::make('created_at')->dateTime()->toggleable(),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('is_important')
                    ->label('Important')
                    ->query(fn ($query) => $query->where('is_important', true)),
                TernaryFilter::make('processed')
                    ->label('Processed')
                    ->nullable()
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('processed_at'),
                        false: fn ($query) => $query->whereNull('processed_at'),
                        blank: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                DeleteAction::make(),
                ViewAction::make(),
                ...MarkImportantActions::configure(),
                Action::make('process')
                    ->authorize('process')
                    ->label('Process')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->processed_at === null)
                    ->action(function ($record) {
                        $record->update(['processed_at' => now()]);
                        Notification::make()
                            ->success()
                            ->title("Feedback #{$record->id} marked as processed")
                            ->send();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
