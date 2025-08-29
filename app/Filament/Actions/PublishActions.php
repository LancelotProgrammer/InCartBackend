<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class PublishActions
{
    public static function configure(): array
    {
        return [
            Action::make('publish')
                ->action(function ($record) {
                    return $record->publish();
                })
                ->requiresConfirmation()
                ->visible(function ($record) {
                    return ! $record->isPublished();
                }),
            Action::make('unpublish')
                ->action(function ($record) {
                    return $record->unpublish();
                })
                ->requiresConfirmation()
                ->visible(function ($record) {
                    return $record->isPublished();
                }),
        ];
    }
}
