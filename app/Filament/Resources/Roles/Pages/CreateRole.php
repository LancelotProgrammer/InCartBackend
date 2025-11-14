<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Services\PermissionDependencyService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;


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
