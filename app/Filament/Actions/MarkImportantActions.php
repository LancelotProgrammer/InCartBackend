<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class MarkImportantActions
{
    public static function configure(): array
    {
        return [
            Action::make('mark_important')
                ->authorize('markImportant')
                ->label('Mark Important')
                ->icon('heroicon-o-star')
                ->requiresConfirmation()
                ->visible(fn ($record) => ! $record->is_important)
                ->action(fn ($record) => $record->update(['is_important' => true])),
            Action::make('unmark_important')
                ->authorize('unmarkImportant')
                ->label('Unmark Important')
                ->icon('heroicon-o-star')
                ->color('secondary')
                ->visible(fn ($record) => $record->is_important)
                ->action(fn ($record) => $record->update(['is_important' => false])),
        ];
    }
}
