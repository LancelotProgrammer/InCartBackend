<?php

namespace App\Filament\Actions;

use App\Services\PublishService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PublishActions
{
    public static function configure(string $model): array
    {
        return [
            Action::make('publish')
                ->authorize('publish')
                ->label('Activate')
                ->color('success')
                ->icon(Heroicon::Bolt)
                ->requiresConfirmation()
                ->visible(fn ($record) => ! $record->isPublished())
                ->action(function ($record) use ($model) {
                    [$condition, $reason] = PublishService::canPublish($model, $record);
                    if (! $condition) {
                        Notification::make()
                            ->title('Cannot Publish')
                            ->body($reason)
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }
                    $record->publish();
                    Notification::make()
                        ->title('Published Successfully')
                        ->body('This record has been published successfully.')
                        ->success()
                        ->send();
                }),

            Action::make('unpublish')
                ->authorize('unpublish')
                ->label('Deactivate')
                ->color('warning')
                ->icon(Heroicon::BoltSlash)
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->isPublished())
                ->action(function ($record) use ($model) {
                    [$condition, $reason] = PublishService::canUnpublish($model, $record);
                    if (! $condition) {
                        Notification::make()
                            ->title('Cannot Unpublish')
                            ->body($reason)
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }
                    $record->unpublish();
                    Notification::make()
                        ->title('Unpublished Successfully')
                        ->body('This record has been unpublished successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
