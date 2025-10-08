<?php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use App\Traits\HandleDeleteDependencies;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCity extends EditRecord
{
    use HandleDeleteDependencies;

    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
        ];
    }
}
