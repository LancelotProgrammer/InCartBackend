<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\ExternalServices\FirebaseFCM;
use App\Filament\Actions\MarkImportantActions;
use App\Models\Ticket;
use App\Services\DatabaseUserNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('question')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at')->dateTime(),
                TextColumn::make('manager.name')->label('Manager')->toggleable(isToggledHiddenByDefault: true),
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
                DeleteAction::make(),
                ViewAction::make(),
                ...MarkImportantActions::configure(),
                Action::make('process')
                    ->authorize('process')
                    ->label('Process')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->processed_at === null)
                    ->form([
                        Textarea::make('reply')->required(),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        $reply = $data['reply'];
                        $record->update(
                            [
                                'processed_at' => now(),
                                'processed_by' => auth()->user()->id,
                                'reply' => $data['reply'],
                            ]
                        );
                        FirebaseFCM::sendTicketNotification($record, $reply);
                        DatabaseUserNotification::sendTicketNotification($record, $reply);
                        Notification::make()
                            ->success()
                            ->title("Ticket #{$record->id} marked as processed")
                            ->send();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
