<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Services\DatabaseUserNotification;
use App\Services\FirebaseFCM;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('question')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at')->dateTime(),
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
                    ->schema([
                        Textarea::make('reply')->required(),
                        Checkbox::make('send_notification')->default(true)->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update(['processed_at' => now()]);
                        if ($data['send_notification'] === true) {
                            FirebaseFCM::sendTicketNotification($record, $data['reply']);
                            DatabaseUserNotification::sendTicketNotification($record, $data['reply']);
                        }
                        Notification::make()
                            ->title('Support ticket processed')
                            ->body("Ticket #{$record->id} has been processed.")
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
