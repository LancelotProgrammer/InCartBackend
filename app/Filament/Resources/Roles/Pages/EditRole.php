<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Services\PermissionDependencyService;
use App\Traits\HandleDeleteDependencies;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use HandleDeleteDependencies, HasConcurrentEditingProtection;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
        ];
    }

    protected function getConcurrencyAttributes(): array
    {
        return [
            $this->record?->updated_at?->toDateTimeString(),
            $this->record?->permissions()?->count(),
        ];
    }

    protected function beforeValidate(): void
    {
        $result = PermissionDependencyService::validate($this->form->getRawState()['permissions']);

        switch ($result['type']) {
            case 'error':
                Notification::make()
                    ->danger()
                    ->title($result['title'])
                    ->body($result['body'])
                    ->persistent()
                    ->send();
                $this->halt();
            case 'warning':
                Notification::make()
                    ->warning()
                    ->title($result['title'])
                    ->body($result['body'])
                    ->persistent()
                    ->send();
                break;
            case 'info':
                Notification::make()
                    ->info()
                    ->title($result['title'])
                    ->body($result['body'])
                    ->persistent()
                    ->send();
                break;
        }
    }
}
