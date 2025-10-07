<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PublishActions
{
    public static function configure(): array
    {
        return [
            Action::make('publish')
                ->authorize('publish')
                ->label('activate')
                ->color('success')
                ->icon(Heroicon::Bolt)
                ->action(function ($record) {
                    $record->publish();
                    Notification::make()
                        ->title('Published successfully')
                        ->body('This record has been published successfully.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(function ($record) {
                    return ! $record->isPublished();
                }),
            Action::make('unpublish')
                ->authorize('unpublish')
                ->label('deactivate')
                ->color('warning')
                ->icon(Heroicon::BoltSlash)
                ->action(function ($record) {
                    $record->unpublish();
                    Notification::make()
                        ->title('Unpublished successfully')
                        ->body('This record has been unpublished successfully.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(function ($record) {
                    return $record->isPublished();
                }),
        ];
    }
}
