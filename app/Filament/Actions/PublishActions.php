<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PublishActions
{
    public static function configure(): array
    {
        return [
            Action::make('publish')
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
