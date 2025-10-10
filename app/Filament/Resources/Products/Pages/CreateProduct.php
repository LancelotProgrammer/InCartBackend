<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\HandleUploadedFiles;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedPaths = $data['files'] ?? [];

        unset($data['files']);

        $model = static::getModel()::create($data);

        HandleUploadedFiles::configure($uploadedPaths, $model, 'products');

        return $model;
    }
}
