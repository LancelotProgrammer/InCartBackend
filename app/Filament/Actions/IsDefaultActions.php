<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class IsDefaultActions
{
    public static function configure(): array
    {
        return [
            Action::make('mark_as_default')
                ->hidden(fn(Model $row) => $row->is_default === true)
                ->action(function (Model $row) {
                    $alreadyExists = $row->newQuery()
                        ->where('city_id', $row->city_id)
                        ->where('is_default', true)
                        ->where('id', '!=', $row->id)
                        ->exists();
                    if ($alreadyExists) {
                        Notification::make()
                            ->title('Default already exists')
                            ->body('A default record already exists for this city.')
                            ->warning()
                            ->send();
                        return;
                    }
                    $row->markAsDefault();
                    Notification::make()
                        ->title('Marked as default')
                        ->body('This record has been set as the default for the city.')
                        ->success()
                        ->send();
                }),
            Action::make('unmark_as_default')
                ->hidden(fn(Model $row) => $row->is_default === false)
                ->action(function (Model $row) {
                    $row->unmarkAsDefault();
                    Notification::make()
                        ->title('Marked as not default')
                        ->body('This record is no longer the default for its city.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
