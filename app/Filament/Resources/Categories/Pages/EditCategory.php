<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Actions\CategoriesActions;
use App\Filament\Resources\Categories\CategoryResource;
use App\Traits\HandleDeleteDependencies;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use HandleDeleteDependencies, HasConcurrentEditingProtection;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
            CategoriesActions::configureViewProductsAction()->label('View Products'),
            CategoriesActions::configureViewCategoriesAction()->label('View Categories'),
        ];
    }
}
