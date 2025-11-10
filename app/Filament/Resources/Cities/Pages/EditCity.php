<?php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use App\Traits\HandleDeleteDependencies;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCity extends EditRecord
{
    use HandleDeleteDependencies, HasConcurrentEditingProtection;

    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
            Action::make('force')
                ->label('Force Save')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $city = $this->getRecord();
                    $data = $this->form->getState();

                    $this->handleRecordUpdate($city, $data);

                    Notification::make()
                        ->success()
                        ->title('Saved')
                        ->send();
                })
        ];
    }

    protected function beforeSave(): void
    {
        $city = $this->getRecord();
        $data = $this->form->getState();

        if ($city->boundary !== $data['boundary'] && !request()->boolean('force')) {
            Notification::make()
                ->warning()
                ->title('You are about to change the city boundary')
                ->body('All user\'s addresses which are outside the new boundary will be rejected. If you are sure, click "Force Save" to proceed.')
                ->send();

            $this->halt();
        }
    }
}
