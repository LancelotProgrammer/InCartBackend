<?php

namespace App\Filament\Actions;

use App\Models\Advertisement;
use Filament\Actions\Action;

class PublishActions
{
    public static function configure(): array
    {
        return [
            Action::make('publish')
                ->action(function (Advertisement $record) {
                    return $record->publish();
                })
                ->requiresConfirmation()
                ->visible(function (Advertisement $record) {
                    return ! $record->isPublished();
                }),
            Action::make('unpublish')
                ->action(function (Advertisement $record) {
                    return $record->unpublish();
                })
                ->requiresConfirmation()
                ->visible(function (Advertisement $record) {
                    return $record->isPublished();
                })
        ];
    }
}
