<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Services\HandleUploadedFiles;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, string $model): Model {
                    $uploadedPaths = $data['files'] ?? [];

                    unset($data['files']);

                    $created = $model::create($data);

                    HandleUploadedFiles::configure($uploadedPaths, $created, 'categories');

                    return $created;
                }),
        ];
    }
}
