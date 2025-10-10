<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Services\HandleUploadedFiles;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedPaths = $data['files'] ?? [];

        unset($data['files']);

        $model = static::getModel()::create($data);

        HandleUploadedFiles::configure($uploadedPaths, $model, 'categories');

        return $model;
    }
}
