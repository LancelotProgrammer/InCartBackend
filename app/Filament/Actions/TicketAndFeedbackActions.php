<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class TicketAndFeedbackActions
{
    public static function configure(string $modelName): array
    {
        return [
            Action::make('mark_important')
                ->authorize('markImportant')
                ->label('Mark Important')
                ->icon('heroicon-o-star')
                ->requiresConfirmation()
                ->visible(fn($record) => ! $record->is_important)
                ->action(fn($record) => $record->update(['is_important' => true])),
            Action::make('unmark_important')
                ->authorize('unmarkImportant')
                ->label('Unmark Important')
                ->icon('heroicon-o-star')
                ->color('secondary')
                ->visible(fn($record) => $record->is_important)
                ->action(fn($record) => $record->update(['is_important' => false])),
            Action::make('process')
                ->authorize('process')
                ->label('Process')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->processed_at === null)
                ->action(function ($record) use ($modelName) {
                    $record->update(['processed_at' => now()]);
                    Notification::make()
                        ->title("$modelName processed")
                        ->body("$modelName #{$record->id} has been processed.")
                        ->sendToDatabase($record->user);
                }),
        ];
    }
}
