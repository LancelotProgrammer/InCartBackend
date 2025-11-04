<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Traits\HandleDeleteDependencies;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\DeleteAction;
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
}
