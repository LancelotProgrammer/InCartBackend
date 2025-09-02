<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Filament\Services\HandleUploadedFiles;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedPaths = $data['files'] ?? [];

        unset($data['files']);

        $model = static::getModel()::create($data);

        HandleUploadedFiles::configure($uploadedPaths, $model);

        return $model;
    }
}
